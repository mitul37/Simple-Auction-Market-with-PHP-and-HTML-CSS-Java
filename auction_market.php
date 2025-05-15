<?php
session_start();
include 'config.php';

// Fetch live auctions that haven't ended yet
$stmt = $pdo->prepare("
    SELECT a.AuctionID, a.StartDateTime, a.EndDateTime, a.StartPrice, a.ReservePrice, a.CurrentHighestBid, a.Status, 
           art.Title, art.Description, art.ImageSourceURL, art.ApprovedPrice, art.UserID AS OwnerID
    FROM Auction a
    JOIN Artwork art ON a.ArtworkID = art.ArtworkID
    WHERE a.Status = 'Live' AND a.EndDateTime > NOW()
");
$stmt->execute();
$auctions = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <title>Auction Market</title>
    <style>
      /* Mansoory-style CSS for auction grid */

      body {
        font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        background-color: #f0f2f5;
        margin: 0;
        padding: 40px 40px;
      }

      h2 {
        text-align: center;
        font-weight: 700;
        font-size: 2.5rem;
        color: #222;
        margin-bottom: 40px;
      }

      .grid-container {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
        gap: 25px;
        max-width: 1200px;
        margin: 0 auto;
      }

      .grid-item {
        background: white;
        border-radius: 12px;
        box-shadow: 0 8px 20px rgba(0,0,0,0.1);
        overflow: hidden;
        display: flex;
        flex-direction: column;
        transition: transform 0.3s ease, box-shadow 0.3s ease;
      }

      .grid-item:hover {
        transform: translateY(-10px);
        box-shadow: 0 20px 40px rgba(0,0,0,0.15);
      }

      .grid-item img {
        width: 100%;
        height: 180px;
        object-fit: cover;
        border-bottom: 1px solid #eee;
        transition: transform 0.3s ease;
      }

      .grid-item:hover img {
        transform: scale(1.05);
      }

      .content {
        padding: 20px;
        flex-grow: 1;
        display: flex;
        flex-direction: column;
        justify-content: space-between;
      }

      .content h3 {
        font-weight: 700;
        margin: 0 0 12px 0;
        color: #1a1a1a;
        font-size: 1.3rem;
      }

      .content p {
        color: #555;
        font-size: 0.95rem;
        flex-grow: 1;
        margin-bottom: 15px;
        line-height: 1.4;
      }

      .bid-info {
        font-weight: 600;
        color: #222;
        font-size: 1rem;
        margin-bottom: 10px;
      }

      .timer {
        font-weight: 700;
        color: #e74c3c;
        font-size: 1rem;
        margin-bottom: 15px;
      }

      form {
        display: flex;
        gap: 8px;
        align-items: center;
      }

      input[type=number] {
        flex-grow: 1;
        padding: 8px 12px;
        font-size: 1rem;
        border: 2px solid #ddd;
        border-radius: 8px;
        outline: none;
        transition: border-color 0.3s ease;
      }

      input[type=number]:focus {
        border-color: #27ae60;
        box-shadow: 0 0 8px #27ae6077;
      }

      button {
        padding: 10px 16px;
        background-color: #27ae60;
        border: none;
        border-radius: 8px;
        color: white;
        font-weight: 700;
        font-size: 1rem;
        cursor: pointer;
        transition: background-color 0.3s ease;
      }

      button:hover {
        background-color: #2ecc71;
      }

      @media (max-width: 600px) {
        body {
          padding: 15px;
        }
        .grid-container {
          grid-template-columns: 1fr;
          gap: 20px;
        }
      }
    </style>
</head>
<body>

<h2>🎯 Live Auction Market</h2>

<div class="grid-container">
<?php if (count($auctions) === 0): ?>
    <p style="text-align:center; font-size:1.2rem; color:#777;">No live auctions currently.</p>
<?php else: ?>
    <?php foreach ($auctions as $auction): ?>
        <div class="grid-item">
            <img src="uploads/<?= htmlspecialchars($auction['ImageSourceURL']) ?>" alt="<?= htmlspecialchars($auction['Title']) ?>" />
            <div class="content">
                <h3><?= htmlspecialchars($auction['Title']) ?></h3>
                <p><?= nl2br(htmlspecialchars($auction['Description'])) ?></p>
                <div class="bid-info">Current Highest Bid: <?= number_format($auction['CurrentHighestBid'], 2) ?> ৳</div>
                <div class="timer" id="timer-<?= $auction['AuctionID'] ?>">Loading timer...</div>
                <form action="place_bid.php" method="POST" onsubmit="return validateBid(this, <?= $auction['CurrentHighestBid'] ?>);">
                    <input type="hidden" name="auction_id" value="<?= $auction['AuctionID'] ?>" />
                    <input type="number" name="bid_amount" step="0.01" min="<?= max($auction['CurrentHighestBid'] + 0.01, 0.01) ?>" placeholder="Your bid (min <?= number_format(max($auction['CurrentHighestBid'] + 0.01, 0.01), 2) ?>)" required />
                    <button type="submit">Place Bid</button>
                </form>
            </div>
        </div>
    <?php endforeach; ?>
<?php endif; ?>
</div>

<script>
    function validateBid(form, currentBid) {
        const bidInput = form.bid_amount;
        const bidValue = parseFloat(bidInput.value);
        if (isNaN(bidValue) || bidValue <= currentBid) {
            alert('Your bid must be higher than the current highest bid.');
            bidInput.focus();
            return false;
        }
        return true;
    }

    // Countdown timers for each auction
    <?php foreach ($auctions as $auction): 
        $endTimestamp = strtotime($auction['EndDateTime']) * 1000;
    ?>
    (function(){
        const timerId = 'timer-<?= $auction['AuctionID'] ?>';
        const countdown = setInterval(() => {
            const now = new Date().getTime();
            const distance = <?= $endTimestamp ?> - now;
            const timerElem = document.getElementById(timerId);
            if (!timerElem) return clearInterval(countdown);
            if (distance <= 0) {
                clearInterval(countdown);
                timerElem.innerText = "Auction Ended";
            } else {
                const hours = Math.floor((distance % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
                const minutes = Math.floor((distance % (1000 * 60 * 60)) / (1000 * 60));
                const seconds = Math.floor((distance % (1000 * 60)) / 1000);
                timerElem.innerText = `${hours}h ${minutes}m ${seconds}s`;
            }
        }, 1000);
    })();
    <?php endforeach; ?>
</script>

</body>
</html>
