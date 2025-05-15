<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

ini_set('display_errors', 1);
error_reporting(E_ALL);

include 'config.php';

// Super admin check
if (!isset($_SESSION['super_admin']) || $_SESSION['super_admin'] !== true) {
    header("Location: admin_login.php");
    exit();
}

// Fetch bids joined with user and artwork and auction info
$sql = "
    SELECT 
        b.BidID, b.BidAmount, b.BidTime,
        u.UserName, u.Email,
        a.AuctionID, a.Status AS AuctionStatus,
        art.Title AS ArtworkTitle
    FROM Bid b
    JOIN User u ON b.UserID = u.UserID
    JOIN Auction a ON b.AuctionID = a.AuctionID
    JOIN Artwork art ON a.ArtworkID = art.ArtworkID
    ORDER BY b.BidTime DESC
";

$stmt = $pdo->prepare($sql);
$stmt->execute();
$bids = $stmt->fetchAll();

?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8" />
<meta name="viewport" content="width=device-width, initial-scale=1" />
<title>Manage Bids - Super Admin</title>
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
  table {
    border-collapse: collapse;
    width: 100%;
    max-width: 1200px;
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
  }
</style>
</head>
<body>

<h1>Manage Bids - Super Admin</h1>

<?php if (empty($bids)): ?>
  <p style="text-align:center; font-size:1.2rem; color:#777;">No bids found.</p>
<?php else: ?>
  <table>
    <thead>
      <tr>
        <th>Bid ID</th>
        <th>User Name</th>
        <th>User Email</th>
        <th>Artwork Title</th>
        <th>Auction ID</th>
        <th>Auction Status</th>
        <th>Bid Amount (৳)</th>
        <th>Bid Time</th>
      </tr>
    </thead>
    <tbody>
      <?php foreach ($bids as $bid): ?>
        <tr>
          <td data-label="Bid ID"><?= htmlspecialchars($bid['BidID']) ?></td>
          <td data-label="User Name"><?= htmlspecialchars($bid['UserName']) ?></td>
          <td data-label="User Email"><?= htmlspecialchars($bid['Email']) ?></td>
          <td data-label="Artwork Title"><?= htmlspecialchars($bid['ArtworkTitle']) ?></td>
          <td data-label="Auction ID"><?= htmlspecialchars($bid['AuctionID']) ?></td>
          <td data-label="Auction Status"><?= htmlspecialchars($bid['AuctionStatus']) ?></td>
          <td data-label="Bid Amount"><?= number_format($bid['BidAmount'], 2) ?></td>
          <td data-label="Bid Time"><?= htmlspecialchars($bid['BidTime']) ?></td>
        </tr>
      <?php endforeach; ?>
    </tbody>
  </table>
<?php endif; ?>

<a href="super_admin_dashboard.php" style="display: block; text-align:center; font-weight: 600; color: #27ae60; text-decoration: none; margin-top: 20px;">
  ← Back to Dashboard
</a>

</body>
</html>
