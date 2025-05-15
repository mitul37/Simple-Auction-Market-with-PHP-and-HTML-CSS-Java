<?php
session_start();
include 'config.php';

ini_set('display_errors', 1);
error_reporting(E_ALL);

// Check super admin session
if (!isset($_SESSION['super_admin']) || $_SESSION['super_admin'] !== true) {
    header("Location: admin_login.php");
    exit();
}

$art_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($art_id <= 0) {
    die("❌ Invalid Artwork ID.");
}

try {
    $pdo->beginTransaction();

    // Check if an auction already exists for this artwork
    $stmtCheck = $pdo->prepare("SELECT AuctionID, Status FROM Auction WHERE ArtworkID = ?");
    $stmtCheck->execute([$art_id]);
    $auction = $stmtCheck->fetch();

    // If auction exists and is not live, update it to live
    if ($auction) {
        if ($auction['Status'] !== 'Live') {
            $stmtUpdateAuction = $pdo->prepare("UPDATE Auction SET Status = 'Live', CurrentHighestBid = 0, StartDateTime = NOW(), EndDateTime = DATE_ADD(NOW(), INTERVAL 24 HOUR) WHERE AuctionID = ?");
            $stmtUpdateAuction->execute([$auction['AuctionID']]);
        }
    } else {
        // Insert new auction if it doesn't exist
        $stmtPrice = $pdo->prepare("SELECT ApprovedPrice FROM Artwork WHERE ArtworkID = ?");
        $stmtPrice->execute([$art_id]);
        $price = $stmtPrice->fetchColumn();

        $stmtInsertAuction = $pdo->prepare("INSERT INTO Auction (ArtworkID, StartDateTime, EndDateTime, StartPrice, CurrentHighestBid, Status) VALUES (?, NOW(), DATE_ADD(NOW(), INTERVAL 24 HOUR), ?, 0, 'Live')");
        $stmtInsertAuction->execute([$art_id, $price]);
    }

    // Update Artwork status to 'Live'
    $stmtUpdateArtwork = $pdo->prepare("UPDATE Artwork SET AuctionStatus = 'Live' WHERE ArtworkID = ?");
    $stmtUpdateArtwork->execute([$art_id]);

    $pdo->commit();

    header("Location: super_admin_dashboard.php?approved=1");
    exit();

} catch (Exception $e) {
    $pdo->rollBack();
    die("Error: " . $e->getMessage());
}
