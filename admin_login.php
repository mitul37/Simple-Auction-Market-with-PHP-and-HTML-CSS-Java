<?php
session_start();
include 'config.php';


if (isset($_SESSION['admin'])) {
    header("Location: admin_dashboard.php");
    exit();
}


$admin_username = 'admin';
$admin_password = 'password123'; 

$error = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = $_POST['username'];
    $password = $_POST['password'];

    if ($username === $admin_username && $password === $admin_password) {
        $_SESSION['admin'] = true;
        header("Location: admin_dashboard.php");
        exit();
    } else {
        $error = "Invalid Admin Credentials.";
    }
}
?>

<h2>🔒 Admin Login</h2>

<?php if ($error) echo "<p style='color:red;'>$error</p>"; ?>

<form method="POST">
    <input name="username" placeholder="Admin Username" required><br><br>
    <input name="password" type="password" placeholder="Admin Password" required><br><br>
    <button type="submit">Login</button>
</form>
