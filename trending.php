<?php
session_name("USER_SESSION");
session_start();
require 'db.php';

// Fix: Cleaned up PHP logic to remove non-breaking spaces (U+00A0)

// Fetch top 10 trending songs
$stmt = $pdo->prepare("
    SELECT s.*, a.username AS artist_name 
    FROM songs s
    JOIN artists a ON s.artist_id = a.id
    WHERE s.status='approved'
    ORDER BY s.play_count DESC
    LIMIT 10
");
$stmt->execute();
$topSongs = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Collect unique genre_ids, category_ids, artist_ids from top songs
$genre_ids = [];
$category_ids = [];
$artist_ids = [];

foreach ($topSongs as $song) {
    if ($song['genre_id']) $genre_ids[] = $song['genre_id'];
    if ($song['category_id']) $category_ids[] = $song['category_id'];
    if ($song['artist_id']) $artist_ids[] = $song['artist_id'];
}

// Make IDs unique
$genre_ids = array_unique($genre_ids);
$category_ids = array_unique($category_ids);
$artist_ids = array_unique($artist_ids);

// Helper function to fetch details from IN clause
function fetchDetails($pdo, $table, $ids) {
    if (!$ids) return [];
    // Clean string repeat to prevent syntax errors
    $in = str_repeat('?,', count($ids) - 1) . '?';
    $stmt = $pdo->prepare("SELECT * FROM $table WHERE id IN ($in)");
    $stmt->execute($ids);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// Fetch related genres
$genres = fetchDetails($pdo, 'genres', $genre_ids);

// Fetch related categories
$categories = fetchDetails($pdo, 'categories', $category_ids);

// Fetch related artists
$artists = fetchDetails($pdo, 'artists', $artist_ids);

// Fetch user's playlists only if logged in (needed for the Add button)
$user_playlists = [];
if (isset($_SESSION['user_id'])) {
    $stmt = $pdo->prepare("SELECT * FROM user_playlists WHERE user_id=? ORDER BY created_at DESC");
    $stmt->execute([$_SESSION['user_id']]);
    $user_playlists = $stmt->fetchAll(PDO::FETCH_ASSOC);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Sangeet - Trending</title>
<style>
/* ======================================================
  SPOTIFY-INSPIRED CSS 
======================================================
*/
* {
  margin: 0;
  padding: 0;
  box-sizing: border-box;
  font-family: 'Poppins', sans-serif;
}

body {
  background-color: #121212; 
  color: #fff;
  overflow-x: hidden;
}

/* 🔹 Navigation Bar */
nav {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 16px 32px;
  background: #000000;
  position: sticky;
  top: 0;
  z-index: 999;
}

nav h1 {
  font-size: 24px;
  font-weight: 700;
  color: #1db954; /* Spotify Green */
}

nav ul {
  list-style: none;
  display: flex;
  gap: 24px;
}

nav ul li a {
  text-decoration: none;
  color: #b3b3b3;
  font-weight: 500;
  transition: 0.3s;
}

nav ul li a:hover {
  color: #fff;
}

/* 🔹 Header/Banner Area */
.banner {
  /* Using padding-top to create the initial space below the nav */
  padding: 30px 32px 10px;
  background: linear-gradient(to bottom, #1f1f1f, #121212); /* Subtle gradient for depth */
  display: block;
  text-align: left;
}

.banner-content h2 {
  color: #fff;
  font-size: 32px;
  margin-bottom: 20px;
}

.banner-content form {
  /* Search should be more like a component than a full banner feature */
  display: inline-flex;
}

.banner-content input {
  padding: 10px 16px;
  width: 300px;
  border-radius: 50px; /* Fully rounded */
  border: 1px solid #535353;
  outline: none;
  background: #2a2a2a;
  color: #fff;
  font-size: 14px;
}

.banner-content button {
  /* Integrated search button visually into the input style */
  padding: 10px 18px;
  border: none;
  border-radius: 50px;
  background: #1db954; /* Spotify Green */
  color: #000;
  font-weight: 700;
  cursor: pointer;
  margin-left: -45px; /* Overlap search button for better integration */
  transition: 0.3s;
}

.banner-content button:hover {
  background: #1ed760; /* Slightly brighter green on hover */
}

/* 🔹 Section/Results */
.section {
  padding: 20px 32px;
}

.section h2 {
  font-size: 24px;
  font-weight: 700;
  margin-bottom: 15px;
  color: #fff;
}

/* 🔹 Horizontal Scrolling List (Shelf) */
.horizontal-scroll {
  display: flex;
  overflow-x: auto;
  gap: 24px; 
  padding: 10px 0;
  scrollbar-width: none;
}

.horizontal-scroll::-webkit-scrollbar {
  display: none; 
}

/* 🔹 Cards (Songs, Categories, Artists) */
.song-card, .card {
  background: #181818; 
  border-radius: 8px; 
  padding: 16px;
  flex: 0 0 auto;
  width: 180px; 
  cursor: pointer;
  transition: background-color 0.3s ease;
}

.song-card:hover, .card:hover {
  background: #282828; 
}

.song-card img {
  width: 100%;
  height: auto;
  aspect-ratio: 1 / 1; 
  border-radius: 4px; 
  object-fit: cover;
  margin-bottom: 12px;
  box-shadow: 0 8px 24px rgba(0,0,0,.5);
}

.card img {
  width: 100%;
  height: auto;
  aspect-ratio: 1 / 1;
  border-radius: 50%; /* Artists circle */
  object-fit: cover;
  margin-bottom: 12px;
  box-shadow: 0 8px 24px rgba(0,0,0,.5);
}

.song-card:nth-child(1) .song-title,
.song-card:nth-child(2) .song-title,
.song-card:nth-child(3) .song-title {
    color: #1db954; /* Highlight top 3 */
}

.song-title, .card-title {
  color: #fff;
  font-weight: 600;
  font-size: 16px; 
  margin-top: 5px;
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
}

.song-artist, .card-sub {
  font-size: 14px;
  color: #b3b3b3; 
  margin-top: 2px;
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
}

/* Buttons inside cards */
.song-card button {
  padding: 8px 12px;
  border: none;
  border-radius: 50px;
  background: #1db954; 
  color: #000;
  font-size: 14px;
  font-weight: 700;
  cursor: pointer;
  transition: 0.3s;
}

.song-card button:hover {
  background: #1ed760; 
}

.song-card .add-playlist {
    background: #535353;
    color: #fff;
}
.song-card .add-playlist:hover {
    background: #6e6e6e;
}


/* 🔹 Player Bar */
.player {
  position: fixed;
  bottom: 0;
  left: 0;
  right: 0;
  background: #181818;
  display: none; 
  align-items: center;
  justify-content: space-between;
  padding: 10px 20px;
  box-shadow: 0 -2px 10px rgba(0,0,0,0.6);
  z-index: 1000;
}

.now-playing {
  flex: 1;
  margin-left: 15px;
  font-size: 16px;
  color: #fff;
  font-weight: 600;
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
}

audio {
  flex: 1;
  max-width: 400px; 
  margin: 0 20px;
}

.close-player {
  color: #b3b3b3;
  cursor: pointer;
  margin-left: 10px;
  font-size: 20px;
  opacity: 0.7;
  transition: opacity 0.3s;
}

.close-player:hover {
    opacity: 1;
    color: #fff;
}

/* 🔹 Modal Styling */
#create-playlist-modal {
  display: none;
  position: fixed;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
  background: rgba(0,0,0,0.85);
  backdrop-filter: blur(6px);
  justify-content: center;
  align-items: center;
  z-index: 9999;
}

#create-playlist-modal .modal-content {
    background: #282828;
    border-radius: 12px;
    padding: 30px;
    width: 320px;
    text-align: center;
    box-shadow: 0 10px 30px rgba(0,0,0,0.8);
}

#playlist-select, #new-playlist-name {
    padding: 10px;
    border-radius: 4px;
    border: 1px solid #535353;
    background: #121212;
    color: #fff;
    width: 100%;
    margin-top: 10px;
    box-sizing: border-box;
}

#create-playlist-modal button {
    padding: 10px 15px;
    margin-top: 15px;
    border-radius: 50px;
    font-weight: 700;
}
#create-playlist-modal button:last-child {
    background: #535353;
}

