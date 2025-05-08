<?php
session_start();
include 'config.php';

if (!isset($_GET['id'])) {
    die('Invalid Artwork ID');
}

$artwork_id = (int)$_GET['id'];

// Fetch artwork details
$stmt = $pdo->prepare("SELECT Artwork.*, SA.SoldAt, SA.SoldPrice
    FROM Artwork 
    LEFT JOIN SoldArtworks SA ON Artwork.ArtworkID = SA.ArtworkID
    WHERE Artwork.ArtworkID = ?");
$stmt->execute([$artwork_id]);
$artwork = $stmt->fetch();

if (!$artwork) {
    die('Artwork not found.');
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title><?= htmlspecialchars($artwork['Title']) ?> - Art Preview</title>
    <link rel="stylesheet" href="css/style.css">
    <style>
        body {
            margin: 0;
            height: 100vh;
            background: linear-gradient(to right top, #c1dfc4, #deecdd, #f5f5f5);
            display: flex;
            justify-content: center;
            align-items: center;
            overflow: hidden;
            font-family: 'Arial', sans-serif;
            position: relative;
        }

        .back-button {
            position: absolute;
            top: 20px;
            left: 20px;
            background: rgba(255, 255, 255, 0.3);
            padding: 10px 20px;
            border-radius: 30px;
            text-decoration: none;
            color: #000;
            font-weight: bold;
            backdrop-filter: blur(10px);
        }

        .artwork-container {
            position: relative;
            width: 70%;
            max-width: 900px;
            aspect-ratio: 16/9;
            background: rgba(255, 255, 255, 0.05);
            border-radius: 20px;
            box-shadow: 0 8px 32px rgba(31, 38, 135, 0.37);
            overflow: hidden;
            animation: float 6s ease-in-out infinite;
        }

        .artwork-container img {
            width: 100%;
            height: 100%;
            object-fit: contain;
            transition: 0.5s ease;
        }

        @keyframes float {
            0% { transform: translatey(0px); }
            50% { transform: translatey(-20px); }
            100% { transform: translatey(0px); }
        }

        .info-panel {
            position: absolute;
            top: 0;
            right: -300px;
            width: 300px;
            height: 100%;
            background: rgba(0,0,0,0.6);
            color: #fff;
            padding: 20px;
            box-sizing: border-box;
            transition: right 0.5s ease;
            backdrop-filter: blur(8px);
        }

        .artwork-container:hover .info-panel {
            right: 0;
        }

        .info-panel h2 {
            margin-top: 0;
            font-size: 24px;
        }

        .info-panel p {
            margin-top: 10px;
            font-size: 16px;
        }
    </style>
</head>
<body>

<a href="dashboard.php" class="back-button">← Back to Dashboard</a>

<div class="artwork-container">
    <img src="uploads/<?= htmlspecialchars($artwork['ImageSourceURL']) ?>" alt="<?= htmlspecialchars($artwork['Title']) ?>">
    <div class="info-panel">
        <h2><?= htmlspecialchars($artwork['Title']) ?></h2>
        <p><strong>Description:</strong><br> <?= nl2br(htmlspecialchars($artwork['Description'])) ?></p>
        <?php if (!empty($artwork['SoldAt'])): ?>
            <p><strong>Bought On:</strong><br> <?= htmlspecialchars($artwork['SoldAt']) ?></p>
            <p><strong>Price:</strong><br> <?= htmlspecialchars($artwork['SoldPrice']) ?>৳</p>
        <?php else: ?>
            <p>Not sold yet.</p>
        <?php endif; ?>
    </div>
</div>

</body>
</html>
