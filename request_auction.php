<?php
session_start();
include 'config.php';

if (!isset($_SESSION['user'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user']['UserID'];
$art_id = $_GET['id'] ?? 0;

$stmt = $pdo->prepare("SELECT * FROM Artwork WHERE ArtworkID = ? AND UserID = ?");
$stmt->execute([$art_id, $user_id]);
$artwork = $stmt->fetch();

if (!$artwork) {
    echo "❌ Access Denied.";
    exit();
}

$error = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $start_price = (float)$_POST['start_price'];

    if ($start_price > 0) {
        // Define auction start and end time (e.g., 7 days auction)
        $startDateTime = date('Y-m-d H:i:s');
        $endDateTime = date('Y-m-d H:i:s', strtotime('+7 days'));

        // Insert auction with the correct artwork ID ($art_id)
        $stmt = $pdo->prepare("INSERT INTO Auction (ArtworkID, StartDateTime, EndDateTime, StartPrice, CurrentHighestBid, Status) VALUES (?, ?, ?, ?, 0, 'Live')");
        $stmt->execute([$art_id, $startDateTime, $endDateTime, $start_price]);

        header("Location: dashboard.php?auction_requested=1");
        exit();
    } else {
        $error = "Invalid Starting Price.";
    }
}
?>

<h2>🎯 Request Auction for <?= htmlspecialchars($artwork['Title']) ?></h2>

<?php if (!empty($error)) echo "<p style='color:red;'>$error</p>"; ?>

<form method="POST">
    <label>Set Your Starting Price (Minimum Bid Price)</label><br><br>
    <input type="number" name="start_price" step="0.01" min="0.01" required><br><br>
    <button type="submit">Submit Auction Request</button>
</form>
