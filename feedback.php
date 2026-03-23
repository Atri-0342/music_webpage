<?php
// Start session
session_name("USER_SESSION");
session_start();
require 'db.php';

$success = '';
$error = '';
$user_name = '';
$user_email = '';
// Ensure $message is defined for the form value attribute
$message = ''; 

if(isset($_SESSION['username'])) {
    // If logged in, prefill name and email
    $user_name = $_SESSION['username'];
    $user_email = $_SESSION['user_email'] ?? '';
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $message = trim($_POST['message']);

    // Re-assign user_name/email from POST to keep the form fields filled after submission attempt
    $user_name = $name;
    $user_email = $email;

    if(empty($name) || empty($email) || empty($message)) {
        $error = "All fields are required.";
    } elseif(!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Invalid email address.";
    } else {
        $stmt = $pdo->prepare("INSERT INTO feedback (user_name, user_email, message) VALUES (?, ?, ?)");
        if($stmt->execute([$name, $email, $message])) {
            $success = "Thank you for your feedback! We appreciate you helping us improve.";
            // Clear message input only on successful submission
            $message = ''; 
            $user_name = $_SESSION['username'] ?? '';
            $user_email = $_SESSION['user_email'] ?? '';
        } else {
            $error = "Something went wrong. Please try again.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Submit Feedback - Sangeet</title>
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
  min-height: 100vh;
  display: flex;
  flex-direction: column;
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

/* 🔹 Container and Form */
.container { 
    max-width: 600px; 
    width: 90%;
    margin: 50px auto; 
    background: #181818; /* Darker than body */
    padding: 30px; 
    border-radius: 10px; 
    box-shadow: 0 4px 12px rgba(0,0,0,0.5); 
    flex-grow: 1; /* Allows container to push footer down */
}

h2 { 
    text-align: center; 
    margin-bottom: 20px; 
    color: #1db954; /* Spotify Green */
    font-size: 28px;
}

form label { 
    display: block; 
    margin: 15px 0 5px; 
    color: #b3b3b3;
    font-weight: 500;
}

form input[type="text"], 
form input[type="email"], 
form textarea {
    width: 100%; 
    padding: 12px; 
    border-radius: 6px; 
    border: 1px solid #535353;
    background: #282828;
    color: #fff; 
    outline: none; 
    font-size: 16px;
    box-sizing: border-box;
    transition: border-color 0.3s;
}

form input:focus, form textarea:focus {
    border-color: #1db954;
}

form textarea { 
    resize: vertical; 
    height: 150px; 
    margin-bottom: 20px;
}

form button { 
    width: 100%; 
    padding: 14px; 
    border: none; 
    border-radius: 50px; 
    background: #1db954; 
    color: #000; 
    font-weight: 700; 
    font-size: 18px;
    cursor: pointer; 
    transition: background-color 0.3s; 
}

form button:hover { 
    background: #1ed760; 
}

.success { 
    background: #1db954; 
    color: #000; 
    padding: 15px; 
    border-radius: 8px; 
    margin-bottom: 20px; 
    text-align: center;
    font-weight: 600;
}

.error { 
    background: #b33a3a; /* Darker Red */
    color: #fff; 
    padding: 15px; 
    border-radius: 8px; 
    margin-bottom: 20px; 
    text-align: center; 
}

/* 🔹 Footer */
footer {
    background-color: #000000;
    color: #b3b3b3;
    padding: 30px 32px 30px; 
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

</style>
</head>
<body>

<nav>
<h1>Sangeet</h1>
<ul>
<li><a href="index.php">Home</a></li>
<li><a href="trending.php">Trending</a></li>
<li><a href="playlists.php">Playlists</a></li>
<li class="active"><a href="feedback.php" style="color:#fff;">Feedback</a></li>
<?php if(isset($_SESSION['username'])): ?>
<li><a href="profile.php">Profile</a></li>
<li><a href="logout.php">Logout</a></li>
<?php else: ?>
<li><a href="login.php">Login</a></li>
<?php endif; ?>
</ul>
</nav>

<div class="container">
<h2>Submit Your Feedback ✍️</h2>

<?php if($success): ?>
<div class="success"><?= $success ?></div>
<?php endif; ?>
<?php if($error): ?>
<div class="error"><?= $error ?></div>
<?php endif; ?>

<form method="post" action="feedback.php">
<label for="name">Your Name:</label>
<input type="text" name="name" id="name" value="<?= htmlspecialchars($user_name ?? '') ?>" required>

<label for="email">Your Email:</label>
<input type="email" name="email" id="email" value="<?= htmlspecialchars($user_email ?? '') ?>" required>

<label for="message">Your Message or Suggestion:</label>
<textarea name="message" id="message" required><?= htmlspecialchars($message ?? '') ?></textarea>

<button type="submit">Submit Feedback</button>
</form>
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
</body>
</html>