<?php
session_start();
include 'config.php';


ini_set('display_errors', 1);
error_reporting(E_ALL);

if (!isset($_SESSION['admin'])) {
    header("Location: admin_login.php");
    exit();
}

$art_id = $_GET['id'] ?? 0;

if (!$art_id) {
    die("❌ Invalid Artwork ID.");
}


$stmt = $pdo->prepare("UPDATE Artwork 
    SET AuctionStatus = 'Live', 
        ApprovedPrice = StartPrice, 
        AuctionStartTime = NOW(), 
        AuctionEndTime = DATE_ADD(NOW(), INTERVAL 24 HOUR)
    WHERE ArtworkID = ?");
$stmt->execute([$art_id]);



$stmtFetch = $pdo->prepare("SELECT ApprovedPrice FROM Artwork WHERE ArtworkID = ?");
$stmtFetch->execute([$art_id]);
$art = $stmtFetch->fetch();

$startPrice = $art['ApprovedPrice'] ?? 0;


$stmt2 = $pdo->prepare("INSERT INTO Auction (ArtworkID, StartPrice, CurrentHighestBid, Status) VALUES (?, ?, ?, 'Live')");
$stmt2->execute([$art_id, $startPrice, 0]);



header("Location: admin_dashboard.php?approved=1");
exit();