/* 🔹 Footer */
footer {
    background-color: #000000;
    color: #b3b3b3;
    padding: 30px 32px 80px; 
    margin-top: 40px;
    border-top: 1px solid #282828;
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    flex-wrap: wrap;
    gap: 20px;
}

footer .footer-col {
    width: 200px;
}

footer h4 {
    color: #fff;
    font-size: 16px;
    margin-bottom: 15px;
    font-weight: 700;
}

footer ul {
    list-style: none;
}

footer ul li {
    margin-bottom: 8px;
}

footer ul li a {
    text-decoration: none;
    color: #b3b3b3;
    font-size: 14px;
    transition: color 0.3s;
}

footer ul li a:hover {
    color: #1db954; 
}

footer .copyright {
    width: 100%;
    text-align: center;
    padding-top: 20px;
    border-top: 1px solid #282828;
    margin-top: 20px;
    font-size: 12px;
    color: #727272;
}


/* 🔹 Bottom Padding for Scroll */
body::after {
  content: "";
  display: block;
  height: 5px; 
}
</style>
</head>
<body>

<nav>
<h1>Sangeet</h1>
<div class="banner" style="background:#000000">
<div class="banner-content">
<form action="search.php" method="GET">
<input type="text" name="q" placeholder="Search songs, artists, or categories">
<button type="submit">🔍</button>
</form>
</div>
</div>
<ul>
<li><a href="index.php">Home</a></li>
<li><a href="trending.php">Trending</a></li>
<li><a href="playlists.php">Playlists</a></li>
<li><a href="feedback.php">Feedback</a></li>
<li><a href="profile.php">Profile</a></li>
<li><a href="logout.php">Logout</a></li>
</ul>
</nav>

