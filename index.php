<?php
include 'config.php';
session_start();
?>

<!DOCTYPE html>
<html>
<head>
    <title>Art Auction Home</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
<div id="loader-wrapper">
    <div class="loader"></div>
</div>

<?php include 'includes/header.php'; ?>

<div class="grid-container">
<?php
$search = $_GET['search'] ?? '';
$stmt = $pdo->prepare("
    SELECT Artwork.*, User.Username 
    FROM Artwork 
    JOIN User ON Artwork.UserID = User.UserID 
    WHERE Artwork.Title LIKE ?
");
$stmt->execute(["%$search%"]);
$artworks = $stmt->fetchAll();

if (count($artworks) === 0) {
    echo "<p>No artworks found.</p>";
} else {
    foreach ($artworks as $art) {
        echo "<div class='grid-item'>
                <img src='uploads/{$art['ImageSourceURL']}' alt='{$art['Title']}'>
                <h3>{$art['Title']}</h3>
                <h5>{$art['Description']}</h5>
                <p>By: {$art['Username']}</p>
              </div>";
    }
}
?>
</div>
<script>
window.addEventListener('load', function() {
    document.getElementById('loader-wrapper').style.display = 'none';
});
</script>


</body>
</html>
