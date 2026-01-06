<?php
declare(strict_types=1);

require_once __DIR__ . '/functions.php';

$rows = get_submissions($pdo);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>List</title>
    <style>
        body { font-family: Arial, sans-serif; padding: 32px; }
        table { border-collapse: collapse; width: 100%; max-width: 960px; }
        th, td { border: 1px solid #333; padding: 8px; text-align: left; }
        th { background: #f2f2f2; }
        .actions { margin-top: 16px; display: flex; gap: 12px; }
        .btn { padding: 10px 16px; border: 1px solid #333; text-decoration: none; color: #333; border-radius: 4px; }
        .btn:hover { background: #f2f2f2; }
    </style>
</head>
<body>
    <h2>Submissions</h2>

    <?php if (!$rows): ?>
        <p>No submissions yet.</p>
    <?php else: ?>
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Name</th>
                    <th>Email</th>
                    <th>Message</th>
                    <th>Created</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($rows as $row): ?>
                    <tr>
                        <td><?php echo htmlspecialchars((string) $row['id'], ENT_QUOTES, 'UTF-8'); ?></td>
                        <td><?php echo htmlspecialchars($row['name'], ENT_QUOTES, 'UTF-8'); ?></td>
                        <td><?php echo htmlspecialchars($row['email'], ENT_QUOTES, 'UTF-8'); ?></td>
                        <td><?php echo htmlspecialchars($row['message'], ENT_QUOTES, 'UTF-8'); ?></td>
                        <td><?php echo htmlspecialchars($row['created_at'], ENT_QUOTES, 'UTF-8'); ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>

    <div class="actions">
        <a class="btn" href="create.php">Create</a>
        <a class="btn" href="index.php">Back</a>
    </div>
</body>
</html>