<div class="banner">
    <div class="banner-content">
        <h2 style="color: #1db954; font-size: 40px;">Global Top 10</h2>
        <p style="color: #fff; margin-left: 32px; font-size: 18px;">The most popular songs right now, based on play count.</p>
    </div>
</div>

<section class="section">
    <h2>🎶 Top Trending Songs</h2>
    <div class="horizontal-scroll">
    <?php foreach($topSongs as $song): ?>
    <div class="song-card">
        <img src="admin/uploads/songs/<?= htmlspecialchars($song['cover_image'] ?: 'default_song.png') ?>" alt="<?= htmlspecialchars($song['title']) ?>">
        <div class="song-title"><?= htmlspecialchars($song['title']) ?></div>
        <div class="song-artist"><?= htmlspecialchars($song['artist_name']) ?></div>

        <div style="margin-top:10px; display: flex; justify-content: space-between; gap: 5px;">
            <button style="flex: 1;" onclick="playSong('admin/uploads/songs/<?= htmlspecialchars($song['file_path']) ?>','<?= addslashes($song['title']) ?>', <?= $song['id'] ?>)">▶ Play</button>
            <?php if(isset($_SESSION['user_id'])): ?>
            <button style="flex: 1;" class="add-playlist" onclick="openPlaylistModal(<?= $song['id'] ?>)">➕ Add</button>
            <?php endif; ?>
        </div>
    </div>
    <?php endforeach; ?>
    </div>
</section>

<section class="section">
    <h2>🎵 Related Genres</h2>
    <div class="horizontal-scroll">
    <?php foreach($genres as $genre): ?>
    <div class="card" onclick="window.location.href='search.php?genre=<?= $genre['id'] ?>'">
        <img src="admin/uploads/<?= htmlspecialchars($genre['image'] ?: 'default_genre.png') ?>" alt="<?= htmlspecialchars($genre['name']) ?>">
        <div class="card-title" style="border-radius: 0;"><?= htmlspecialchars($genre['name']) ?></div>
    </div>
    <?php endforeach; ?>
    </div>
</section>

<section class="section">
    <h2>✨ Related Categories</h2>
    <div class="horizontal-scroll">
    <?php foreach($categories as $cat): ?>
    <div class="card" onclick="window.location.href='search.php?category=<?= $cat['id'] ?>'">
        <img src="admin/uploads/<?= htmlspecialchars($cat['image'] ?: 'default_category.png') ?>" alt="<?= htmlspecialchars($cat['name']) ?>">
        <div class="card-title" style="border-radius: 0;"><?= htmlspecialchars($cat['name']) ?></div>
    </div>
    <?php endforeach; ?>
    </div>
</section>

<section class="section">
    <h2>🎤 Featured Artists</h2>
    <div class="horizontal-scroll">
    <?php foreach($artists as $artist): ?>
    <div class="card" onclick="window.location.href='search.php?artist=<?= $artist['id'] ?>'">
        <img src="admin/<?= htmlspecialchars($artist['profile_pic'] ?: 'default_artist.png') ?>" alt="<?= htmlspecialchars($artist['username']) ?>" style="border-radius: 50%;">
        <div class="card-title" style="text-align: center;"><?= htmlspecialchars($artist['username']) ?></div>
        <div class="card-sub" style="text-align: center;">Artist</div>
    </div>
    <?php endforeach; ?>
    </div>
