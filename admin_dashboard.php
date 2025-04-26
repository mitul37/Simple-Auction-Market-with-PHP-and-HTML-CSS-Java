<?php
session_start();
include 'config.php';


if (!isset($_SESSION['admin'])) {
    header("Location: admin_login.php");
    exit();
}


$stmt = $pdo->query("SELECT * FROM Artwork WHERE IsAuction = 1 AND AuctionStatus = 'Pending'");
$pending_artworks = $stmt->fetchAll();
?>

<h2>📋 Admin Dashboard - Approve Auction Requests</h2>

<a href="admin_logout.php">Logout</a><br><br>

<?php
if (count($pending_artworks) === 0) {
    echo "<p>No pending auctions.</p>";
} else {
    foreach ($pending_artworks as $art) {
        echo "<div style='border:1px solid #ccc; padding:10px; margin:10px;'>
            <img src='uploads/{$art['ImageSourceURL']}' alt='{$art['Title']}' width='150'><br>
            <strong>Title:</strong> {$art['Title']}<br>
            <strong>Description:</strong> {$art['Description']}<br>
            <strong>Requested Start Price:</strong> {$art['StartPrice']}<br><br>
            <a href='approve_auction.php?id={$art['ArtworkID']}'>✅ Approve</a> |
            <a href='reject_auction.php?id={$art['ArtworkID']}'>❌ Reject</a>
        </div>";
    }
}
?>
<?php if (isset($_GET['approved'])): ?>
    <p style="color:green;">✅ Auction approved successfully!</p>
<?php endif; ?>
