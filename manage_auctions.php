<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

ini_set('display_errors', 1);
error_reporting(E_ALL);

include 'config.php';

// Super admin session check
if (!isset($_SESSION['super_admin']) || $_SESSION['super_admin'] !== true) {
    header("Location: admin_login.php");
    exit();
}

$message = '';
$error = '';

// Handle auction ending request via POST
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['end_auction_id'])) {
    $auctionId = (int)$_POST['end_auction_id'];

    if ($auctionId > 0) {
        try {
            $pdo->beginTransaction();

            // Fetch auction info
            $stmt = $pdo->prepare("SELECT AuctionID, ArtworkID, Status FROM Auction WHERE AuctionID = ?");
            $stmt->execute([$auctionId]);
            $auction = $stmt->fetch();

            if (!$auction) {
                throw new Exception("Auction not found.");
            }
            if ($auction['Status'] !== 'Live') {
                throw new Exception("Auction is not live and cannot be ended.");
            }

            // Fetch highest bid
            $stmt = $pdo->prepare("SELECT UserID, BidAmount FROM Bid WHERE AuctionID = ? ORDER BY BidAmount DESC LIMIT 1");
            $stmt->execute([$auctionId]);
            $highestBid = $stmt->fetch();

            if ($highestBid) {
                $buyerId = $highestBid['UserID'];
                $bidAmount = $highestBid['BidAmount'];

                // Fetch seller ID
                $stmt = $pdo->prepare("SELECT UserID FROM Artwork WHERE ArtworkID = ?");
                $stmt->execute([$auction['ArtworkID']]);
                $sellerId = $stmt->fetchColumn();

                if (!$sellerId) {
                    throw new Exception("Seller not found.");
                }

                // Check buyer WalletBalance
                $stmt = $pdo->prepare("SELECT WalletBalance FROM User WHERE UserID = ?");
                $stmt->execute([$buyerId]);
                $balance = $stmt->fetchColumn();

                if ($balance === false) {
                    throw new Exception("Buyer not found.");
                }

                if (bccomp($balance, $bidAmount, 2) < 0) {
                    throw new Exception("Buyer does not have sufficient wallet balance. Balance: $balance, Bid: $bidAmount");
                }

                // Deduct buyer WalletBalance
                $stmt = $pdo->prepare("UPDATE User SET WalletBalance = WalletBalance - ? WHERE UserID = ? AND WalletBalance >= ?");
                $stmt->execute([$bidAmount, $buyerId, $bidAmount]);

                if ($stmt->rowCount() === 0) {
                    throw new Exception("Failed to deduct wallet balance, buyer might not have sufficient funds.");
                }

                // Credit seller WalletBalance
                $stmt = $pdo->prepare("UPDATE User SET WalletBalance = WalletBalance + ? WHERE UserID = ?");
                $stmt->execute([$bidAmount, $sellerId]);

                // Transfer artwork ownership to buyer
                $stmt = $pdo->prepare("UPDATE Artwork SET UserID = ?, AuctionStatus = 'Sold', IsAuction = 0 WHERE ArtworkID = ?");
                $stmt->execute([$buyerId, $auction['ArtworkID']]);

                // Mark auction as sold
                $stmt = $pdo->prepare("UPDATE Auction SET Status = 'Sold', EndDateTime = NOW() WHERE AuctionID = ?");
                $stmt->execute([$auctionId]);

                // Log buyer transaction
                $stmt = $pdo->prepare("INSERT INTO Transaction (UserID, AuctionID, Amount, Type, Status, Description, TransactionDate) VALUES (?, ?, ?, 'Purchase', 'Completed', 'Auction purchase', NOW())");
                $stmt->execute([$buyerId, $auctionId, $bidAmount]);

                // Log seller transaction
                $stmt = $pdo->prepare("INSERT INTO Transaction (UserID, AuctionID, Amount, Type, Status, Description, TransactionDate) VALUES (?, ?, ?, 'Sale', 'Completed', 'Auction sale', NOW())");
                $stmt->execute([$sellerId, $auctionId, $bidAmount]);

                $message = "Auction #$auctionId ended successfully. Sold to User #$buyerId for ৳$bidAmount.";
            } else {
                // No bids: expire auction and reset artwork
                $stmt = $pdo->prepare("UPDATE Auction SET Status = 'Expired', EndDateTime = NOW() WHERE AuctionID = ?");
                $stmt->execute([$auctionId]);

                $stmt = $pdo->prepare("UPDATE Artwork SET AuctionStatus = 'Expired', IsAuction = 0 WHERE ArtworkID = ?");
                $stmt->execute([$auction['ArtworkID']]);

                $message = "Auction #$auctionId ended with no bids. Artwork is available again.";
            }

            $pdo->commit();
        } catch (Exception $e) {
            $pdo->rollBack();
            $error = "Error ending auction: " . htmlspecialchars($e->getMessage());
        }
    } else {
        $error = "Invalid auction ID.";
    }
}

