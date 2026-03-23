<?php
session_name("USER_SESSION");
session_start();
require 'db.php';
if (!isset($_SESSION['user_id'])) header("Location: login.php");
$user_id = $_SESSION['user_id'];

// Fetch user's playlists
$stmt = $pdo->prepare("SELECT * FROM user_playlists WHERE user_id=? ORDER BY created_at DESC");
$stmt->execute([$user_id]);
$playlists = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Sangeet - My Playlists</title>
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

/* 🔹 Header/Title Section */
.header-section {
    padding: 30px 32px 20px;
    background: linear-gradient(to bottom, #1f1f1f, #121212);
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.header-section h2 {
    font-size: 32px;
    color: #fff;
    margin: 0;
}

/* --- MODAL TRIGGER STYLES --- */
.create-btn {
    padding: 10px 20px;
    border: none;
    border-radius: 50px;
    background: #1db954;
    color: #000;
    font-weight: 700;
    text-decoration: none;
    cursor: pointer; /* Added cursor pointer */
    transition: background-color 0.3s;
}

.create-btn:hover {
    background: #1ed760;
}
/* ---------------------------- */


/* 🔹 Playlist Container and Cards */
.playlist-container {
    padding: 20px 32px;
    display: grid;
    gap: 15px;
}

.playlist {
    background: #181818;
    padding: 20px;
    border-radius: 8px;
    cursor: pointer;
    transition: background-color 0.3s;
}

.playlist:hover {
    background: #282828;
}

.playlist h3 {
    margin: 0;
    font-size: 18px;
    color: #fff;
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.playlist h3 span {
    font-size: 14px;
    transition: transform 0.3s;
}

.playlist[aria-expanded="true"] h3 span {
    transform: rotate(180deg);
}

.song-list {
    display: none;
    margin-top: 15px;
    border-top: 1px solid #282828;
    padding-top: 10px;
}

.song-card {
    background: #282828;
    padding: 10px;
    margin: 8px 0;
    border-radius: 4px;
    display: flex;
    align-items: center;
    justify-content: space-between;
}

.song-card img {
    width: 40px;
    height: 40px;
    object-fit: cover;
    border-radius: 4px;
    margin-right: 15px;
}

.song-info {
    flex: 1;
    color: #fff;
    font-size: 14px;
    font-weight: 500;
}

.song-artist {
    display: block;
    color: #b3b3b3;
    font-size: 12px;
}

button.play-btn {
    padding: 8px 15px;
    border: none;
    border-radius: 50px;
    background: #1db954;
    color: #000;
    font-weight: 700;
    cursor: pointer;
    transition: background-color 0.3s;
}

button.play-btn:hover {
    background: #1ed760;
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

/* --- NEW MODAL STYLES --- */
#create-playlist-modal {
    display: none; /* Hidden by default */
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(0,0,0,0.85);
    backdrop-filter: blur(4px);
    justify-content: center;
    align-items: center;
    z-index: 9999;
}

#create-playlist-modal .modal-content {
    background: #282828;
    border-radius: 12px;
    padding: 30px;
    width: 300px;
    text-align: center;
    box-shadow: 0 10px 30px rgba(0,0,0,0.8);
}

#playlist-name-input {
    padding: 10px;
    border-radius: 4px;
    border: 1px solid #535353;
    background: #121212;
    color: #fff;
    width: 100%;
    margin: 15px 0;
    box-sizing: border-box;
}

#create-playlist-modal .modal-content button {
    padding: 10px 15px;
    margin: 0 5px;
    border: none;
    border-radius: 50px;
    font-weight: 700;
    cursor: pointer;
    transition: 0.3s;
}

#create-playlist-modal .modal-content button:first-of-type {
    background: #1db954; /* Create Button */
    color: #000;
}
#create-playlist-modal .modal-content button:last-of-type {
    background: #535353; /* Cancel Button */
    color: #fff;
}
/* -------------------------- */
</style>
</head>
<body>

<nav>
<h1 style="margin-right: auto;">Sangeet</h1>
<ul>
<li><a href="index.php">Home</a></li>
<li><a href="trending.php">Trending</a></li>
<li><a href="playlists.php">Playlists</a></li>
<li><a href="feedback.php">Feedback</a></li>
<li><a href="profile.php">Profile</a></li>
<li><a href="logout.php">Logout</a></li>
</ul>
</nav>

<div class="header-section">
    <h2>My Playlists</h2>
    <div class="create-btn" onclick="openCreatePlaylistModal()">➕ New Playlist</div>
</div>

