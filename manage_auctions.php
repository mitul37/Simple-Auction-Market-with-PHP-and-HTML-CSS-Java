<?php
session_start();
include 'config.php';

// Check if the user is logged in as Super Admin
if (!isset($_SESSION['super_admin']) || $_SESSION['super_admin'] !== true) {
    header("Location: admin_login.php");
    exit();
}

// Handle auction actions (start, end, reset)
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $action = $_POST['action']; 
    $auction_id = $_POST['auction_id'];

    if ($action == 'end') {
        // End auction manually
        $stmt = $pdo->prepare("UPDATE Auction SET Status = 'Ended' WHERE AuctionID = ?");
        $stmt->execute([$auction_id]);
        echo "Auction ended successfully!";
    } elseif ($action == 'reset') {
        // Reset auction to live and set CurrentHighestBid to 0
        $stmt = $pdo->prepare("UPDATE Auction SET Status = 'Live', CurrentHighestBid = 0 WHERE AuctionID = ?");
        $stmt->execute([$auction_id]);
        echo "Auction reset successfully!";
    } elseif ($action == 'start') {
        // Start a new auction (set status to Live)
        $stmt = $pdo->prepare("UPDATE Auction SET Status = 'Live' WHERE AuctionID = ?");
        $stmt->execute([$auction_id]);
        echo "Auction started successfully!";
    }
}

// Get all auctions
$stmt = $pdo->prepare("SELECT * FROM Auction");
$stmt->execute();
$auctions = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Manage Auctions</title>
</head>
<body>

<h2>Manage Auctions</h2>

<form method="POST">
    <label>Action:</label>
    <select name="action" required>
        <option value="start">Start Auction</option>
        <option value="end">End Auction</option>
        <option value="reset">Reset Auction</option>
    </select><br><br>
    
    <label>Auction ID:</label>
    <input type="number" name="auction_id" required><br><br>
    
    <button type="submit">Submit</button>
</form>

<h3>Active Auctions</h3>
<table>
    <tr>
        <th>AuctionID</th>
        <th>Status</th>
        <th>ArtworkID</th>
        <th>Start Price</th>
        <th>End Date</th>
    </tr>
    <?php foreach ($auctions as $auction): ?>
    <tr>
        <td><?php echo $auction['AuctionID']; ?></td>
        <td><?php echo $auction['Status']; ?></td>
        <td><?php echo $auction['ArtworkID']; ?></td>
        <td><?php echo $auction['StartPrice']; ?>৳</td>
        <td><?php echo $auction['EndDateTime']; ?></td>
    </tr>
    <?php endforeach; ?>
</table>

</body>
</html>
