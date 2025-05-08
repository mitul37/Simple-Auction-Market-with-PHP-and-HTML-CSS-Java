<?php
session_start();
include 'config.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = trim($_POST['username']);
    $email = trim($_POST['email']);
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);

    $stmt = $pdo->prepare("INSERT INTO User (Username, Email, Password, WalletBalance) VALUES (?, ?, ?, 5000)");
    $stmt->execute([$username, $email, $password]);

    $_SESSION['success'] = "Registration Successful! Please Login.";
    header("Location: login.php");
    exit();
} else {
    header("Location: register.php");
    exit();
}
?>
