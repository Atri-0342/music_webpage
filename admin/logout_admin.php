<?php
// Set a custom session name for admin
session_name("ADMIN_SESSION");
session_start();

// Clear only admin session
$_SESSION = [];
session_destroy();

// Delete cookie
setcookie("ADMIN_SESSION", '', time() - 3600, '/');

header("Location: admin_login.php");
exit;
?>
