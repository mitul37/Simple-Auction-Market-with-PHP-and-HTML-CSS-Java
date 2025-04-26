<?php
include 'config.php';
session_start();
$errors = [];

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = trim($_POST['username']);
    $email    = trim($_POST['email']);
    $password = $_POST['password'];

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Invalid email format.";
    }

    if (strlen($password) < 6 || !preg_match('/[0-9]/', $password)) {
        $errors[] = "Password must be at least 6 characters and include a number.";
    }

    if (empty($errors)) {
        $stmt = $pdo->prepare("SELECT * FROM User WHERE Username = ?");
        $stmt->execute([$username]);

        if ($stmt->fetch()) {
            $errors[] = "Username already exists.";
        } else {
            $hash = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare("INSERT INTO User (Username, Email, Password) VALUES (?, ?, ?)");
            $stmt->execute([$username, $email, $hash]);

            $_SESSION['user'] = [
                'username' => $username,
                'id' => $pdo->lastInsertId()
            ];

            header("Location: index.php");
            exit();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Register - Art Auction</title>
<link rel="stylesheet" href="css/auth.css">
</head>
<body class="register-bg">

<div class="auth-card">
    <div class="icon-circle">
        <img src="uploads/register-icon.png" alt="Register" style="width:40px;">
    </div>
    <h2>Register</h2>

    <form method="POST" action="register_process.php">
        <input type="text" name="username" placeholder="Username" required><br>
        <input type="email" name="email" placeholder="Email" required><br>
        <input type="password" name="password" placeholder="Password" required><br>
        <input type="password" name="confirm_password" placeholder="Confirm Password" required><br>
        <button type="submit">REGISTER</button>
    </form>

    <a href="login.php" class="switch-link">Already a member? Login</a>
</div>

</body>
</html>
