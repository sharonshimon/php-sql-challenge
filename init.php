<?php

require_once 'config.php';

// Ensure database exists
try {
    $pdo = new PDO("mysql:host=" . DB_HOST . ";port=" . DB_PORT . ";charset=utf8mb4", DB_USER, DB_PASS);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->exec("CREATE DATABASE IF NOT EXISTS " . DB_NAME . " CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci");
} catch (PDOException $e) {
    die("Database setup failed: " . $e->getMessage());
}

require_once 'db/Database.php';

$db = Database::getInstance();

// Create users table
$db->createTable('users', [
    ['name' => 'id', 'definition' => 'INT PRIMARY KEY AUTO_INCREMENT'],
    ['name' => 'name', 'definition' => 'VARCHAR(50) NOT NULL'],
    ['name' => 'email', 'definition' => 'VARCHAR(100) NOT NULL UNIQUE'],
    ['name' => 'birth_date', 'definition' => 'DATE DEFAULT NULL'],  // new column
    ['name' => 'active', 'definition' => 'BOOLEAN DEFAULT 1']
]);

// Create posts table
$db->createTable('posts', [
    ['name' => 'id', 'definition' => 'INT PRIMARY KEY AUTO_INCREMENT'],
    ['name' => 'user_id', 'definition' => 'INT NOT NULL'],
    ['name' => 'title', 'definition' => 'VARCHAR(100) NOT NULL'],
    ['name' => 'body', 'definition' => 'TEXT NOT NULL'],
    ['name' => 'created_at', 'definition' => 'DATETIME DEFAULT CURRENT_TIMESTAMP'],
    ['name' => 'active', 'definition' => 'BOOLEAN DEFAULT 1']
], [
    'FOREIGN KEY (user_id) REFERENCES users(id)'
]);

// cURL helper function
function fetchDataUsingCurl(string $url): string {
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    $response = curl_exec($ch);
    if (curl_errno($ch)) {
        die("cURL error: " . curl_error($ch));
    }
    curl_close($ch);
    return $response;
}

// Insert users from API only if no users exist
if (empty($db->select('users'))) {
    $usersData = fetchDataUsingCurl(API_URL . '/users');
    $users = json_decode($usersData, true);
    foreach ($users as $user) {
        $db->insert('users', [
            'name' => $user['name'],
            'email' => $user['email'],
            'active' => 1
        ]);
    }
}

// Insert posts from API only if no posts exist
if (empty($db->select('posts'))) {
    $postsData = fetchDataUsingCurl(API_URL . '/posts');
    $posts = json_decode($postsData, true);
    
    // Get valid user IDs from the users table
    $userRows = $db->select('users', ['id']);
    $validUserIds = array_column($userRows, 'id');
    
    foreach ($posts as $post) {
        if (!in_array($post['userId'], $validUserIds)) {
            continue;
        }
        $db->insert('posts', [
            'user_id' => $post['userId'],
            'title'   => $post['title'],
            'body'    => $post['body'],
            'active'  => 1
        ]);
    }
}

// Save avatar image using cURL
$avatarPath = __DIR__ . '/data/' . AVATAR_FILE_NAME;
if (!file_exists($avatarPath)) {
    // Ensure data directory exists
    $dataDir = __DIR__ . '/data';
    if (!is_dir($dataDir)) {
        mkdir($dataDir, 0755, true);
    }
    file_put_contents($avatarPath, fetchDataUsingCurl(AVATAR_IMAGE_URL));
}


