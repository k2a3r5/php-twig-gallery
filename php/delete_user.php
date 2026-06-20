<?php
session_start();
require 'db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin' || !isset($_GET['id'])) {
    header("Location: galleries.php");
    exit;
}

$userId = $_GET['id'];

if ($userId == $_SESSION['user_id']) {
    header("Location: admin_users.php");
    exit;
}

$stmt = $pdo->prepare("DELETE FROM users WHERE id = ?");
$stmt->execute([$userId]);

header("Location: admin_users.php");
exit;
?>
