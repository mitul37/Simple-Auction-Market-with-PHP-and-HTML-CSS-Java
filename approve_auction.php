<?php
session_start();
include 'config.php';

ini_set('display_errors', 1);
error_reporting(E_ALL);

// Check super admin session, if your system distinguishes super_admin
if (!isset($_SESSION['super_admin']) || $_SESSION['super_admin'] !== true) {
    header("Location: admin_login.php");
    exit();
}

$art_id = isset($_GET['id']) ? (int) $_GET['id'] : 0;
if ($art_id <= 0) {
    die("❌ Invalid Artwork ID.");
}

try {
    $pdo->beginTransaction();

    // Check if an active auction already exists for this artwork
    $checkAuction = $pdo->prepare("SELECT AuctionID FROM Auction WHERE ArtworkID = ? AND Status = 'Live'");
    $checkAuction->execute([$art_id]);
    if ($checkAuction->fetch()) {
        throw new Exception("An active auction already exists for this artwork.");
    }

    // Update Artwork to live auction
    $stmt = $pdo->prepare("UPDATE Artwork 
        SET AuctionStatus = 'Live', 
            ApprovedPrice = StartPrice, 
            AuctionStartTime = NOW(), 
            AuctionEndTime = DATE_ADD(NOW(), INTERVAL 24 HOUR)
        WHERE ArtworkID = ?");
    $stmt->execute([$art_id]);

    // Get approved price for auction insert
    $stmtFetch = $pdo->prepare("SELECT ApprovedPrice FROM Artwork WHERE ArtworkID = ?");
    $stmtFetch->execute([$art_id]);
    $art = $stmtFetch->fetch();
    $startPrice = $art['ApprovedPrice'] ?? 0;

    // Insert new auction
    $stmt2 = $pdo->prepare("INSERT INTO Auction (ArtworkID, StartPrice, CurrentHighestBid, Status) VALUES (?, ?, ?, 'Live')");
    $stmt2->execute([$art_id, $startPrice, 0]);

    $pdo->commit();

    header("Location: super_admin_dashboard.php?approved=1");
    exit();

} catch (Exception $e) {
    $pdo->rollBack();
    die("Error: " . $e->getMessage());
}
?>
