<?php
session_start();
require 'db.php';

if (!isset($_SESSION['user_id']) || !isset($_GET['id']) || !isset($_GET['gallery_id'])) {
    header("Location: galleries.php");
    exit;
}

$imageId = $_GET['id'];
$galleryId = $_GET['gallery_id'];

$stmt = $pdo->prepare("SELECT gi.id, gi.image_path, g.user_id 
                       FROM gallery_images gi 
                       JOIN galleries g ON gi.gallery_id = g.id 
                       WHERE gi.id = ?");
$stmt->execute([$imageId]);
$image = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$image) {
    header("Location: galleries.php");
    exit;
}

if ($_SESSION['role'] !== 'admin' && $image['user_id'] != $_SESSION['user_id']) {
    header("Location: galleries.php");
    exit;
}

$filePath = __DIR__ . '/' . $image['image_path'];

if (file_exists($filePath)) {
    unlink($filePath);
}

$stmt = $pdo->prepare("DELETE FROM gallery_images WHERE id = ?");
$stmt->execute([$imageId]);

header("Location: edit_gallery.php?id=" . $galleryId);
exit;
?>
