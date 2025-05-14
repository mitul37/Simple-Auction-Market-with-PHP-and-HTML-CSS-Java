<?php
session_start();
include 'config.php';

// If already logged in as admin or super admin, redirect accordingly
if (isset($_SESSION['super_admin']) && $_SESSION['super_admin'] === true) {
    header("Location: super_admin_dashboard.php");
    exit();
} elseif (isset($_SESSION['admin']) && $_SESSION['admin'] === true) {
    header("Location: super_admin_dashboard.php");
    exit();
}

// Hardcoded super admin credentials (consider hashing or DB lookup for production)
$super_admin_username = 'admin';
$super_admin_password = 'password123';  // You can hash this in future

$error = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = trim($_POST['username']);
    $password = trim($_POST['password']);

    if ($username === $super_admin_username && $password === $super_admin_password) {
        // Set both admin and super_admin flags
        $_SESSION['admin'] = true;
        $_SESSION['super_admin'] = true;
        header("Location: super_admin_dashboard.php");
        exit();
    } else {
        $error = "Invalid Admin Credentials.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Super Admin Login</title>
    <style>
        /* Basic styling */
        body { font-family: Arial, sans-serif; max-width: 400px; margin: 50px auto; }
        input { width: 100%; padding: 10px; margin: 10px 0; }
        button { padding: 10px 15px; width: 100%; background: #007BFF; color: white; border: none; cursor: pointer; }
        button:hover { background: #0056b3; }
        .error { color: red; }
    </style>
</head>
<body>

<h2>🔒 Super Admin Login</h2>

<?php if ($error): ?>
    <p class="error"><?= htmlspecialchars($error) ?></p>
<?php endif; ?>

<form method="POST" autocomplete="off">
    <input name="username" placeholder="Admin Username" required autofocus>
    <input name="password" type="password" placeholder="Admin Password" required>
    <button type="submit">Login</button>
</form>

</body>
</html>
