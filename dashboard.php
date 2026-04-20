<?php
require_once 'includes/auth.php';
requireLogin();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Dashboard — Helpdesk</title>
</head>
<body>
    <h1>Bienvenue, <?= htmlspecialchars($_SESSION['name']) ?> !</h1>
    <p>Rôle : <strong><?= htmlspecialchars($_SESSION['role']) ?></strong></p>
    <a href="logout.php">Se déconnecter</a>
</body>
</html>