<?php
session_start();
require 'db.php';

if (!isset($_GET['id'])) {
    header("Location: galleries.php");
    exit;
}

$id = $_GET['id'];

$stmt = $pdo->prepare("SELECT id, user_id, title, description, created_at FROM galleries WHERE id = ?");
$stmt->execute([$id]);
$gallery = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$gallery) {
    header("Location: galleries.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_SESSION['user_id'])) {
    $content = trim($_POST['content'] ?? '');
    $image_id = (int)($_POST['image_id'] ?? 0);
    $author_name = $_SESSION['username'] ?? 'Anonim';

    if ($content !== '' && $image_id > 0) {
        $checkImg = $pdo->prepare("SELECT id FROM gallery_images WHERE id = ? AND gallery_id = ?");
        $checkImg->execute([$image_id, $id]);

        if ($checkImg->fetch()) {
            $stmtInsert = $pdo->prepare("
                INSERT INTO image_comments (image_id, user_id, author_name, content, created_at)
                VALUES (?, ?, ?, ?, NOW())
            ");
            $stmtInsert->execute([$image_id, $_SESSION['user_id'], $author_name, $content]);
        }
    }

    header("Location: gallery.php?id=" . $id);
    exit;
}

$stmtImg = $pdo->prepare("SELECT id, image_path, alt_text FROM gallery_images WHERE gallery_id = ?");
$stmtImg->execute([$id]);
$images = $stmtImg->fetchAll(PDO::FETCH_ASSOC);

foreach ($images as &$img) {
    $img['image_url'] = '/galerieblog/public/' . ltrim($img['image_path'], '/');

    $stmtImgComments = $pdo->prepare("
        SELECT id, image_id, user_id, author_name, content, created_at
        FROM image_comments
        WHERE image_id = ?
        ORDER BY id DESC
    ");
    $stmtImgComments->execute([$img['id']]);
    $img['comments'] = $stmtImgComments->fetchAll(PDO::FETCH_ASSOC);
}
unset($img);

$twig = require_once '../twig_init.php';
echo $twig->render('gallery.twig', [
    'gallery' => $gallery,
    'images' => $images,
    'session_username' => $_SESSION['username'] ?? null,
    'session_user_id' => $_SESSION['user_id'] ?? null
]);
?>
