<?php
require 'db.php';
session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $name = filter_input(INPUT_POST, 'name', FILTER_SANITIZE_STRING);
        $email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);
        $password_raw = $_POST['password'];
        $dob = $_POST['dob'];
        $city = filter_input(INPUT_POST, 'city', FILTER_SANITIZE_STRING);
        $phone = filter_input(INPUT_POST, 'phone', FILTER_SANITIZE_STRING);

        if (!$name || !$email || !$password_raw || !$dob || !$city || !$phone) {
            throw new Exception("Please fill in all required fields.");
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw new Exception("Invalid email format.");
        }

        $password = password_hash($password_raw, PASSWORD_DEFAULT);

        $stmt = $pdo->prepare("SELECT id FROM users WHERE mail = :email");
        $stmt->bindParam(':email', $email);
        $stmt->execute();

        if ($stmt->fetch()) {
            throw new Exception("Email already registered. Please log in.");
        }

        $stmt = $pdo->prepare("INSERT INTO users (name, mail, password, dob, city, phone) 
                               VALUES (:name, :mail, :password, :dob, :city, :phone)");
        $stmt->bindParam(':name', $name);
        $stmt->bindParam(':mail', $email);
        $stmt->bindParam(':password', $password);
        $stmt->bindParam(':dob', $dob);
        $stmt->bindParam(':city', $city);
        $stmt->bindParam(':phone', $phone);
        $stmt->execute();

        $_SESSION['success_message'] = "Registration successful! Please log in.";
        header("Location: login.php");
        exit;

    } catch (Exception $e) {
        $error_message = $e->getMessage();
    } catch (PDOException $e) {
        $error_message = "Database error: " . $e->getMessage();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Registration Form</title>
<style>
body {
    font-family: Arial, sans-serif;
    line-height: 1.6;
    margin: 0;
    padding: 20px;
    background-color: #f4f4f4;
}
.container {
    max-width: 500px;
    margin: 40px auto;
    background: #fff;
    padding: 20px;
    border-radius: 8px;
    box-shadow: 0 0 10px rgba(0,0,0,0.1);
}
h2 {
    text-align: center;
    margin-bottom: 20px;
    color: #333;
}
.form-group {
    margin-bottom: 15px;
}
label {
    display: block;
    margin-bottom: 5px;
    font-weight: bold;
}
input[type="text"],
input[type="email"],
input[type="date"],
input[type="tel"],
input[type="password"] {
    width: 100%;
    padding: 10px;
    border: 1px solid #ddd;
    border-radius: 4px;
    box-sizing: border-box;
}
.counter {
    font-size: 12px;
    color: #555;
    margin-top: 3px;
}
button {
    background-color: #4CAF50;
    color: white;
    padding: 12px 20px;
    border: none;
    border-radius: 4px;
    cursor: pointer;
    width: 100%;
    font-size: 16px;
}
button:hover {
    background-color: #45a049;
}
.error {
    color: red;
    text-align: center;
    margin-bottom: 15px;
}
</style>
</head>
<body>
<div class="container">
    <h2>Registration Form</h2>

    <?php if (isset($error_message)) : ?>
        <div class="error"><?= htmlspecialchars($error_message) ?></div>
    <?php endif; ?>

    <form id="registrationForm" action="registration.php" method="post">
        <div class="form-group">
            <label for="name">Full Name:</label>
            <input type="text" id="name" name="name" required placeholder="Enter your full name">
        </div>

        <div class="form-group">
            <label for="email">Email:</label>
            <input type="email" id="email" name="email" required placeholder="Enter your email">
        </div>

        <div class="form-group">
            <label for="password">Password:</label>
            <input type="password" id="password" name="password" required placeholder="Create a password">
            <div class="counter">Length: <span id="passwordCount">0</span> characters</div>
        </div>

        <div class="form-group">
            <label for="dob">Date of Birth:</label>
            <input type="date" id="dob" name="dob" required>
        </div>

        <div class="form-group">
            <label for="city">City:</label>
            <input type="text" id="city" name="city" required placeholder="Enter your city">
        </div>

        <div class="form-group">
            <label for="phone">Phone Number:</label>
            <input type="tel" id="phone" name="phone" required placeholder="Enter your phone number" maxlength="15">
            <div class="counter">Remaining digits: <span id="phoneCount">15</span></div>
        </div>

        <div class="form-group">
            <button type="submit">Register</button>
        </div>
    </form>
</div>

<script src="js/validation.js"></script>
</body>
</html>
