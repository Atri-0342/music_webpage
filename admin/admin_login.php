<?php
require 'db.php';

// Use a separate session for admin
session_name("ADMIN_SESSION");
session_start();

$error = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = $_POST['username'];
    $password = $_POST['password'];

    // Admin table
    $stmt = $pdo->prepare("SELECT * FROM admins WHERE username = ? LIMIT 1");
    $stmt->execute([$username]);
    $admin = $stmt->fetch();

    if ($admin && password_verify($password, $admin['password'])) {
        // Separate session variables for admin
        $_SESSION['admin_id'] = $admin['id'];
        $_SESSION['admin_username'] = $admin['username'];
        header("Location: index.php");
        exit;
    } else {
        $error = "❌ Invalid username or password!";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Admin Login</title>
  <style>
    body {
      margin: 0;
      padding: 0;
      font-family: "Segoe UI", sans-serif;
      background: linear-gradient(135deg, #141e30, #243b55);
      display: flex;
      justify-content: center;
      align-items: center;
      height: 100vh;
      color: #fff;
    }
    .login-container {
      background: rgba(0, 0, 0, 0.7);
      padding: 40px;
      border-radius: 15px;
      box-shadow: 0px 8px 15px rgba(0,0,0,0.5);
      width: 350px;
      text-align: center;
    }
    .login-container h2 { margin-bottom: 20px; color: #00ff99; }
    .input-group { margin: 15px 0; text-align: left; }
    .input-group label { font-size: 14px; color: #aaa; }
    .input-group input {
      width: 100%;
      padding: 10px;
      margin-top: 5px;
      border: none;
      border-radius: 8px;
      outline: none;
      font-size: 16px;
    }
    .btn {
      background: #00ff99;
      color: #000;
      border: none;
      padding: 12px;
      margin-top: 20px;
      width: 100%;
      font-size: 16px;
      border-radius: 8px;
      cursor: pointer;
      transition: 0.3s;
    }
    .btn:hover { background: #00cc7a; }
    .error { margin-top: 15px; color: red; font-size: 14px; }
  </style>
</head>
<body>
  <div class="login-container">
    <h2>🔑 Admin Login</h2>
    <form method="POST">
      <div class="input-group">
        <label for="username">Username</label>
        <input type="text" name="username" required>
      </div>
      <div class="input-group">
        <label for="password">Password</label>
        <input type="password" name="password" required>
      </div>
      <button type="submit" class="btn">Login</button>
    </form>

    <div style="margin-top: 20px; text-align: center;">
    <a href="register.php" style="color: white; text-decoration: none;">Register Here</a>
    </div>

    <?php if ($error): ?>
      <div class="error"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>
    
  </div>
</body>
</html>
