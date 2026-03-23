<?php
require 'db.php';

// Use a custom session name for users
session_name("USER_SESSION");
session_start();

$error = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = trim($_POST['email']);
    $password = trim($_POST['password']);

    $stmt = $pdo->prepare("SELECT * FROM users WHERE mail = :email LIMIT 1");
    $stmt->execute(['email' => $email]);
    $user = $stmt->fetch();

    if ($user && password_verify($password, $user['password'])) {
        // Store user-specific session variables
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['username'] = $user['name']; 
        header("Location: index.php");
        exit;
    } else {
        $error = "❌ Invalid email or password!";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>User Login</title>
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
.login-container h2 { margin-bottom: 20px; color: #00ffcc; }
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
    background: #00ffcc;
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
.btn:hover { background: #00c2a3; }
.error { margin-top: 15px; color: #ff4c4c; font-size: 14px; }
.register-link { margin-top: 20px; color: #aaa; font-size: 14px; }
.register-link a { color: #00ffcc; text-decoration: none; }
.register-link a:hover { text-decoration: underline; }
</style>
</head>
<body>
<div class="login-container">
    <h2>🎧 User Login</h2>
    <form method="POST" action="">
        <div class="input-group">
            <label for="email">Email</label>
            <input type="email" name="email" required>
        </div>
        <div class="input-group">
            <label for="password">Password</label>
            <input type="password" name="password" required>
        </div>
        <button type="submit" class="btn">Login</button>
    </form>

    <?php if ($error): ?>
        <div class="error"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <div class="register-link">
        Don’t have an account? <a href="registration.php">Register here</a>
    </div>
</div>
</body>
</html>
