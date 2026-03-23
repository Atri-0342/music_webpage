<?php
session_name("USER_SESSION");
session_start();
require 'db.php';

// Fetch trending songs (top 5 by play_count)
$stmt = $pdo->prepare("SELECT s.*, a.username AS artist_name 
                        FROM songs s
                        JOIN artists a ON s.artist_id = a.id
                        ORDER BY s.play_count DESC, RAND() LIMIT 5");
$stmt->execute();
$songs = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Fetch random categories (limit 5)
$stmt = $pdo->prepare("SELECT * FROM categories ORDER BY RAND() LIMIT 5");
$stmt->execute();
$categories = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Fetch random genres (limit 5)
$stmt = $pdo->prepare("SELECT * FROM genres ORDER BY RAND() LIMIT 5");
$stmt->execute();
$genres = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Fetch random artists (limit 5)
$stmt = $pdo->prepare("SELECT * FROM artists ORDER BY RAND() LIMIT 5");
$stmt->execute();
$artists = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Fetch user's playlists only if logged in
$playlists = [];
if (isset($_SESSION['user_id'])) {
    $user_id = $_SESSION['user_id'];
    $stmt = $pdo->prepare("SELECT * FROM user_playlists WHERE user_id=? ORDER BY created_at DESC");
    $stmt->execute([$user_id]);
    $playlists = $stmt->fetchAll(PDO::FETCH_ASSOC);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Sangeet</title>
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
  /* Spotify's background is a very dark gray/black */
  background-color: #121212; 
  color: #fff;
  overflow-x: hidden;
}

/* 🔹 Navigation Bar (Top Bar) */
nav {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 16px 32px;
  background: #000000; /* Darker black for the top bar */
  position: sticky;
  top: 0;
  z-index: 999;
  border-bottom: none; /* Removed border */
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
  color: #b3b3b3; /* Gray for subtle text */
  font-weight: 500;
  transition: 0.3s;
}

nav ul li a:hover {
  color: #fff; /* White on hover */
}

/* 🔹 Banner (Replaced with a simple header area like the "Good evening" section) */
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

/* 🔹 Section Titles */
.section {
  padding: 20px 32px;
}

.section h2 {
  font-size: 24px;
  font-weight: 700;
  margin-bottom: 15px;
  color: #fff;
}

/* 🔹 Horizontal Scrolling Lists/Shelves */
.horizontal-scroll {
  display: flex;
  overflow-x: auto;
  gap: 24px; /* Increased gap */
  padding: 10px 0;
  /* Remove custom scrollbar styling for a cleaner look */
  scrollbar-width: none;
}

.horizontal-scroll::-webkit-scrollbar {
  display: none; /* Hide scrollbar for a modern feel */
}


/* 🔹 Cards (Songs, Categories, Artists) */
.song-card, .card {
  background: #181818; /* Card background is slightly lighter than body */
  border-radius: 8px; /* Slightly rounded corners */
  padding: 16px;
  flex: 0 0 auto;
  width: 180px; /* Wider cards */
  cursor: pointer;
  transition: background-color 0.3s ease, transform 0.1s;
}

.song-card:hover, .card:hover {
  background: #282828; /* Lighter background on hover */
  transform: none; /* Removed the translateY lift */
}

.song-card img {
  width: 100%;
  height: auto;
  aspect-ratio: 1 / 1; /* Ensures square images for album/track covers */
  border-radius: 4px; 
  object-fit: cover;
  margin-bottom: 12px;
  box-shadow: 0 8px 24px rgba(0,0,0,.5); /* Subtle shadow for depth */
}

.card img {
  /* Different shape for non-song cards (like artists/genres) */
  width: 100%;
  height: auto;
  aspect-ratio: 1 / 1;
  border-radius: 50%; /* Circle for artists, square for others - adjust as needed */
  object-fit: cover;
  margin-bottom: 12px;
  box-shadow: 0 8px 24px rgba(0,0,0,.5);
}

.song-title, .card-title {
  color: #fff;
  font-weight: 600;
  font-size: 16px; /* Slightly larger title */
  margin-top: 5px;
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
}

.song-artist, .card-sub {
  font-size: 14px;
  color: #b3b3b3; /* Gray for artist/sub text */
  margin-top: 2px;
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
}

/* 🔹 Play/Add Buttons inside cards */
.song-card button, .card button {
  padding: 8px 15px;
  border: none;
  border-radius: 50px;
  background: #1db954; 
  color: #000;
  font-size: 14px;
  font-weight: 700;
  cursor: pointer;
  transition: 0.3s;
  display: inline-block;
}

.song-card button:hover, .card button:hover {
  background: #1ed760; 
}

/* 🔹 Playlist Modal */
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
  z-index: 9999; /* Higher z-index for modal */
}

#create-playlist-modal .modal-content {
    background: #282828; /* Darker grey for modal background */
    border-radius: 12px;
    padding: 30px;
    width: 320px; /* Slightly wider modal */
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


/* 🔹 Player Bar (Fixed Bottom) */
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
  border-top: none; 
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


