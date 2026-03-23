<?php
session_name("USER_SESSION");
session_start();
require 'db.php';

if(!isset($_SESSION['user_id'])) exit('User not logged in');

$user_id = $_SESSION['user_id'];  // ✅ Correct session variable
$name = trim($_POST['name'] ?? '');

if(!$name) exit('Playlist name required');

// Insert playlist with session user ID
$stmt = $pdo->prepare("INSERT INTO user_playlists (user_id, name) VALUES (?, ?)");
$stmt->execute([$user_id, $name]);

// Return new playlist ID for JS
echo $pdo->lastInsertId();
?>
