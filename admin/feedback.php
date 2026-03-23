<?php
session_name("ADMIN_SESSION");
session_start();

// Redirect if not logged in
if (!isset($_SESSION['admin_id'])) {
    header("Location: admin_login.php");
    exit;
}

require_once '../db.php'; // Include your database connection

$username = $_SESSION['admin_username'];

// Handle delete individual feedback
if (isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    $stmt = $pdo->prepare("DELETE FROM feedback WHERE id = ?");
    $stmt->execute([$id]);
    header("Location: feedback.php");
    exit;
}

// Handle delete all feedback
if (isset($_POST['delete_all'])) {
    $pdo->query("DELETE FROM feedback");
    header("Location: feedback.php");
    exit;
}

// Fetch all feedback
$stmt = $pdo->query("SELECT * FROM feedback ORDER BY created_at DESC");
$feedbacks = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>User Feedback - Admin</title>
<style>
body {
    font-family: "Segoe UI", sans-serif;
    margin: 0;
    background: #f4f6f9;
}
header {
    background: #243b55;
    color: #fff;
    padding: 20px;
    text-align: center;
}
header h1 { margin: 0; font-size: 24px; }
header p { margin-top: 5px; }
.container { display: flex; }
nav {
    width: 250px;
    background: #141e30;
    min-height: 100vh;
    padding-top: 20px;
    color: #fff;
    box-sizing: border-box;
}
nav a, nav button {
    display: block;
    width: 100%;
    padding: 12px 20px;
    color: #fff;
    text-align: left;
    text-decoration: none;
    font-size: 16px;
    border: none;
    background: none;
    cursor: pointer;
    transition: background 0.3s;
}
nav a:hover, nav button:hover { background: #00cc7a; }
nav button:focus { outline: none; }
button::after { content: " ▾"; float: right; }
.submenu { display: none; background: #1c1c1c; }
.submenu a {
    padding-left: 35px;
    font-size: 14px;
    color: #fff;
    text-decoration: none;
}
.submenu a:hover { background: #00cc7a; }
main { flex: 1; padding: 30px; }
.card {
    background: #fff;
    padding: 20px;
    margin-bottom: 20px;
    border-radius: 10px;
    box-shadow: 0 2px 6px rgba(0,0,0,0.1);
}
.feedback-table {
    width: 100%;
    border-collapse: collapse;
}
.feedback-table th, .feedback-table td {
    padding: 10px;
    border: 1px solid #ddd;
}
.feedback-table th { background: #243b55; color: #fff; }
.feedback-table td a {
    color: #ff4d4d;
    text-decoration: none;
}
.feedback-table td a:hover { text-decoration: underline; }
.delete-all-btn {
    padding: 10px 20px;
    background: #ff4d4d;
    color: #fff;
    border: none;
    border-radius: 6px;
    cursor: pointer;
    margin-bottom: 15px;
}
.delete-all-btn:hover { background: #e60000; }
.logout-btn {
    display: inline-block;
    margin-top: 10px;
    padding: 10px 20px;
    background: #ff4d4d;
    color: #fff;
    border: none;
    border-radius: 6px;
    cursor: pointer;
    text-decoration: none;
}
.logout-btn:hover { background: #e60000; }
</style>
</head>
<body>

<header>
<h1>💡 User Feedback</h1>
<p>Welcome, <b><?= htmlspecialchars($username) ?></b></p>
<a href="logout_admin.php" class="logout-btn">Logout</a>
</header>

<div class="container">
<nav>
    <a href="admin.php">🏠 Dashboard</a>
    <button onclick="toggleMenu('manageMenu')">🛠️ Manage</button>
    <div class="submenu" id="manageMenu">
        <a href="/pinaki/admin/manage_songs/songs.php">🎵 Songs</a>
        <a href="/pinaki/admin/manage_artists/artists.php">🎤 Artists</a>
        <a href="/pinaki/admin/manage_genres/genres.php">🎶 Genres</a>
        <a href="/pinaki/admin/manage_categories/categories.php">📁 Categories</a>
        <a href="/pinaki/admin/manage_users/users.php">👥 Users</a>
    </div>
    <a href="/pinaki/admin/feedback.php">💡 User Feedback</a>
    <a href="/pinaki/admin/profile.php">👤 Profile</a>
</nav>

<main>
<div class="card">
    <h2>All Feedbacks</h2>
    <?php if(count($feedbacks) > 0): ?>
        <form method="post" onsubmit="return confirm('Are you sure you want to delete all feedbacks?');">
            <button type="submit" name="delete_all" class="delete-all-btn">Delete All Feedbacks</button>
        </form>
        <table class="feedback-table">
            <tr>
                <th>ID</th>
                <th>Name</th>
                <th>Email</th>
                <th>Message</th>
                <th>Date</th>
                <th>Action</th>
            </tr>
            <?php foreach($feedbacks as $fb): ?>
            <tr>
                <td><?= $fb['id'] ?></td>
                <td><?= htmlspecialchars($fb['user_name']) ?></td>
                <td><?= htmlspecialchars($fb['user_email']) ?></td>
                <td><?= nl2br(htmlspecialchars($fb['message'])) ?></td>
                <td><?= $fb['created_at'] ?></td>
                <td><a href="?delete=<?= $fb['id'] ?>" onclick="return confirm('Delete this feedback?');">Delete</a></td>
            </tr>
            <?php endforeach; ?>
        </table>
    <?php else: ?>
        <p>No feedback submitted yet.</p>
    <?php endif; ?>
</div>
</main>
</div>

<script>
function toggleMenu(id) {
    const menu = document.getElementById(id);
    menu.style.display = (menu.style.display === "block") ? "none" : "block";
}
</script>

</body>
</html>
