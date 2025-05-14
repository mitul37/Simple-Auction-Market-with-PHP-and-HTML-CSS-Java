<?php
session_start();
include 'config.php';

// Check super admin session (update if your system distinguishes super_admin)
if (!isset($_SESSION['super_admin']) || $_SESSION['super_admin'] !== true) {
    header("Location: admin_login.php");
    exit();
}

$art_id = isset($_GET['id']) ? (int) $_GET['id'] : 0;

if ($art_id <= 0) {
    die("❌ Invalid Artwork ID.");
}

try {
    // Begin transaction for safety
    $pdo->beginTransaction();

    // Update artwork to reject auction
    $stmt = $pdo->prepare("UPDATE Artwork 
        SET IsAuction = 0, 
            AuctionStatus = 'Rejected',
            StartPrice = NULL,
            ApprovedPrice = NULL,
            AuctionStartTime = NULL,
            AuctionEndTime = NULL
        WHERE ArtworkID = ?");
    $stmt->execute([$art_id]);

    // Optionally, you might want to delete related auction entries if any
    $stmt2 = $pdo->prepare("DELETE FROM Auction WHERE ArtworkID = ?");
    $stmt2->execute([$art_id]);

    $pdo->commit();

    // Redirect with success flag (optional)
    header("Location: super_admin_dashboard.php?rejected=1");
    exit();

} catch (Exception $e) {
    $pdo->rollBack();
    die("Error: " . $e->getMessage());
}
