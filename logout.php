<?php
// Set a custom session name for user
session_name("USER_SESSION");
session_start();

// Clear only user session
$_SESSION = [];
session_destroy();

// Delete cookie
setcookie("USER_SESSION", '', time() - 3600, '/');

header("Location: login.php");
exit;
?>
