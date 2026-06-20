<?php
session_start();
require 'db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
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
        $stmt = $pdo->prepare("INSERT INTO galleries (user_id, title, description, created_at) VALUES (?, ?, ?, NOW())");
        $stmt->execute([$_SESSION['user_id'], $title, $description]);
        $galleryId = $pdo->lastInsertId();

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
                        $stmt->execute([$galleryId, $imagePathForDb, $altText]);
                    }
                }
            }
        }

        $success = "Galeria a fost creata cu succes!";
    }
}

$twig = require_once '../twig_init.php';
echo $twig->render('add_gallery.twig', [
    'error' => $error,
    'success' => $success,
    'session_username' => $_SESSION['username'] ?? null
]);
?>
