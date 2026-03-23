<?php
session_name("USER_SESSION");
session_start();
require 'db.php';

$song_id = $_GET['song_id'] ?? null;
$playlist_id = $_GET['playlist_id'] ?? null;

if(!$song_id || !$playlist_id){
    exit('Missing song or playlist ID');
}

try {
    $stmt = $pdo->prepare("INSERT IGNORE INTO playlist_songs (playlist_id, song_id) VALUES (?, ?)");
    $stmt->execute([$playlist_id, $song_id]);
    echo "Song added!";
} catch(Exception $e){
    echo "Error: ".$e->getMessage();
}
?>
