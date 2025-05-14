<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
include 'config.php';

// Check super admin login
if (!isset($_SESSION['super_admin']) || $_SESSION['super_admin'] !== true) {
    header("Location: admin_login.php");
    exit();
}

try {
    $stmt = $pdo->prepare("SELECT t.TransactionID, t.UserID, t.AuctionID, t.Amount, t.Type, t.Status, t.Description, t.TransactionDate, u.Username
                           FROM Transaction t
                           LEFT JOIN User u ON t.UserID = u.UserID
                           ORDER BY t.TransactionDate DESC");
    $stmt->execute();
    $transactions = $stmt->fetchAll();
} catch (PDOException $e) {
    die("Database error: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <title>Manage Transactions</title>
</head>
<body>

<h2>Manage Transactions</h2>

<table border="1" cellpadding="5" cellspacing="0">
    <thead>
        <tr>
            <th>TransactionID</th>
            <th>UserID</th>
            <th>Username</th>
            <th>AuctionID</th>
            <th>Amount</th>
            <th>Type</th>
            <th>Status</th>
            <th>Description</th>
            <th>Date</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($transactions as $txn): ?>
        <tr>
            <td><?= htmlspecialchars($txn['TransactionID']) ?></td>
            <td><?= htmlspecialchars($txn['UserID']) ?></td>
            <td><?= htmlspecialchars($txn['Username'] ?? 'N/A') ?></td>
            <td><?= htmlspecialchars($txn['AuctionID'] ?? 'N/A') ?></td>
            <td><?= htmlspecialchars($txn['Amount']) ?> ৳</td>
            <td><?= htmlspecialchars($txn['Type']) ?></td>
            <td><?= htmlspecialchars($txn['Status']) ?></td>
            <td><?= htmlspecialchars($txn['Description'] ?? '') ?></td>
            <td><?= htmlspecialchars($txn['TransactionDate']) ?></td>
        </tr>
        <?php endforeach; ?>
    </tbody>
</table>

</body>
</html>
