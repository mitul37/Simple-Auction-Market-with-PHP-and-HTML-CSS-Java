<?php
session_start();
include 'config.php';

if (!isset($_SESSION['user'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user']['UserID'];
$art_id = $_GET['id'] ?? 0;


if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $title = $_POST['title'];
    $desc  = $_POST['description'];
    $image = $_FILES['image'];

    if ($image['name']) {
        $filename = time() . '_' . basename($image['name']);
        move_uploaded_file($image['tmp_name'], "uploads/$filename");

        $stmt = $pdo->prepare("UPDATE Artwork SET Title = ?, Description = ?, ImageSourceURL = ? WHERE ArtworkID = ? AND UserID = ?");
        $stmt->execute([$title, $desc, $filename, $art_id, $user_id]);
    } else {
        $stmt = $pdo->prepare("UPDATE Artwork SET Title = ?, Description = ? WHERE ArtworkID = ? AND UserID = ?");
        $stmt->execute([$title, $desc, $art_id, $user_id]);
    }

    header("Location: dashboard.php");
    exit();
}


$stmt = $pdo->prepare("SELECT * FROM Artwork WHERE ArtworkID = ? AND UserID = ?");
$stmt->execute([$art_id, $user_id]);
$art = $stmt->fetch();

if (!$art) {
    echo "Access denied.";
    exit();
}
?>

<h2>Edit Artwork</h2>
<form method="POST" enctype="multipart/form-data">
    <input name="title" value="<?= htmlspecialchars($art['Title']) ?>" required><br>
    <textarea name="description" required><?= htmlspecialchars($art['Description']) ?></textarea><br>
    <input type="file" name="image"><br>
    <small>Leave blank to keep current image.</small><br><br>
    <button type="submit">Update</button>
</form>
