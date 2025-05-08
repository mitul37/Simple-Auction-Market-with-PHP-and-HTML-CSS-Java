<?php
session_start();
include 'config.php';

if (!isset($_SESSION['user'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user']['UserID'];
$art_id = $_GET['id'] ?? 0;

$stmt = $pdo->prepare("DELETE FROM Artwork WHERE ArtworkID = ? AND UserID = ?");
$stmt->execute([$art_id, $user_id]);

header("Location: dashboard.php");
exit();
