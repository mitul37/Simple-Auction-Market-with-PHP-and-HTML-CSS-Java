<?php
session_start();
include 'config.php';

// Check if user is super admin
if (!isset($_SESSION['super_admin']) || $_SESSION['super_admin'] !== true) {
    header("Location: admin_login.php");
    exit();
}

// Handle image upload
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $action = $_POST['action']; 
    $artwork_id = $_POST['artwork_id'];
    $title = $_POST['title'];
    $description = $_POST['description'];
    $image = $_FILES['image']['name'];

    if ($action == 'add') {
        // Upload image
        move_uploaded_file($_FILES['image']['tmp_name'], "uploads/" . $image);
        $stmt = $pdo->prepare("INSERT INTO Artwork (Title, Description, ImageSourceURL) VALUES (?, ?, ?)");
        $stmt->execute([$title, $description, $image]);
        echo "Artwork added successfully!";
    } elseif ($action == 'edit') {
        // Edit artwork
        $stmt = $pdo->prepare("UPDATE Artwork SET Title = ?, Description = ?, ImageSourceURL = ? WHERE ArtworkID = ?");
        $stmt->execute([$title, $description, $image, $artwork_id]);
        echo "Artwork updated successfully!";
    } elseif ($action == 'remove') {
        // Remove artwork
        $stmt = $pdo->prepare("DELETE FROM Artwork WHERE ArtworkID = ?");
        $stmt->execute([$artwork_id]);
        echo "Artwork removed successfully!";
    }
}

// Get artwork list
$stmt = $pdo->prepare("SELECT * FROM Artwork");
$stmt->execute();
$artworks = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Manage Artworks</title>
</head>
<body>

<h2>Manage Artworks</h2>
<form method="POST" enctype="multipart/form-data">
    <label>Action:</label>
    <select name="action" required>
        <option value="add">Add Artwork</option>
        <option value="edit">Edit Artwork</option>
        <option value="remove">Remove Artwork</option>
    </select><br><br>
    
    <label>Title:</label>
    <input type="text" name="title" required><br><br>
    
    <label>Description:</label>
    <textarea name="description" required></textarea><br><br>
    
    <label>Image:</label>
    <input type="file" name="image"><br><br>
    
    <label>Artwork ID (for edit/remove only):</label>
    <input type="number" name="artwork_id"><br><br>
    
    <button type="submit">Submit</button>
</form>

<h3>Artworks List</h3>
<table>
    <tr>
        <th>ArtworkID</th>
        <th>Title</th>
        <th>Description</th>
        <th>Image</th>
    </tr>
    <?php foreach ($artworks as $artwork): ?>
    <tr>
        <td><?php echo $artwork['ArtworkID']; ?></td>
        <td><?php echo $artwork['Title']; ?></td>
        <td><?php echo $artwork['Description']; ?></td>
        <td><img src="uploads/<?php echo $artwork['ImageSourceURL']; ?>" width="100"></td>
    </tr>
    <?php endforeach; ?>
</table>

</body>
</html>
