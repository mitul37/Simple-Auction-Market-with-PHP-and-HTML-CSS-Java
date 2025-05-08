<?php
include 'config.php';

$stmt = $pdo->prepare("
    SELECT Auction.*, Artwork.Title, Artwork.UserID AS SellerID
    FROM Auction
    JOIN Artwork ON Auction.ArtworkID = Artwork.ArtworkID
    WHERE Auction.Status = 'Live' AND Artwork.AuctionEndTime < NOW()
");
$stmt->execute();
$expired_auctions = $stmt->fetchAll();

foreach ($expired_auctions as $auction) {
    $auction_id = $auction['AuctionID'];
    $buyer_id = $auction['BuyerID'];
    $seller_id = $auction['SellerID'];
    $artwork_id = $auction['ArtworkID'];
    $sold_price = $auction['CurrentHighestBid'];

    if ($buyer_id && $sold_price > 0) {

        $pdo->prepare("UPDATE Auction SET Status = 'Sold' WHERE AuctionID = ?")->execute([$auction_id]);
        $pdo->prepare("UPDATE Artwork SET AuctionStatus = 'Ended' WHERE ArtworkID = ?")->execute([$artwork_id]);


        $pdo->prepare("INSERT INTO SoldArtworks (ArtworkID, SellerID, BuyerID, SoldPrice) VALUES (?, ?, ?, ?)")->execute([$artwork_id, $seller_id, $buyer_id, $sold_price]);


        $pdo->prepare("UPDATE User SET WalletBalance = WalletBalance + ? WHERE UserID = ?")->execute([$sold_price, $seller_id]);
    } else {

        $pdo->prepare("UPDATE Auction SET Status = 'Expired' WHERE AuctionID = ?")->execute([$auction_id]);
        $pdo->prepare("UPDATE Artwork SET IsAuction = 0, AuctionStatus = 'Ended', StartPrice = NULL, ApprovedPrice = NULL, AuctionStartTime = NULL, AuctionEndTime = NULL WHERE ArtworkID = ?")->execute([$artwork_id]);
    }
}

echo "✅ Auction auto-closing done.";
?>
