<?php
session_start();
include 'config.php';

if (!isset($_SESSION['admin'])) {
    header("Location: admin_login.php");
    exit();
}

$art_id = $_GET['id'] ?? 0;

// Reject auction
$stmt = $pdo->prepare("UPDATE Artwork 
    SET IsAuction = 0, 
        AuctionStatus = 'Rejected',
        StartPrice = NULL,
        ApprovedPrice = NULL,
        AuctionStartTime = NULL,
        AuctionEndTime = NULL
    WHERE ArtworkID = ?");
$stmt->execute([$art_id]);

header("Location: admin_dashboard.php");
exit();
