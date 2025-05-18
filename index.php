<?php

require_once 'init.php';
require_once 'config.php';
require_once 'db/Database.php';

$db = Database::getInstance();

// Get all active users
$users = $db->select('users', ['id', 'name', 'email'], ['active = 1']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Users and Posts</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <?php foreach ($users as $user): ?>
        <div class="user-card">
            <h3><?php echo "{$user['name']} ({$user['email']})"; ?></h3>
            <img src="data/<?php echo AVATAR_FILE_NAME; ?>" alt="Avatar" class="avatar">
            <?php
                // Get user's posts
                $posts = $db->select('posts', ['title', 'body'], [
                    'user_id = ' . $user['id'],
                    'active = 1'
                ]);
            ?>
            <?php if (count($posts) === 0): ?>
                <p>No posts found.</p>
            <?php else: ?>
                <ul class="posts-list">
                    <?php foreach ($posts as $post): ?>
                        <li>
                            <strong><?php echo htmlspecialchars($post['title']); ?></strong><br>
                            <?php echo htmlspecialchars($post['body']); ?>
                        </li>
                    <?php endforeach; ?>
                </ul>
            <?php endif; ?>
        </div>
    <?php endforeach; ?>
</body>
</html>
