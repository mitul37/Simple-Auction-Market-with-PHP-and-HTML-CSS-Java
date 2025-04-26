<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

include 'config.php';

try {
    if (!isset($_SESSION['user'])) {
        throw new Exception("User session not found. Please login.");
    }

    $user_id = $_SESSION['user']['UserID'];

    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        $auction_id = (int)$_POST['auction_id'];
        $buy_now_price = (float)$_POST['buy_now_price'];

        // Fetch Auction
        $stmt = $pdo->prepare("
            SELECT Auction.*, Artwork.UserID AS OwnerID, Artwork.ArtworkID
            FROM Auction
            JOIN Artwork ON Auction.ArtworkID = Artwork.ArtworkID
            WHERE Auction.AuctionID = ?
        ");
        $stmt->execute([$auction_id]);
        $auction = $stmt->fetch();

        if (!$auction) {
            throw new Exception("Auction not found.");
        }

        if ($auction['OwnerID'] == $user_id) {
            throw new Exception("You cannot buy your own artwork.");
        }

        $artwork_id = $auction['ArtworkID'];

        // Check Wallet
        $stmt = $pdo->prepare("SELECT WalletBalance FROM User WHERE UserID = ?");
        $stmt->execute([$user_id]);
        $user = $stmt->fetch();
        
        if (!$user) {
            throw new Exception("User not found.");
        }

        if ($user['WalletBalance'] < $buy_now_price) {
            throw new Exception("Not enough wallet balance.");
        }

        // Start Transaction
        $pdo->beginTransaction();

        // Deduct from Buyer
        $stmt = $pdo->prepare("UPDATE User SET WalletBalance = WalletBalance - ? WHERE UserID = ?");
        $stmt->execute([$buy_now_price, $user_id]);

        // Credit to Seller
        $stmt = $pdo->prepare("UPDATE User SET WalletBalance = WalletBalance + ? WHERE UserID = ?");
        $stmt->execute([$buy_now_price, $auction['OwnerID']]);

        // Mark Auction as Sold
        $stmt = $pdo->prepare("UPDATE Auction SET Status = 'Sold' WHERE AuctionID = ?");
        $stmt->execute([$auction_id]);

        // Mark Artwork as Ended
        $stmt = $pdo->prepare("UPDATE Artwork SET IsAuction = 0, AuctionStatus = 'Ended', StartPrice = NULL, ApprovedPrice = NULL, AuctionStartTime = NULL, AuctionEndTime = NULL WHERE ArtworkID = ?");
        $stmt->execute([$artwork_id]);

        // Insert into SoldArtworks
        $stmt = $pdo->prepare("INSERT INTO SoldArtworks (ArtworkID, SellerID, BuyerID, SoldPrice) VALUES (?, ?, ?, ?)");
        $stmt->execute([$artwork_id, $auction['OwnerID'], $user_id, $buy_now_price]);

        // Transfer ownership to Buyer
        $stmt = $pdo->prepare("UPDATE Artwork SET UserID = ? WHERE ArtworkID = ?");
        $stmt->execute([$user_id, $artwork_id]);

        // Transfer ownership to Buyer
        $stmt = $pdo->prepare("UPDATE Artwork 
        SET UserID = ?, 
            IsAuction = 0, 
            AuctionStatus = 'Not Listed', 
            StartPrice = NULL, 
            ApprovedPrice = NULL, 
            AuctionStartTime = NULL, 
            AuctionEndTime = NULL
        WHERE ArtworkID = ?");
        $stmt->execute([$user_id, $artwork_id]);
        // Commit Transaction
        $pdo->commit();
        // Redirect with success message
        echo "<script src='https://cdn.jsdelivr.net/npm/canvas-confetti@1.5.1/dist/confetti.min.js'></script>
        <script src='js/confetti.js'></script>
        <script>
        launchConfetti();
        setTimeout(function() {
            alert('🎉 Congratulations! You bought the artwork successfully!');
            window.location.href = 'dashboard.php?bought_success=1';
        }, 2000);
        </script>";
        exit();
    }
} catch (Exception $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    echo "<h1>Error:</h1><p>" . $e->getMessage() . "</p>";
}
?>
