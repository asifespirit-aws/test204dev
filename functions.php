<?php
declare(strict_types=1);

require_once __DIR__ . '/db.php';

function base_url(): string
{
    $isHttps = !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off';
    $scheme = $isHttps ? 'https' : 'http';
    $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
    $dir = str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'] ?? '/'));
    $path = rtrim($dir, '/');

    return $scheme . '://' . $host . ($path ? $path . '/' : '/');
}

function ensure_table(PDO $pdo): void
{
    $sql = "CREATE TABLE IF NOT EXISTS submissions (
        id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(100) NOT NULL,
        email VARCHAR(150) NOT NULL,
        message TEXT NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";

    $pdo->exec($sql);
}

function insert_submission(PDO $pdo, array $data): int
{
    ensure_table($pdo);

    $stmt = $pdo->prepare(
        'INSERT INTO submissions (name, email, message) VALUES (:name, :email, :message)'
    );
    $stmt->execute([
        ':name' => $data['name'],
        ':email' => $data['email'],
        ':message' => $data['message'],
    ]);

    return (int) $pdo->lastInsertId();
}

function get_submissions(PDO $pdo): array
{
    ensure_table($pdo);

    $stmt = $pdo->query(
        'SELECT id, name, email, message, created_at FROM submissions ORDER BY id DESC'
    );

    return $stmt->fetchAll();
}

function send_smtp_email(
    string $toEmail,
    string $toName,
    string $subject,
    string $body,
    string $fromEmail,
    string $fromName
): void {
    $smtpHost = 'sandbox.smtp.mailtrap.io';
    $smtpPort = 587;
    $smtpUser = '553853e14274b3';
    $smtpPass = '023f4b4be4c34c';

    $socket = stream_socket_client(
        'tcp://' . $smtpHost . ':' . $smtpPort,
        $errno,
        $errstr,
        15
    );
    if (!$socket) {
        throw new RuntimeException('SMTP connect failed: ' . $errstr);
    }

    $read = static function () use ($socket): string {
        $data = '';
        while (($line = fgets($socket, 515)) !== false) {
            $data .= $line;
            if (isset($line[3]) && $line[3] === ' ') {
                break;
            }
        }
        return $data;
    };

    $write = static function (string $command) use ($socket): void {
        fwrite($socket, $command . "\r\n");
    };

    $expect = static function (string $response, array $codes): void {
        $code = substr($response, 0, 3);
        if (!in_array($code, $codes, true)) {
            throw new RuntimeException('SMTP error: ' . trim($response));
        }
    };

    $expect($read(), ['220']);
    $write('EHLO localhost');
    $ehlo = $read();
    $expect($ehlo, ['250']);

    if (stripos($ehlo, 'STARTTLS') !== false) {
        $write('STARTTLS');
        $expect($read(), ['220']);
        if (!stream_socket_enable_crypto($socket, true, STREAM_CRYPTO_METHOD_TLS_CLIENT)) {
            throw new RuntimeException('SMTP TLS failed.');
        }
        $write('EHLO localhost');
        $expect($read(), ['250']);
    }

    $write('AUTH LOGIN');
    $expect($read(), ['334']);
    $write(base64_encode($smtpUser));
    $expect($read(), ['334']);
    $write(base64_encode($smtpPass));
    $expect($read(), ['235']);

    $write('MAIL FROM:<' . $fromEmail . '>');
    $expect($read(), ['250']);
    $write('RCPT TO:<' . $toEmail . '>');
    $expect($read(), ['250', '251']);

    $write('DATA');
    $expect($read(), ['354']);

    $headers = [
        'From: ' . $fromName . ' <' . $fromEmail . '>',
        'To: ' . $toName . ' <' . $toEmail . '>',
        'Reply-To: ' . $toEmail,
        'Subject: ' . $subject,
        'MIME-Version: 1.0',
        'Content-Type: text/plain; charset=UTF-8',
    ];

    $message = implode("\r\n", $headers) . "\r\n\r\n" . $body;
    $message = str_replace(["\r\n.\r\n", "\n.\n"], ["\r\n..\r\n", "\n..\n"], $message);

    $write($message . "\r\n.");
    $expect($read(), ['250']);

    $write('QUIT');
    fclose($socket);
}
