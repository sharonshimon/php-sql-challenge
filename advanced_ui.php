<?php
require_once 'init.php';
require_once 'config.php';
require_once 'db/Database.php';

$db = Database::getInstance();

// Advanced Query 1: Latest post for users with birthdays in the current month
$queryBirthday = "
    SELECT p.*, u.name, u.email
    FROM posts p
    JOIN users u ON u.id = p.user_id
    WHERE MONTH(u.birth_date) = MONTH(CURRENT_DATE())
      AND p.created_at = (
          SELECT MAX(p2.created_at)
          FROM posts p2
          WHERE p2.user_id = u.id
      )
      AND u.active = 1
      AND p.active = 1
";
$stmt = $db->rawQuery($queryBirthday);
$birthdayPosts = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Advanced Query 2: Group posts by date and hour, counting posts per group
$queryGrouped = "
    SELECT DATE(created_at) as post_date,
           HOUR(created_at) as post_hour,
           COUNT(*) as post_count
    FROM posts
    GROUP BY DATE(created_at), HOUR(created_at)
    ORDER BY post_date, post_hour
";
$stmt = $db->rawQuery($queryGrouped);
$groupedPosts = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Advanced Queries UI</title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="advanced.css">
</head>
<body>
    <h1>Advanced Queries</h1>
    <div class="advanced-section">
        <h2>Latest Posts for Users with Birthdays This Month</h2>
        <?php if (count($birthdayPosts) > 0): ?>
            <table>
                <thead>
                    <tr>
                        <th>User Name</th>
                        <th>Email</th>
                        <th>Post Title</th>
                        <th>Post Body</th>
                        <th>Created At</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($birthdayPosts as $bp): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($bp['name']); ?></td>
                            <td><?php echo htmlspecialchars($bp['email']); ?></td>
                            <td><?php echo htmlspecialchars($bp['title']); ?></td>
                            <td><?php echo htmlspecialchars($bp['body']); ?></td>
                            <td><?php echo htmlspecialchars($bp['created_at']); ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php else: ?>
            <p>No posts found for users with birthdays this month.</p>
        <?php endif; ?>
    </div>
    <div class="advanced-section">
        <h2>Posts Grouped by Date and Hour</h2>
        <?php if (count($groupedPosts) > 0): ?>
            <table>
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Hour</th>
                        <th>Post Count</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($groupedPosts as $group): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($group['post_date']); ?></td>
                            <td><?php echo htmlspecialchars($group['post_hour']); ?></td>
                            <td><?php echo htmlspecialchars($group['post_count']); ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php else: ?>
            <p>No grouped posts data found.</p>
        <?php endif; ?>
    </div>
</body>
</html>