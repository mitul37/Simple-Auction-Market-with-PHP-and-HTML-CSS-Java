<?php
session_start();
include 'config.php';

if (!isset($_SESSION['user'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user']['UserID'];

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $auction_id = (int)$_POST['auction_id'];
    $bid_amount = (float)$_POST['bid_amount'];


    $stmt = $pdo->prepare("
        SELECT Auction.*, Artwork.ApprovedPrice, Artwork.UserID AS OwnerID, Artwork.AuctionEndTime
        FROM Auction
        JOIN Artwork ON Auction.ArtworkID = Artwork.ArtworkID
        WHERE Auction.AuctionID = ?
    ");
    $stmt->execute([$auction_id]);
    $auction = $stmt->fetch();

    if (!$auction) {
        die("Auction not found.");
    }

    if ($auction['OwnerID'] == $user_id) {
        die("You cannot bid on your own artwork.");
    }


    $now = new DateTime();
    $auction_end = new DateTime($auction['AuctionEndTime']);
    if ($now > $auction_end) {
        die("Auction already ended.");
    }


    $required_bid = ($auction['CurrentHighestBid'] > 0)
        ? round($auction['CurrentHighestBid'] * 1.2, 2)
        : round($auction['ApprovedPrice'] * 1.2, 2);

    if ($bid_amount < $required_bid) {
        die("❌ Your bid must be at least {$required_bid}৳ or higher!");
    }


    $stmt = $pdo->prepare("SELECT WalletBalance FROM User WHERE UserID = ?");
    $stmt->execute([$user_id]);
    $user = $stmt->fetch();

    if ($user['WalletBalance'] < $bid_amount) {
        die("❌ Insufficient wallet balance. Please add more funds.");
    }


    $stmt = $pdo->prepare("INSERT INTO Bid (AuctionID, UserID, BidAmount) VALUES (?, ?, ?)");
    $stmt->execute([$auction_id, $user_id, $bid_amount]);


    $stmt = $pdo->prepare("UPDATE Auction SET CurrentHighestBid = ?, BuyerID = ? WHERE AuctionID = ?");
    $stmt->execute([$bid_amount, $user_id, $auction_id]);

    header("Location: auction_market.php?bid_success=1");
    exit();
}
?>
