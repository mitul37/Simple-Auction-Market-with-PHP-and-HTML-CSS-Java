<?php
session_start();
include 'config.php';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Auction Market</title>
    <link rel="stylesheet" href="css/style.css">
    <script src="popup.js"></script> <!-- Real-time Popup Script -->
</head>
<body>

<?php include 'includes/header.php'; ?>

<?php
if (isset($_GET['bid_success'])) echo "<p style='color:green;'>✅ Bid placed successfully!</p>";
if (isset($_GET['buy_success'])) echo "<p style='color:green;'>✅ Artwork bought successfully!</p>";
?>

<h2>🎯 Live Auction Market</h2>

<div class="grid-container">
<?php
$stmt = $pdo->prepare("
    SELECT Auction.*, Artwork.Title, Artwork.Description, Artwork.ImageSourceURL, Artwork.ApprovedPrice, Artwork.UserID AS OwnerID, Artwork.AuctionEndTime
    FROM Auction
    JOIN Artwork ON Auction.ArtworkID = Artwork.ArtworkID
    WHERE Auction.Status = 'Live' AND Artwork.AuctionEndTime > NOW()
");
$stmt->execute();
$auctions = $stmt->fetchAll();

if (count($auctions) === 0) {
    echo "<p>No live auctions currently.</p>";
} else {
    foreach ($auctions as $auction) {
        $buyNowPrice = round($auction['ApprovedPrice'] * 1.15, 2);
        $minimumBid = ($auction['CurrentHighestBid'] == 0) 
            ? round($auction['ApprovedPrice'] * 0.2, 2)
            : round($auction['CurrentHighestBid'] * 1.2, 2);

        $endTimestamp = strtotime($auction['AuctionEndTime']) * 1000;
        echo "<div class='grid-item'>
                <img src='uploads/{$auction['ImageSourceURL']}' alt='{$auction['Title']}'>
                <h3>{$auction['Title']}</h3>
                <p>{$auction['Description']}</p>
                <p><strong>Asking Price:</strong> {$auction['ApprovedPrice']}৳</p>
                <p><strong>Current Highest Bid:</strong> {$auction['CurrentHighestBid']}৳</p>
                <p><strong>Minimum Next Bid:</strong> {$minimumBid}৳</p>
                <p><strong>Time Left:</strong> <span id='timer-{$auction['AuctionID']}'></span></p>";

        if (isset($_SESSION['user']) && $_SESSION['user']['UserID'] !== $auction['OwnerID']) {
            echo "<form action='place_bid.php' method='POST'>
                    <input type='hidden' name='auction_id' value='{$auction['AuctionID']}'>
                    <input type='number' name='bid_amount' step='0.01' placeholder='Min {$minimumBid}৳' required><br><br>
                    <button type='submit'>Place Bid</button>
                  </form><br>
                  <form action='buy_now.php' method='POST'>
                    <input type='hidden' name='auction_id' value='{$auction['AuctionID']}'>
                    <input type='hidden' name='buy_now_price' value='{$buyNowPrice}'>
                    <button type='submit'>Buy Now for {$buyNowPrice}৳</button>
                  </form>";
        } else if (!isset($_SESSION['user'])) {
            echo "<p><a href='login.php'>Login</a> to bid!</p>";
        } else {
            echo "<p><em>You cannot bid on your own artwork.</em></p>";
        }

        echo "</div>";

        echo "
        <script>
        const countdown{$auction['AuctionID']} = setInterval(function() {
            const now = new Date().getTime();
            const distance = {$endTimestamp} - now;
            if (distance <= 0) {
                clearInterval(countdown{$auction['AuctionID']});
                document.getElementById('timer-{$auction['AuctionID']}').innerHTML = 'Ended';
            } else {
                const hours = Math.floor((distance % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
                const minutes = Math.floor((distance % (1000 * 60 * 60)) / (1000 * 60));
                const seconds = Math.floor((distance % (1000 * 60)) / 1000);
                document.getElementById('timer-{$auction['AuctionID']}').innerHTML = hours + 'h ' + minutes + 'm ' + seconds + 's';
            }
        }, 1000);
        </script>
        ";
    }
}
?>
</div>

</body>
</html>
