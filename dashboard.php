<?php
session_start();
include 'config.php';

if (!isset($_SESSION['user'])) {
    header('Location: login.php');
    exit();
}

$user_id = $_SESSION['user']['UserID'];
$success_message = '';
$error_message = '';

// Handle Upload Form Submission
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['upload_artwork'])) {
    $title = trim($_POST['title']);
    $description = trim($_POST['description']);
    $category = trim($_POST['category']);

    $image_name = time() . "_" . basename($_FILES['image']['name']);
    $target = "uploads/" . $image_name;

    if (move_uploaded_file($_FILES['image']['tmp_name'], $target)) {
        $stmt = $pdo->prepare("INSERT INTO Artwork (UserID, Title, Description, ImageSourceURL, UploadDate, Category, IsAuction, AuctionStatus) VALUES (?, ?, ?, ?, NOW(), ?, 0, 'Not Listed')");
        $stmt->execute([$user_id, $title, $description, $image_name, $category]);
        $success_message = "Artwork uploaded successfully!";
    } else {
        $error_message = "Failed to upload artwork.";
    }
}

// Fetch Wallet Balance
$stmt = $pdo->prepare("SELECT WalletBalance FROM User WHERE UserID = ?");
$stmt->execute([$user_id]);
$balance = $stmt->fetchColumn();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Dashboard - Art Auction</title>
    <link rel="stylesheet" href="css/style.css">
    <style>
        .dashboard-layout {
            display: flex;
            flex-wrap: wrap;
            gap: 30px;
            padding: 30px;
        }
        .left-side {
            flex: 2;
        }
        .right-side {
            flex: 1;
            background: rgba(255,255,255,0.05);
            backdrop-filter: blur(8px);
            border-radius: 20px;
            padding: 20px;
            box-shadow: 0px 4px 20px rgba(0,0,0,0.2);
            height: fit-content;

            position: sticky;   
            top: 100px;         
        }

        .right-side h3 {
            margin-top: 0;
            color: #fff;
        }
        .right-side form input, .right-side form textarea, .right-side form select {
            width: 100%;
            margin: 8px 0;
            padding: 10px;
            border-radius: 5px;
            border: none;
            background: #f0f0f0;
        }
        .right-side form button {
            width: 100%;
            background-color: #8e44ad;
            color: white;
            padding: 12px;
            margin-top: 10px;
            border: none;
            border-radius: 30px;
            font-weight: bold;
            transition: 0.3s ease;
        }
        .right-side form button:hover {
            background-color: #c2a4ff;
            transform: scale(1.05);
        }
    </style>
</head>
<body>

<?php include 'includes/header.php'; ?>

<h2>🎯 Dashboard</h2>

<?php if ($success_message): ?>
    <div class="success-message"><?= $success_message ?></div>
<?php endif; ?>

<?php if ($error_message): ?>
    <div class="error-message"><?= $error_message ?></div>
<?php endif; ?>

<h3>💰 Your Wallet Balance: <?= number_format($balance, 2) ?> ৳</h3>

<div class="dashboard-layout">

    <!-- LEFT: Your Artworks -->
    <div class="left-side">
        <h2>🎨 Your Artworks</h2>

        <div class="grid-container">
        <?php
        $stmt = $pdo->prepare("SELECT * FROM Artwork WHERE UserID = ?");
        $stmt->execute([$user_id]);
        $artworks = $stmt->fetchAll();

        if (count($artworks) === 0) {
            echo "<p>You don't have any artworks yet.</p>";
        } else {
            foreach ($artworks as $art) {
                echo "<div class='grid-item'>
                        <img src='uploads/{$art['ImageSourceURL']}' alt='{$art['Title']}'>
                        <h3><a href='view_art.php?id={$art['ArtworkID']}' style='text-decoration:none; color:inherit;'>{$art['Title']}</a></h3>";

                if (!$art['IsAuction']) {
                    echo "<p><strong>Status:</strong> Not listed</p>";
                    echo "<a href='request_auction.php?id={$art['ArtworkID']}'>🎯 Request Auction</a> | ";
                } elseif ($art['AuctionStatus'] == 'Live') {
                    echo "<p><strong>Status:</strong> Auction Live</p>";
                } elseif ($art['AuctionStatus'] == 'Ended') {
                    echo "<p><strong>Status:</strong> Auction Ended</p>";
                } else {
                    echo "<p><strong>Status:</strong> Auction Pending</p>";
                }

                echo "<a href='edit_art.php?id={$art['ArtworkID']}'>✏️ Edit</a> |
                      <a href='delete_art.php?id={$art['ArtworkID']}' onclick=\"return confirm('Delete this artwork?');\">🗑️ Delete</a>";

                echo "</div>";
            }
        }
        ?>
        </div>
    </div>

    <!-- RIGHT: Upload New Artwork -->
    <div class="right-side">
        <h3>➕ Upload New Artwork</h3>
        <form method="POST" enctype="multipart/form-data">
            <input type="text" name="title" placeholder="Artwork Title" required>
            <textarea name="description" placeholder="Artwork Description" required></textarea>
            <select name="category" required>
                <option value="">Select Category</option>
                <option value="Painting">Painting</option>
                <option value="Photography">Photography</option>
                <option value="Sculpture">Sculpture</option>
                <option value="Digital Art">Digital Art</option>
            </select>
            <input type="file" name="image" required>
            <button type="submit" name="upload_artwork">UPLOAD</button>
        </form>
    </div>

</div>

</body>
</html>
