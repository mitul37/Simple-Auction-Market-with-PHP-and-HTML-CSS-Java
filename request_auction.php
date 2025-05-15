<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);
session_start();
include 'config.php';

if (!isset($_SESSION['user'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user']['UserID'];
$art_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($art_id <= 0) {
    die("❌ Invalid Artwork ID.");
}

$stmt = $pdo->prepare("SELECT * FROM Artwork WHERE ArtworkID = ? AND UserID = ?");
$stmt->execute([$art_id, $user_id]);
$artwork = $stmt->fetch();

if (!$artwork) {
    die("❌ Access Denied. You don't own this artwork.");
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $start_price = isset($_POST['start_price']) ? (float)$_POST['start_price'] : 0;

    if ($start_price > 0) {
        try {
            $pdo->beginTransaction();

            // Step 1: Update Artwork status to Pending and mark IsAuction=1
            $stmt1 = $pdo->prepare("UPDATE Artwork SET AuctionStatus = 'Pending', IsAuction = 1 WHERE ArtworkID = ?");
            $stmt1->execute([$art_id]);

            // Step 2: Insert a new auction with 'Pending' status
            $stmt2 = $pdo->prepare("INSERT INTO Auction (ArtworkID, StartDateTime, EndDateTime, StartPrice, CurrentHighestBid, Status) VALUES (?, NOW(), DATE_ADD(NOW(), INTERVAL 7 DAY), ?, 0, 'Pending')");
            $stmt2->execute([$art_id, $start_price]);

            $pdo->commit();

            header("Location: dashboard.php?auction_requested=1");
            exit();
        } catch (PDOException $e) {
            $pdo->rollBack();
            $error = "Database error: " . $e->getMessage();
        }
    } else {
        $error = "Invalid Starting Price.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8" />
<meta name="viewport" content="width=device-width, initial-scale=1" />
<title>Request Auction - <?= htmlspecialchars($artwork['Title']) ?></title>
<style>
  /* Basic styles as before */
</style>
</head>
<body>

<div class="container">
  <h2>🎯 Request Auction for<br><em><?= htmlspecialchars($artwork['Title']) ?></em></h2>

  <?php if ($error): ?>
    <p style="color: red; font-weight: bold;"><?= htmlspecialchars($error) ?></p>
  <?php endif; ?>

  <form method="POST" novalidate>
    <label for="start_price">Set Your Starting Price (Minimum Bid Price)</label>
    <input type="number" name="start_price" id="start_price" step="0.01" min="0.01" required placeholder="Enter starting price in ৳" />
    <br><br>
    <button type="submit">Submit Auction Request</button>
  </form>
</div>

</body>
</html>