</section>

<footer>
    <div class="footer-col">
        <h4>Company</h4>
        <ul>
            <li><a href="#">About</a></li>
            <li><a href="#">Careers</a></li>
            <li><a href="#">For the Record</a></li>
        </ul>
    </div>
    <div class="footer-col">
        <h4>Communities</h4>
        <ul>
            <li><a href="#">Artists</a></li>
            <li><a href="#">Developers</a></li>
            <li><a href="#">Advertising</a></li>
        </ul>
    </div>
    <div class="footer-col">
        <h4>Useful links</h4>
        <ul>
            <li><a href="feedback.php">Support</a></li>
            <li><a href="#">Free Mobile App</a></li>
        </ul>
    </div>
    <div class="copyright">
        &copy; <?= date('Y'); ?> Sangeet Music Player. All rights reserved.
    </div>
</footer>
<div id="create-playlist-modal">
  <div class="modal-content">
    <h3>Add to Playlist</h3>
    <select id="playlist-select">
        <option value="">--Select Playlist--</option>
        <?php foreach($user_playlists as $pl): ?>
        <option value="<?= $pl['id'] ?>"><?= htmlspecialchars($pl['name']) ?></option>
        <?php endforeach; ?>
        <option value="new">➕ Create New Playlist</option>
    </select>
    <input type="text" id="new-playlist-name" placeholder="New Playlist Name" style="display:none;margin-top:10px;">
    <br><br>
    <button onclick="saveToPlaylist()">Save</button>
    <button onclick="closeModal()">Cancel</button>
  </div>
</div>

<div class="player" id="player">
<div class="now-playing" id="now-playing">Select a song to play 🎶</div>
<audio id="audio" controls></audio>
<div class="close-player" onclick="closePlayer()">✖</div>
</div>

<script>
const audio = document.getElementById('audio');
const nowPlaying = document.getElementById('now-playing');
const player = document.getElementById('player');
const modal = document.getElementById('create-playlist-modal');
const playlistSelect = document.getElementById('playlist-select');
const newPlaylistInput = document.getElementById('new-playlist-name');
let selectedSongId = null;

function playSong(file, title, songId){
    audio.src = file;
    audio.play();
    nowPlaying.textContent = 'Now Playing: ' + title + ' 🎧';
    player.style.display = 'flex';
    // Increment play count
    fetch('increment_play.php?song_id=' + songId).then(res=>res.text());
}

function closePlayer(){
    audio.pause();
    audio.src = '';
    player.style.display = 'none';
}

function openPlaylistModal(songId){
    selectedSongId = songId;
    modal.style.display = 'flex';
}

function closeModal(){
    modal.style.display = 'none';
    playlistSelect.value = '';
    newPlaylistInput.style.display = 'none';
    newPlaylistInput.value = '';
}

playlistSelect.addEventListener('change', function(){
    if(this.value === 'new'){
        newPlaylistInput.style.display = 'block';
    } else {
        newPlaylistInput.style.display = 'none';
    }
});

function saveToPlaylist(){
    const playlistId = playlistSelect.value;
    if(!playlistId){ alert('Please select or create a playlist'); return; }

    if(playlistId === 'new'){
        const name = newPlaylistInput.value.trim();
        if(!name){ alert('Enter playlist name'); return; }

        fetch('create_playlist.php', {
            method:'POST',
            headers:{'Content-Type':'application/x-www-form-urlencoded'},
            body:'name='+encodeURIComponent(name)
        })
        .then(res=>res.text())
        .then(newId=>{
            // Assuming create_playlist.php returns the new playlist ID
            if(isNaN(parseInt(newId))){
                alert("Error creating playlist: " + newId);
                return;
            }
            addSongToPlaylist(selectedSongId, newId);
        });
    } else {
        addSongToPlaylist(selectedSongId, playlistId);
    }
}
function addSongToPlaylist(songId, playlistId){
    fetch('add_to_playlist.php?song_id=' + songId + '&playlist_id=' + playlistId)
    .then(res => res.text())
    .then(data => {
        alert(data);
        closeModal();
    });
}
</script>

</body>
</html>