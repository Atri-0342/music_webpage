<?php
require 'db.php';

if(isset($_GET['song_id'])){
    $song_id = (int)$_GET['song_id'];

    // Increment song play count
    $stmt = $pdo->prepare("UPDATE songs SET play_count = play_count + 1 WHERE id=?");
    $stmt->execute([$song_id]);

    // Get song details
    $stmt = $pdo->prepare("SELECT genre_id, category_id, artist_id FROM songs WHERE id=?");
    $stmt->execute([$song_id]);
    $song = $stmt->fetch(PDO::FETCH_ASSOC);

    if($song){
        // Increment related genre play_count
        if($song['genre_id']){
            $stmt = $pdo->prepare("UPDATE genres SET play_count = play_count + 1 WHERE id=?");
            $stmt->execute([$song['genre_id']]);
        }

        // Increment related category play_count
        if($song['category_id']){
            $stmt = $pdo->prepare("UPDATE categories SET play_count = play_count + 1 WHERE id=?");
            $stmt->execute([$song['category_id']]);
        }

        // Increment related artist play_count
        if($song['artist_id']){
            $stmt = $pdo->prepare("UPDATE artists SET play_count = play_count + 1 WHERE id=?");
            $stmt->execute([$song['artist_id']]);
        }
    }

    echo "OK";
}
