<?php
session_name("USER_SESSION");
session_start();
require 'db.php';

// Ensure user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$userId = $_SESSION['user_id'];
$message = '';
$messageType = ''; // 'success' or 'error'

// Fetch user details
$stmt = $pdo->prepare("SELECT * FROM users WHERE id=?");
$stmt->execute([$userId]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Collect and sanitize input
    $name = trim($_POST['name']);
    $mail = trim($_POST['mail']);
    $dob = $_POST['dob'];
    $city = trim($_POST['city']);
    $phone = trim($_POST['phone']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];

    // 1. Validate email uniqueness if changed
    $stmt = $pdo->prepare("SELECT id FROM users WHERE mail=? AND id!=?");
    $stmt->execute([$mail, $userId]);
    if ($stmt->rowCount() > 0) {
        $message = "Email already in use!";
        $messageType = 'error';
    } else {
        $updateColumns = ['name', 'mail', 'dob', 'city', 'phone'];
        $updateValues = [$name, $mail, $dob, $city, $phone];
        $sqlSet = "name=?, mail=?, dob=?, city=?, phone=?";
        
        // 2. Handle password update
        if (!empty($password)) {
            if ($password !== $confirm_password) {
                $message = "Passwords do not match!";
                $messageType = 'error';
            } else {
                $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
                $sqlSet .= ", password=?";
                $updateValues[] = $hashedPassword;
            }
        }

        // Only proceed with database update if no password error occurred
        if ($messageType !== 'error') {
            $updateValues[] = $userId; // Add userId for WHERE clause

            $stmt = $pdo->prepare("UPDATE users SET $sqlSet WHERE id=?");
            if ($stmt->execute($updateValues)) {
                 // Update session username if the name was changed
                if (isset($_SESSION['username']) && $_SESSION['username'] !== $name) {
                    $_SESSION['username'] = $name;
                }
                $message = "Profile updated successfully! ✨";
                $messageType = 'success';
            } else {
                $message = "Database error. Could not update profile.";
                $messageType = 'error';
            }
        }

        // 3. Refresh user data for display (especially important if update failed but original data needs to be displayed)
        $stmt = $pdo->prepare("SELECT * FROM users WHERE id=?");
        $stmt->execute([$userId]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Sangeet - User Profile</title>
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
    background: #181818;
    padding: 30px;
    border-radius: 10px;
    box-shadow: 0 4px 12px rgba(0,0,0,0.5);
    flex-grow: 1; 
}

h2 {
    text-align: center;
    color: #1db954;
    margin-bottom: 30px;
    font-size: 32px;
    font-weight: 700;
}

form label {
    display: block;
    margin: 15px 0 5px;
    font-size: 14px;
    color: #b3b3b3;
    font-weight: 500;
}

form input {
    width: 100%;
    padding: 12px;
    border-radius: 4px;
    border: 1px solid #535353;
    background: #282828;
    color: #fff;
    outline: none;
    margin-bottom: 10px;
    box-sizing: border-box;
    transition: border-color 0.3s;
}

form input:focus {
    border-color: #1db954;
}

input[type="submit"] {
    margin-top: 30px;
    background: #1db954;
    color: #000;
    font-weight: 700;
    font-size: 18px;
    cursor: pointer;
    border: none;
    border-radius: 50px;
    transition: background-color 0.3s;
}

input[type="submit"]:hover {
    background: #1ed760;
}

/* 🔹 Messages */
.message {
    padding: 15px;
    margin-bottom: 20px;
    border-radius: 8px;
    text-align: center;
    font-weight: 600;
}
.message.success {
    background: #1db954; 
    color: #000;
}
.message.error {
    background: #b33a3a; 
    color: #fff;
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
<h1 style="margin-right: auto;">Sangeet</h1>
<ul>
<li><a href="index.php">Home</a></li>
<li><a href="trending.php">Trending</a></li>
<li><a href="playlists.php">Playlists</a></li>
<li><a href="feedback.php">Feedback</a></li>
<li><a href="profile.php" style="color:#fff;">Profile</a></li>
<li><a href="logout.php">Logout</a></li>
</ul>
</nav>

<div class="container">
<h2>My Profile Settings ⚙️</h2>

<?php if($message): ?>
<div class="message <?= htmlspecialchars($messageType) ?>"><?= htmlspecialchars($message) ?></div>
<?php endif; ?>

<form method="POST" action="">
    <label>Name</label>
    <input type="text" name="name" value="<?= htmlspecialchars($user['name']) ?>" required>

    <label>Email</label>
    <input type="email" name="mail" value="<?= htmlspecialchars($user['mail']) ?>" required>

    <label>Date of Birth</label>
    <input type="date" name="dob" value="<?= htmlspecialchars($user['dob']) ?>" required>

    <label>City</label>
    <input type="text" name="city" value="<?= htmlspecialchars($user['city']) ?>">

    <label>Phone</label>
    <input type="text" name="phone" value="<?= htmlspecialchars($user['phone']) ?>">

    <hr style="border-top: 1px solid #333; margin: 30px 0;">

    <label>New Password (leave blank to keep current)</label>
    <input type="password" name="password">

    <label>Confirm New Password</label>
    <input type="password" name="confirm_password">

    <input type="submit" value="Update Profile">
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