<?php
session_start();

if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="ro">
<head>
    <meta charset="UTF-8">
    <title>Pagina principală</title>
</head>
<body>
    <h1>Bun venit, <?php echo $_SESSION['username']; ?>!</h1>
    <p>Rolul tău este: <?php echo $_SESSION['role']; ?></p>

    <a href="logout.php">Logout</a>
</body>
</html>