// Fetch all auctions for display
$stmt = $pdo->prepare("
    SELECT a.AuctionID, a.StartDateTime, a.EndDateTime, a.StartPrice, a.CurrentHighestBid, a.Status,
           art.Title AS ArtworkTitle, art.UserID AS OwnerID
    FROM Auction a
    JOIN Artwork art ON a.ArtworkID = art.ArtworkID
    ORDER BY a.StartDateTime DESC
");
$stmt->execute();
$auctions = $stmt->fetchAll();

?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8" />
<meta name="viewport" content="width=device-width, initial-scale=1" />
<title>Manage Auctions - Super Admin</title>
<style>
  body {
    font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    background: #fafafa;
    margin: 0; padding: 30px 20px;
    color: #333;
  }
  h1 {
    text-align: center;
    margin-bottom: 30px;
    font-weight: 700;
    color: #222;
  }
  .message {
    max-width: 1100px;
    margin: 0 auto 20px;
    padding: 15px 20px;
    border-radius: 10px;
    font-weight: 600;
  }
  .success {
    background-color: #27ae60;
    color: white;
  }
  .error {
    background-color: #c0392b;
    color: white;
  }
  table {
    border-collapse: collapse;
    width: 100%;
    max-width: 1100px;
    margin: 0 auto 40px;
    background: #fff;
    border-radius: 12px;
    box-shadow: 0 8px 30px rgba(0,0,0,0.1);
  }
  th, td {
    padding: 14px 18px;
    border-bottom: 1px solid #eee;
    text-align: left;
  }
  th {
    background-color: #27ae60;
    color: white;
    font-weight: 700;
  }
  tr:hover {
    background-color: #f5f8fa;
  }
  button.end-auction {
    background-color: #c0392b;
    color: white;
    border: none;
    padding: 8px 14px;
    border-radius: 7px;
    cursor: pointer;
    font-weight: 700;
    transition: background-color 0.3s ease;
  }
  button.end-auction:hover {
    background-color: #e74c3c;
  }
  @media (max-width: 768px) {
    table, thead, tbody, th, td, tr {
      display: block;
    }
    thead tr {
      position: absolute;
      top: -9999px;
      left: -9999px;
    }
    tr {
      margin-bottom: 1rem;
      border: 1px solid #ddd;
      border-radius: 10px;
      padding: 10px;
    }
    td {
      border: none;
      position: relative;
      padding-left: 50%;
      white-space: normal;
      text-align: right;
    }
    td::before {
      position: absolute;
      left: 15px;
      width: 45%;
      padding-left: 10px;
      font-weight: 700;
      text-align: left;
      white-space: nowrap;
      content: attr(data-label);
      color: #555;
    }
    button.end-auction {
      width: 100%;
      padding: 12px 0;
      font-size: 1rem;
    }
  }
</style>
</head>
<body>

<h1>Manage Auctions - Super Admin</h1>

<?php if ($message): ?>
  <div class="message success"><?= htmlspecialchars($message) ?></div>
<?php endif; ?>

<?php if ($error): ?>
  <div class="message error"><?= htmlspecialchars($error) ?></div>
<?php endif; ?>

<table>
  <thead>
    <tr>
      <th>Auction ID</th>
      <th>Artwork Title</th>
      <th>Start Date</th>
      <th>End Date</th>
      <th>Start Price (৳)</th>
      <th>Current Highest Bid (৳)</th>
      <th>Status</th>
      <th>Action</th>
    </tr>
  </thead>
  <tbody>
  <?php if (empty($auctions)): ?>
    <tr><td colspan="8" style="text-align:center; color:#777;">No auctions found.</td></tr>
  <?php else: ?>
    <?php foreach ($auctions as $auction): ?>
      <tr>
        <td data-label="Auction ID"><?= htmlspecialchars($auction['AuctionID']) ?></td>
        <td data-label="Artwork Title"><?= htmlspecialchars($auction['ArtworkTitle']) ?></td>
        <td data-label="Start Date"><?= htmlspecialchars($auction['StartDateTime']) ?></td>
        <td data-label="End Date"><?= htmlspecialchars($auction['EndDateTime']) ?></td>
        <td data-label="Start Price"><?= number_format($auction['StartPrice'], 2) ?></td>
        <td data-label="Current Highest Bid"><?= number_format($auction['CurrentHighestBid'], 2) ?></td>
        <td data-label="Status"><?= htmlspecialchars($auction['Status']) ?></td>
        <td data-label="Action">
          <?php if ($auction['Status'] === 'Live'): ?>
            <form method="POST" onsubmit="return confirm('Are you sure you want to end this auction?');" style="margin:0;">
              <input type="hidden" name="end_auction_id" value="<?= htmlspecialchars($auction['AuctionID']) ?>" />
              <button type="submit" class="end-auction">End Auction</button>
            </form>
          <?php else: ?>
            <span style="color:#999;">N/A</span>
          <?php endif; ?>
        </td>
      </tr>
    <?php endforeach; ?>
  <?php endif; ?>
  </tbody>
</table>

<a href="super_admin_dashboard.php" style="display: block; text-align: center; font-weight: 600; color: #27ae60; text-decoration: none;">
  ← Back to Dashboard
</a>

</body>
</html>
