<?php
include 'config.php';

// Last 5 bids
$stmt = $pdo->prepare("
    SELECT Bid.BidAmount AS amount, User.Username AS username, Artwork.Title AS artwork
    FROM Bid
    JOIN Auction ON Bid.AuctionID = Auction.AuctionID
    JOIN Artwork ON Auction.ArtworkID = Artwork.ArtworkID
    JOIN User ON Bid.UserID = User.UserID
    ORDER BY Bid.BidTime DESC
    LIMIT 5
");
$stmt->execute();
$bids = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo json_encode($bids);
?>
