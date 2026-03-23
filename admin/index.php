<?php
session_name("ADMIN_SESSION");
session_start();

// Redirect if not logged in
if (!isset($_SESSION['admin_id'])) {
    header("Location: admin_login.php");
    exit;
}

$username = $_SESSION['admin_username'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Admin Dashboard</title>
<style>
/* Body & Header */
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

/* Container */
.container { display: flex; }



/* Main content */
main { flex: 1; padding: 30px; }
.card {
    background: #fff;
    padding: 20px;
    margin-bottom: 20px;
    border-radius: 10px;
    box-shadow: 0 2px 6px rgba(0,0,0,0.1);
}

/* Logout Button */
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
<h1>🎵 Admin Dashboard</h1>
<p>Welcome, <b><?= htmlspecialchars($username) ?></b></p>
<a href="logout_admin.php" class="logout-btn">Logout</a>
</header>

<div class="container">

<?php include "nav.php"?>

<main>
<div class="card">
<h2>Dashboard Overview</h2>
<p>Click "Manage" to expand options for Songs, Artists, Genres, Categories, and Users. Use the sidebar to navigate to Feedback, Profile, or Logout.</p>
</div>
</main>

</div>

<script>
// Toggle submenu visibility
function toggleMenu(id) {
    const menu = document.getElementById(id);
    menu.style.display = (menu.style.display === "block") ? "none" : "block";
}
</script>

</body>
</html>