/* 🔹 Bottom Padding for Scroll */
body::after {
  content: "";
  display: block;
  height: 80px; /* Increased height to account for the player bar */
}
/* 🔹 Footer */
footer {
    background-color: #000000; /* Match the nav/darkest part of the theme */
    color: #b3b3b3;
    padding: 30px 32px 80px; /* Increased bottom padding to prevent player overlap */
    margin-top: 40px;
    border-top: 1px solid #282828;
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    flex-wrap: wrap;
    gap: 20px;
}

footer .footer-col {
    width: 200px; /* Fixed width for columns on desktop */
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
    color: #1db954; /* Spotify Green on hover */
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


/* 🔹 Responsive */
@media (max-width: 768px) {
  nav {
    padding: 12px 20px;
  }
  nav ul {
    display: none; /* Hide nav links on small screens */
  }
  .banner, .section {
    padding: 20px;
  }
  .banner-content h2 {
    font-size: 26px;
  }
  .banner-content input {
    width: 200px;
  }
  .song-card, .card {
    width: 150px;
  }
  .song-card img, .card img {
    height: auto;
  }
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
<?php if (isset($_SESSION['user_id'])) { ?>
    <li><a href="profile.php">Profile</a></li>
    <li><a href="logout.php">Logout</a></li>
<?php } else { ?>
    <li><a href="registration.php">Register</a></li>
    <li><a href="login.php">Login</a></li>
<?php } ?>

</ul>
</nav>

<div class="banner">
<div class="banner-content">
<div style="color:#fff;font-size:24px;font-weight:700;margin-bottom:15px;">Good evening, <?= htmlspecialchars($_SESSION['username'] ?? 'Guest'); ?>!</div>

</div>
</div>

<section class="section">
<h2>🔥 Trending Songs</h2>
<div class="horizontal-scroll">
<?php foreach($songs as $song): ?>
<div class="song-card">
<img src="admin/uploads/songs/<?= htmlspecialchars($song['cover_image'] ?: 'default_song.png') ?>" alt="<?= htmlspecialchars($song['title']) ?>">
<div class="song-title"><?= htmlspecialchars($song['title']) ?></div>
<div class="song-artist"><?= htmlspecialchars($song['artist_name']) ?></div>

<div style="margin-top:10px; display: flex; justify-content: space-between; gap: 5px;">
<button style="flex: 1;" onclick="playSong('admin/uploads/songs/<?= htmlspecialchars($song['file_path']) ?>','<?= addslashes($song['title']) ?>', <?= $song['id'] ?>)">▶ Play</button>
<button style="flex: 1;" onclick="openPlaylistModal(<?= $song['id'] ?>)">➕ Add</button>
</div>
</div>
<?php endforeach; ?>
</div>
</section>

<section class="section">
<h2>🎤 Popular Artists</h2>
<div class="horizontal-scroll">
<?php foreach($artists as $artist): ?>
<div class="card" onclick="window.location.href='search.php?artist=<?= $artist['id'] ?>'">
<img src="admin/<?= htmlspecialchars($artist['profile_pic'] ?: 'default_artist.png') ?>" alt="<?= htmlspecialchars($artist['username']) ?>">
<div class="card-title" style="text-align: center;"><?= htmlspecialchars($artist['username']) ?></div>
<div class="card-sub" style="text-align: center;">Artist</div>
</div>
<?php endforeach; ?>
</div>
</section>


<section class="section">
<h2>🎧 Explore Genres</h2>
<div class="horizontal-scroll">
<?php foreach($genres as $genre): ?>
<div class="card" onclick="window.location.href='search.php?genre=<?= $genre['id'] ?>'">
<img src="admin/uploads/<?= htmlspecialchars($genre['image'] ?: 'default_genre.png') ?>" alt="<?= htmlspecialchars($genre['name']) ?>">
<div class="card-title"><?= htmlspecialchars($genre['name']) ?></div>
</div>
<?php endforeach; ?>
</div>
</section>

<section class="section">
<h2>✨ Featured Categories</h2>
<div class="horizontal-scroll">
<?php foreach($categories as $cat): ?>
<div class="card" onclick="window.location.href='search.php?category=<?= $cat['id'] ?>'">
<img src="admin/uploads/<?= htmlspecialchars($cat['image'] ?: 'default_category.png') ?>" alt="<?= htmlspecialchars($cat['name']) ?>">
<div class="card-title"><?= htmlspecialchars($cat['name']) ?></div>
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
        <?php foreach($playlists as $pl): ?>
        <option value="<?= $pl['id'] ?>"><?= htmlspecialchars($pl['name']) ?></option>
        <?php endforeach; ?>
        <option value="new">➕ Create New Playlist</option>
    </select>
    <input type="text" id="new-playlist-name" placeholder="New Playlist Name" style="display:none;">
    <br style="margin-top:10px;">
    <button style="margin-top: 15px;" onclick="saveToPlaylist()">Save</button>
    <button style="background: #535353;" onclick="closeModal()">Cancel</button>
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
    // Send request to increment play count
    fetch('increment_play.php?song_id=' + songId).then(res => res.text());
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
    fetch('add_to_playlist.php?song_id='+songId+'&playlist_id='+playlistId)
        .then(res=>res.text())
        .then(msg=>{
            alert(msg);
            closeModal();
        });
}
</script>

</body>
</html>