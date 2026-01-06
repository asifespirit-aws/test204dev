<?php
declare(strict_types=1);

require_once __DIR__ . '/functions.php';

$errors = [];
$success = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $message = trim($_POST['message'] ?? '');

    if ($name === '') {
        $errors[] = 'Name is required.';
    }
    if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Valid email is required.';
    }
    if ($message === '') {
        $errors[] = 'Message is required.';
    }

    if (!$errors) {
        $subject = 'New message from ' . $name;
        $body = "Name: {$name}\nEmail: {$email}\n\nMessage:\n{$message}\n";
        $fromEmail = 'no-reply@example.com';
        $fromName = 'Web Form';

        try {
            send_smtp_email($email, $name, $subject, $body, $fromEmail, $fromName);
            $success = true;
        } catch (RuntimeException $e) {
            $errors[] = 'Email could not be sent.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Email</title>
    <style>
        body { font-family: Arial, sans-serif; padding: 32px; }
        form { max-width: 520px; }
        label { display: block; margin-top: 12px; }
        input, textarea { width: 100%; padding: 8px; margin-top: 6px; }
        .actions { margin-top: 16px; display: flex; gap: 12px; }
        .btn { padding: 10px 16px; border: 1px solid #333; text-decoration: none; color: #333; border-radius: 4px; background: #fff; cursor: pointer; }
        .btn:hover { background: #f2f2f2; }
        .errors { color: #b00020; margin-top: 12px; }
        .success { color: #0a7a0a; margin-top: 12px; }
    </style>
</head>
<body>
    <h2>Send Email</h2>

    <?php if ($success): ?>
        <div class="success">Email sent.</div>
    <?php endif; ?>

    <?php if ($errors): ?>
        <div class="errors">
            <?php foreach ($errors as $error): ?>
                <div><?php echo htmlspecialchars($error, ENT_QUOTES, 'UTF-8'); ?></div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

    <form method="post" action="">
        <label for="name">Name</label>
        <input id="name" name="name" type="text" value="<?php echo htmlspecialchars($_POST['name'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">

        <label for="email">Email</label>
        <input id="email" name="email" type="email" value="<?php echo htmlspecialchars($_POST['email'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">

        <label for="message">Message</label>
        <textarea id="message" name="message" rows="5"><?php echo htmlspecialchars($_POST['message'] ?? '', ENT_QUOTES, 'UTF-8'); ?></textarea>

        <div class="actions">
            <button class="btn" type="submit">Send</button>
            <a class="btn" href="index.php">Back</a>
        </div>
    </form>
</body>
</html>