<div class="playlist-container">
<?php if(!$playlists) echo "<p style='text-align:center; padding: 20px;'>You haven't created any playlists yet. Click 'New Playlist' to start!</p>"; ?>

<?php foreach($playlists as $pl): ?>
<?php
// Fetch songs for the current playlist
$stmt = $pdo->prepare("
    SELECT s.*, a.username AS artist_name
    FROM playlist_songs ps
    JOIN songs s ON ps.song_id = s.id
    JOIN artists a ON s.artist_id = a.id
    WHERE ps.playlist_id=?
");
$stmt->execute([$pl['id']]);
$songs = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<div class="playlist" onclick="togglePlaylist('songs-<?= $pl['id'] ?>', this)" aria-expanded="false">
    <h3><?= htmlspecialchars($pl['name']) ?> <span class="toggle-icon">▼</span></h3>
    <div class="song-list" id="songs-<?= $pl['id'] ?>">
        <?php if(!$songs): ?>
        <p style='color: #b3b3b3;'>No songs in this playlist. Go to the Home page to add some!</p>
        <?php endif; ?>
        <?php foreach($songs as $song): ?>
        <div class="song-card">
            <img src="admin/uploads/songs/<?= htmlspecialchars($song['cover_image'] ?: 'default_song.png') ?>" alt="<?= htmlspecialchars($song['title']) ?>">
            <div class="song-info">
                <?= htmlspecialchars($song['title']) ?>
                <span class="song-artist"><?= htmlspecialchars($song['artist_name']) ?></span>
            </div>
            <button class="play-btn" onclick="playSong(event,'admin/uploads/songs/<?= htmlspecialchars($song['file_path']) ?>','<?= addslashes($song['title']) ?>', <?= $song['id'] ?>)">▶ Play</button>
        </div>
        <?php endforeach; ?>
    </div>
</div>
<?php endforeach; ?>
</div>

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
    <h3>Create New Playlist</h3>
    <input type="text" id="playlist-name-input" placeholder="Enter playlist name">
    <button onclick="createPlaylist()">Create</button>
    <button onclick="closeCreatePlaylistModal()">Cancel</button>
  </div>
</div>
<div class="player" id="player">
<div class="now-playing" id="now-playing">Select a song to play 🎶</div>
<audio id="audio" controls></audio>
<div class="close-player" onclick="closePlayer()">✖</div>
</div>

<script>
// --- GLOBAL ELEMENTS ---
const audio = document.getElementById('audio');
const nowPlaying = document.getElementById('now-playing');
const player = document.getElementById('player');
const modal = document.getElementById('create-playlist-modal');
const nameInput = document.getElementById('playlist-name-input');


// --- PLAYLIST FUNCTIONALITY ---

function togglePlaylist(id, element){
    const el = document.getElementById(id);
    const isExpanded = element.getAttribute('aria-expanded') === 'true';

    if(isExpanded) {
        el.style.display = 'none';
        element.setAttribute('aria-expanded', 'false');
    } else {
        el.style.display = 'block';
        element.setAttribute('aria-expanded', 'true');
    }
}

// --- PLAYER FUNCTIONALITY ---

function playSong(event, file, title, songId){
    event.stopPropagation(); 
    audio.src = file;
    audio.play();
    nowPlaying.textContent = 'Now Playing: ' + title + ' 🎧';
    player.style.display = 'flex';

    // Increment play count (assuming you have this file)
    fetch('increment_play.php?song_id=' + songId).then(res=>res.text());
}

function closePlayer(){
    audio.pause();
    audio.src = '';
    player.style.display = 'none';
}


// --- NEW MODAL FUNCTIONALITY ---

function openCreatePlaylistModal() {
    modal.style.display = 'flex';
    nameInput.focus();
}

function closeCreatePlaylistModal() {
    modal.style.display = 'none';
    nameInput.value = ''; // Clear input on cancel
}

function createPlaylist() {
    const playlistName = nameInput.value.trim();
    if (!playlistName) {
        alert("Please enter a name for your new playlist.");
        return;
    }

    // AJAX call to create_playlist.php
    fetch('create_playlist.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: 'name=' + encodeURIComponent(playlistName)
    })
    .then(response => response.text())
    .then(data => {
        // Assuming create_playlist.php returns a success message or the new ID
        alert("Playlist created successfully! You may need to refresh to see it.");
        closeCreatePlaylistModal();
        // For a seamless update, you would typically reload the playlist-container content 
        // using AJAX here, but a simple page refresh is usually sufficient for a basic setup.
        window.location.reload(); 
    })
    .catch(error => {
        console.error('Error:', error);
        alert("An error occurred while creating the playlist.");
    });
}
</script>

</body>
</html>