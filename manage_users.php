<?php
session_start();
include 'config.php';

// Check if user is super admin
if (!isset($_SESSION['super_admin']) || $_SESSION['super_admin'] !== true) {
    header("Location: admin_login.php");
    exit();
}

// Handle adding/removing funds
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $user_id = $_POST['user_id'];
    $action = $_POST['action']; 
    $amount = $_POST['amount'];

    if ($action == 'add') {
        // Add funds
        $stmt = $pdo->prepare("UPDATE User SET Balance = Balance + ? WHERE UserID = ?");
        $stmt->execute([$amount, $user_id]);
        echo "Funds added successfully!";
    } elseif ($action == 'remove') {
        // Remove funds
        $stmt = $pdo->prepare("UPDATE User SET Balance = Balance - ? WHERE UserID = ?");
        $stmt->execute([$amount, $user_id]);
        echo "Funds removed successfully!";
    }
}

// Get users
$stmt = $pdo->prepare("SELECT * FROM User");
$stmt->execute();
$users = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Manage Users</title>
</head>
<body>

<h2>Manage Users</h2>

<form method="POST">
    <label>User ID:</label>
    <input type="number" name="user_id" required><br><br>
    
    <label>Action:</label>
    <select name="action" required>
        <option value="add">Add Funds</option>
        <option value="remove">Remove Funds</option>
    </select><br><br>
    
    <label>Amount:</label>
    <input type="number" name="amount" step="0.01" required><br><br>

    <button type="submit">Submit</button>
</form>

<h3>Users List</h3>
<table>
    <tr>
        <th>UserID</th>
        <th>Username</th>
        <th>Balance</th>
    </tr>
    <?php foreach ($users as $user): ?>
    <tr>
        <td><?php echo $user['UserID']; ?></td>
        <td><?php echo $user['Username']; ?></td>
        <td><?php echo $user['Balance']; ?>৳</td>
    </tr>
    <?php endforeach; ?>
</table>

</body>
</html>
