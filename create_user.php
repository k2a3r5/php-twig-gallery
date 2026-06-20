<?php
require 'db.php';

$username = 'admin';
$email = 'admin@test.ro';
$password = '123456';
$role = 'admin';

$hash = password_hash($password, PASSWORD_DEFAULT);

$stmt = $pdo->prepare("INSERT INTO users (username, email, password_hash, role) VALUES (?, ?, ?, ?)");
$stmt->execute([$username, $email, $hash, $role]);

echo "User creat";