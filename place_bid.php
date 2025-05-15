<?php
// Only start session if not started yet
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

ini_set('display_errors', 1);
error_reporting(E_ALL);

include 'config.php';

if (!isset($_SESSION['user'])) {
    header("Location: login.php");
    exit();
}

$userId = $_SESSION['user']['UserID'] ?? null;
$auctionId = $_POST['auction_id'] ?? null;
$bidAmount = $_POST['bid_amount'] ?? null;

if (!$auctionId || !$bidAmount) {
    die("Invalid bid submission.");
}

$bidAmount = (float)$bidAmount;
if ($bidAmount <= 0) {
    die("Bid amount must be greater than zero.");
}

try {
    $pdo->beginTransaction();

    // Fetch auction details
    $stmt = $pdo->prepare("SELECT CurrentHighestBid, Status, EndDateTime FROM Auction WHERE AuctionID = ?");
    $stmt->execute([$auctionId]);
    $auction = $stmt->fetch();

    if (!$auction) {
        throw new Exception("Auction not found.");
    }

    if ($auction['Status'] !== 'Live') {
        throw new Exception("Auction is not live.");
    }

    if (strtotime($auction['EndDateTime']) < time()) {
        throw new Exception("Auction has ended.");
    }

    if ($bidAmount <= $auction['CurrentHighestBid']) {
        throw new Exception("Bid must be higher than the current highest bid.");
    }

    // Insert new bid with explicit BidTime = NOW()
    $stmt = $pdo->prepare("INSERT INTO Bid (AuctionID, UserID, BidAmount, BidTime) VALUES (?, ?, ?, NOW())");
    $stmt->execute([$auctionId, $userId, $bidAmount]);

    // Update current highest bid in Auction
    $stmt = $pdo->prepare("UPDATE Auction SET CurrentHighestBid = ? WHERE AuctionID = ?");
    $stmt->execute([$bidAmount, $auctionId]);

    $pdo->commit();

    header("Location: auction_market.php?bid_success=1");
    exit();

} catch (Exception $e) {
    $pdo->rollBack();
    die("Error placing bid: " . $e->getMessage());
}
