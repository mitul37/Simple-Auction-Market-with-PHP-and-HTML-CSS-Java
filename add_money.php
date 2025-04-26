<?php
session_start();
include 'config.php';

if (!isset($_SESSION['user'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user']['UserID'];

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $amount = (float)$_POST['amount'];

    if ($amount > 0) {
        $stmt = $pdo->prepare("UPDATE User SET WalletBalance = WalletBalance + ? WHERE UserID = ?");
        $stmt->execute([$amount, $user_id]);
        header("Location: dashboard.php?money_added=1");
        exit();
    } else {
        $error = "Invalid amount!";
    }
}
?>

<h2>➕ Add Money to Wallet</h2>

<?php if (!empty($error)) echo "<p style='color:red;'>$error</p>"; ?>

<form method="POST">
    <input type="number" name="amount" step="0.01" placeholder="Amount (৳)" required><br><br>
    <button type="submit">Add Money</button>
</form>
