<?php
session_start();
include 'config.php';

// Check if the user is logged in as super admin
if (!isset($_SESSION['super_admin']) || $_SESSION['super_admin'] !== true) {
    header("Location: admin_login.php");
    exit();
}

// Fetch artworks pending auction approval
$stmt = $pdo->query("SELECT * FROM Artwork WHERE IsAuction = 1 AND AuctionStatus = 'Pending'");
$pending_artworks = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <title>Super Admin Dashboard - Approve Auctions</title>
    <link rel="stylesheet" href="css/style.css" />
    <style>
        /* Basic inline styling for the dashboard items - move to CSS file if needed */
        .artwork-card {
            border: 1px solid #ccc;
            padding: 15px;
            margin: 15px 0;
            border-radius: 8px;
            display: flex;
            gap: 20px;
            align-items: center;
            background: #fff;
            box-shadow: 0 2px 6px rgba(0,0,0,0.1);
        }
        .artwork-image img {
            width: 150px;
            border-radius: 6px;
        }
        .artwork-info {
            flex-grow: 1;
        }
        .artwork-actions a {
            margin-right: 15px;
            text-decoration: none;
            font-weight: bold;
            padding: 6px 12px;
            border-radius: 4px;
        }
        .approve-btn {
            background-color: #28a745;
            color: white;
        }
        .reject-btn {
            background-color: #dc3545;
            color: white;
        }
        .nav-links {
            margin-bottom: 20px;
        }
        .nav-links a {
            margin-right: 20px;
            font-weight: 600;
            color: #007bff;
            text-decoration: none;
        }
        .nav-links a:hover {
            text-decoration: underline;
        }
        .success-msg {
            color: green;
            font-weight: bold;
            margin-bottom: 20px;
        }
    </style>
</head>
<body>

<div class="nav-links">
    <a href="super_admin_dashboard.php">Pending Auctions</a>
    <a href="manage_users.php">Manage Users</a>
    <a href="manage_artworks.php">Manage Artworks</a>
    <a href="manage_auctions.php">Manage Auctions</a>
    <a href="manage_transactions.php">Manage Transactions</a>
    <a href="admin_logout.php">Logout</a>
</div>

<h2>📋 Pending Auction Requests</h2>

<?php if (isset($_GET['approved'])): ?>
    <p class="success-msg">✅ Auction approved successfully!</p>
<?php elseif (isset($_GET['rejected'])): ?>
    <p class="success-msg" style="color:#dc3545;">❌ Auction rejected successfully!</p>
<?php endif; ?>

<?php if (count($pending_artworks) === 0): ?>
    <p>No pending auctions.</p>
<?php else: ?>
    <?php foreach ($pending_artworks as $art): ?>
        <div class="artwork-card">
            <div class="artwork-image">
                <img src="uploads/<?= htmlspecialchars($art['ImageSourceURL']) ?>" alt="<?= htmlspecialchars($art['Title']) ?>" />
            </div>
            <div class="artwork-info">
                <h3><?= htmlspecialchars($art['Title']) ?></h3>
                <p><?= nl2br(htmlspecialchars($art['Description'])) ?></p>
                <p><strong>Requested Start Price:</strong> <?= htmlspecialchars($art['StartPrice']) ?> ৳</p>
            </div>
            <div class="artwork-actions">
                <a href="approve_auction.php?id=<?= $art['ArtworkID'] ?>" class="approve-btn" onclick="return confirm('Are you sure you want to approve this auction?');">✅ Approve</a>
                <a href="reject_auction.php?id=<?= $art['ArtworkID'] ?>" class="reject-btn" onclick="return confirm('Are you sure you want to reject this auction?');">❌ Reject</a>
            </div>
        </div>
    <?php endforeach; ?>
<?php endif; ?>

</body>
</html>
