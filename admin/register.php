<?php
require 'db.php';
session_start();

$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = htmlspecialchars(trim($_POST['username']),ENT_QUOTES,'UTF-8');
    $password = htmlspecialchars(trim($_POST['password']),ENT_QUOTES,'UTF-8');
    $confirm_password = htmlspecialchars(trim($_POST['confirm_password']),ENT_QUOTES,'UTF-8');

    if (empty($username) || empty($password) || empty($confirm_password)) {
        $message = "All fields are required!";
    } elseif ($password !== $confirm_password) {
        $message = "Passwords do not match!";
    } else {
        // Check if username already exists
        $stmt = $pdo->prepare("SELECT id FROM admins WHERE username = ?");
        $stmt->execute([$username]);
        if ($stmt->rowCount() > 0) {
            $message = "Username already exists!";
        } else {
            // Hash the password
            $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

            // Insert admin
            $stmt = $pdo->prepare("INSERT INTO admins (username, password) VALUES (?, ?)");
            if ($stmt->execute([$username, $hashedPassword])) {
                $message = "Admin registered successfully!";
            } else {
                $message = "Something went wrong. Please try again.";
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Admin Registration</title>
<style>
body { font-family: 'Poppins', sans-serif; background: linear-gradient(120deg, #141e30, #243b55); color: #fff; display: flex; justify-content: center; align-items: center; height: 100vh; }
form { background: rgba(0,0,0,0.7); padding: 30px; border-radius: 15px; display: flex; flex-direction: column; gap: 15px; width: 300px; }
input { padding: 10px; border-radius: 5px; border: none; outline: none; }
button { padding: 10px; border: none; border-radius: 5px; background: #00ffcc; color: #000; font-weight: 600; cursor: pointer; transition: 0.3s; }
button:hover { background: #00e6b8; }
.message { text-align: center; font-size: 14px; color: #ffd700; }
h2 { text-align: center; margin-bottom: 10px; }
</style>
</head>
<body>

<form method="POST" action="">
  <h2>Admin Registration</h2>
  <?php if($message) echo "<div class='message'>$message</div>"; ?>
  <input type="text" name="username" placeholder="Username" required>
  <input type="password" name="password" placeholder="Password" required>
  <input type="password" name="confirm_password" placeholder="Confirm Password" required>
  <button type="submit">Register</button>
  <div style="text-align:center; margin-top:10px; font-size:14px;">
    Already have an account? <a href="admin_login.php" style="color:#00ffcc; text-decoration:none;">Login here</a>
  </div>
</form>


</body>
</html>
