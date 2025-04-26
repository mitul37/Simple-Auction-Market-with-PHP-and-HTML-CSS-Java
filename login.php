<?php
include 'config.php';
session_start();
$error = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = $_POST['username'];
    $password = $_POST['password'];

    $stmt = $pdo->prepare("SELECT * FROM User WHERE Username = ?");
    $stmt->execute([$username]);
    $user = $stmt->fetch();

    if ($user && password_verify($password, $user['Password'])) {
        $_SESSION['user'] = [
            'Username' => $user['Username'],
            'UserID' => $user['UserID']
        ];
        header("Location: index.php");
        exit();
    } else {
        $error = "Invalid credentials.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Login - Art Auction</title>
<link rel="stylesheet" href="css/auth.css">
</head>
<body class="login-bg">

<div class="auth-card">
    <div class="icon-circle">
        <img src="uploads/user-icon.png" alt="User" style="width:40px;">
    </div>
    <h2>Member Login</h2>

    <form method="POST" action="login_process.php">
        <input type="text" name="username" placeholder="Username" required><br>
        <input type="password" name="password" placeholder="Password" required><br>
        <button type="submit">LOGIN</button>
    </form>

    <a href="register.php" class="switch-link">Don't have an account? Register</a>
</div>

</body>
</html>
