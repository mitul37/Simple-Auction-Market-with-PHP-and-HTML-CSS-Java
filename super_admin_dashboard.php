<?php
session_start();
include 'config.php';

if (!isset($_SESSION['super_admin']) || $_SESSION['super_admin'] !== true) {
    header("Location: admin_login.php");
    exit();
}

// Fetch pending auctions for approval
$stmt = $pdo->prepare("SELECT * FROM Artwork WHERE IsAuction = 1 AND AuctionStatus = 'Pending' ORDER BY ArtworkID DESC");
$stmt->execute();
$pending_artworks = $stmt->fetchAll();

?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8" />
<meta name="viewport" content="width=device-width, initial-scale=1" />
<title>Super Admin Dashboard</title>
<style>
  /* Minimalistic CSS for Super Admin Dashboard */

  body {
    font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    background: #fafafa;
    color: #333;
    margin: 0;
    padding: 30px 20px;
  }

  h1, h2 {
    font-weight: 700;
    color: #222;
    margin-bottom: 25px;
    text-align: center;
  }

  .container {
    max-width: 1200px;
    margin: 0 auto 40px auto;
    background: #fff;
    border-radius: 12px;
    padding: 30px 40px;
    box-shadow: 0 8px 30px rgba(0,0,0,0.1);
  }

  /* Nav buttons for different management pages */
  .nav-buttons {
    display: flex;
    flex-wrap: wrap;
    justify-content: center;
    gap: 15px;
    margin-bottom: 40px;
  }

  .nav-buttons a {
    background: #27ae60;
    color: white;
    text-decoration: none;
    padding: 14px 25px;
    border-radius: 8px;
    font-weight: 600;
    font-size: 1rem;
    box-shadow: 0 4px 10px rgba(0,0,0,0.1);
    transition: background-color 0.3s ease;
    user-select: none;
  }

  .nav-buttons a:hover {
    background: #2ecc71;
  }

  /* Cards for pending auction requests */
  .card {
    border: 1px solid #e1e4e8;
    border-radius: 12px;
    padding: 20px;
    margin-bottom: 20px;
    display: flex;
    gap: 20px;
    align-items: flex-start;
    transition: box-shadow 0.3s ease;
  }

  .card:hover {
    box-shadow: 0 10px 25px rgba(0,0,0,0.12);
  }

  .card img {
    width: 160px;
    height: 120px;
    object-fit: cover;
    border-radius: 10px;
    flex-shrink: 0;
  }

  .card-content {
    flex-grow: 1;
  }

  .card-content h3 {
    margin: 0 0 12px 0;
    font-weight: 700;
    font-size: 1.3rem;
    color: #222;
  }

  .card-content p {
    margin: 0 0 8px 0;
    color: #555;
    line-height: 1.4;
    font-size: 1rem;
  }

  .price {
    font-weight: 700;
    color: #27ae60;
    font-size: 1.1rem;
    margin-top: 8px;
  }

  .actions {
    margin-top: 15px;
  }

  .actions a {
    display: inline-block;
    padding: 10px 18px;
    margin-right: 15px;
    border-radius: 8px;
    text-decoration: none;
    font-weight: 600;
    color: white;
    transition: background-color 0.3s ease;
    user-select: none;
    cursor: pointer;
  }

  .actions a.approve {
    background-color: #27ae60;
  }

  .actions a.approve:hover {
    background-color: #2ecc71;
  }

  .actions a.reject {
    background-color: #c0392b;
  }

  .actions a.reject:hover {
    background-color: #e74c3c;
  }

  @media (max-width: 600px) {
    .card {
      flex-direction: column;
      align-items: center;
    }

    .card img {
      width: 100%;
      height: auto;
    }

    .actions a {
      margin: 10px 10px 0 0;
    }

    .nav-buttons {
      flex-direction: column;
      gap: 10px;
    }

    .nav-buttons a {
      width: 100%;
      text-align: center;
    }
  }
</style>
</head>
<body>

<div class="container">
  <h1>Super Admin Dashboard</h1>

  <nav class="nav-buttons">
    <a href="super_admin_dashboard.php">Pending Auctions</a>
    <a href="manage_auctions.php">Manage Auctions</a>
    <a href="manage_artworks.php">Manage Artworks</a>
    <a href="manage_users.php">Manage Users</a>
    <a href="manage_transactions.php">Manage Transactions</a>
    <a href="manage_bids.php">Manage Bids</a>
    <a href="admin_logout.php">Logout</a>
  </nav>

  <h2>Pending Auction Requests</h2>

  <?php if (count($pending_artworks) === 0): ?>
      <p style="text-align:center; font-size:1.2rem; color:#777;">No pending auctions.</p>
  <?php else: ?>
      <?php foreach ($pending_artworks as $art): ?>
          <div class="card">
              <img src="uploads/<?= htmlspecialchars($art['ImageSourceURL']) ?>" alt="<?= htmlspecialchars($art['Title']) ?>" />
              <div class="card-content">
                  <h3><?= htmlspecialchars($art['Title']) ?></h3>
                  <p><?= nl2br(htmlspecialchars($art['Description'])) ?></p>
                  <p class="price">Requested Price: <?= htmlspecialchars(number_format($art['StartPrice'], 2)) ?> ৳</p>
                  <div class="actions">
                      <a href="approve_auction.php?id=<?= $art['ArtworkID'] ?>" class="approve" onclick="return confirm('Approve this auction?');">Approve</a>
                      <a href="reject_auction.php?id=<?= $art['ArtworkID'] ?>" class="reject" onclick="return confirm('Reject this auction?');">Reject</a>
                  </div>
              </div>
          </div>
      <?php endforeach; ?>
  <?php endif; ?>
</div>

</body>
</html>
