<?php
session_start();
require 'db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: galleries.php");
    exit;
}

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    if ($_POST['action'] === 'add') {
        $username = trim($_POST['username'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';
        $role = $_POST['role'] ?? 'user';

        if ($username === '' || $email === '' || $password === '') {
            $error = "Username, email si parola sunt obligatorii.";
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $error = "Email invalid.";
        } else {
            $stmt = $pdo->prepare("SELECT id FROM users WHERE username = ? OR email = ?");
            $stmt->execute([$username, $email]);

            if ($stmt->fetch()) {
                $error = "Username-ul sau email-ul exista deja.";
            } else {
                $passwordHash = password_hash($password, PASSWORD_DEFAULT);

                $stmt = $pdo->prepare("
                    INSERT INTO users (username, email, password_hash, role, created_at)
                    VALUES (?, ?, ?, ?, NOW())
                ");
                $stmt->execute([$username, $email, $passwordHash, $role]);

                $success = "Utilizator adaugat cu succes!";
            }
        }
    } elseif ($_POST['action'] === 'update_role') {
        $userId = $_POST['user_id'] ?? 0;
        $newRole = $_POST['role'] ?? 'user';

        $stmt = $pdo->prepare("UPDATE users SET role = ? WHERE id = ?");
        $stmt->execute([$newRole, $userId]);

        $success = "Rolul utilizatorului a fost actualizat!";
    }
}

$stmt = $pdo->query("SELECT id, username, email, role, created_at FROM users ORDER BY id DESC");
$users = $stmt->fetchAll(PDO::FETCH_ASSOC);

$twig = require_once '../twig_init.php';
echo $twig->render('admin_users.twig', [
    'users' => $users,
    'error' => $error,
    'success' => $success,
    'session_username' => $_SESSION['username'] ?? null,
    'session_role' => $_SESSION['role'] ?? null
]);
?>
