<?php
session_start();
require 'db.php';

if (!isset($_SESSION['user_id']) || !isset($_GET['id'])) {
    header("Location: galleries.php");
    exit;
}

$id = $_GET['id'];

$stmt = $pdo->prepare("SELECT id, user_id, title, description FROM galleries WHERE id = ?");
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

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = trim($_POST['title'] ?? '');
    $description = trim($_POST['description'] ?? '');

    if ($title === '') {
        $error = "Titlul este obligatoriu.";
    } else {
        $stmt = $pdo->prepare("UPDATE galleries SET title = ?, description = ? WHERE id = ?");
        $stmt->execute([$title, $description, $id]);

        if (isset($_FILES['images']) && is_array($_FILES['images']['name'])) {
            $uploadDir = __DIR__ . '/uploads/';

            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0755, true);
            }

            foreach ($_FILES['images']['tmp_name'] as $key => $tmpName) {
                if ($_FILES['images']['error'][$key] === UPLOAD_ERR_OK) {
                    $fileName = basename($_FILES['images']['name'][$key]);
                    $safeFileName = preg_replace('/[^A-Za-z0-9_\-\.]/', '_', $fileName);
                    $newFileName = uniqid() . '_' . $safeFileName;

                    $targetPath = $uploadDir . $newFileName;
                    $imagePathForDb = 'uploads/' . $newFileName;

                    if (move_uploaded_file($tmpName, $targetPath)) {
                        $altText = pathinfo($fileName, PATHINFO_FILENAME);
                        $stmt = $pdo->prepare("INSERT INTO gallery_images (gallery_id, image_path, alt_text) VALUES (?, ?, ?)");
                        $stmt->execute([$id, $imagePathForDb, $altText]);
                    }
                }
            }
        }

        $success = "Galeria a fost actualizata cu succes!";
        $gallery['title'] = $title;
        $gallery['description'] = $description;
    }
}

$stmtImg = $pdo->prepare("SELECT id, image_path, alt_text FROM gallery_images WHERE gallery_id = ?");
$stmtImg->execute([$id]);
$images = $stmtImg->fetchAll(PDO::FETCH_ASSOC);

foreach ($images as &$img) {
    $img['image_url'] = '/galerieblog/public/' . ltrim($img['image_path'], '/');
}
unset($img);

$twig = require_once '../twig_init.php';
echo $twig->render('edit_gallery.twig', [
    'gallery' => $gallery,
    'images' => $images,
    'error' => $error,
    'success' => $success,
    'session_username' => $_SESSION['username'] ?? null
]);
?>