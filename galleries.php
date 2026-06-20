<?php
session_start();
require 'db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

if ($_SESSION['role'] === 'admin') {
    $stmt = $pdo->query("SELECT id, user_id, title, description, created_at FROM galleries ORDER BY id DESC");
} else {
    $stmt = $pdo->prepare("SELECT id, user_id, title, description, created_at FROM galleries WHERE user_id = ? ORDER BY id DESC");
    $stmt->execute([$_SESSION['user_id']]);
}

$galleries = $stmt->fetchAll(PDO::FETCH_ASSOC);

foreach ($galleries as &$gallery) {
    $stmtImg = $pdo->prepare("SELECT image_path FROM gallery_images WHERE gallery_id = ? LIMIT 1");
    $stmtImg->execute([$gallery['id']]);
    $firstImage = $stmtImg->fetch(PDO::FETCH_ASSOC);

    if ($firstImage && !empty($firstImage['image_path'])) {
        $fileName = basename($firstImage['image_path']);
        $gallery['first_image_url'] = 'uploads/' . $fileName;
    } else {
        $gallery['first_image_url'] = null;
    }
}
unset($gallery);

$twig = require_once '../twig_init.php';
echo $twig->render('galleries.twig', [
    'galleries' => $galleries,
    'session_username' => $_SESSION['username'] ?? null,
    'session_role' => $_SESSION['role'] ?? null
]);
?>