<?php
session_start();
require 'db.php';

if (!isset($_SESSION['user_id']) || !isset($_GET['id'])) {
    header("Location: galleries.php");
    exit;
}

$id = $_GET['id'];

$stmt = $pdo->prepare("SELECT user_id FROM galleries WHERE id = ?");
$stmt->execute([$id]);
$gallery = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$gallery) {
    header("Location: galleries.php");
    exit;
}

if ($_SESSION['role'] !== 'admin' && $gallery['user_id'] != $_SESSION['user_id']) {
    header("Location: galleries.php");
    exit;
}

$stmtImg = $pdo->prepare("SELECT image_path FROM gallery_images WHERE gallery_id = ?");
$stmtImg->execute([$id]);
$images = $stmtImg->fetchAll(PDO::FETCH_ASSOC);

foreach ($images as $img) {
    $filePath = __DIR__ . '/' . $img['image_path'];
    if (file_exists($filePath)) {
        unlink($filePath);
    }
}

$pdo->prepare("DELETE FROM gallery_images WHERE gallery_id = ?")->execute([$id]);
$pdo->prepare("DELETE FROM gallery_comments WHERE gallery_id = ?")->execute([$id]);
$pdo->prepare("DELETE FROM galleries WHERE id = ?")->execute([$id]);

header("Location: galleries.php");
exit;
?>
