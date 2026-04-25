<?php
require_once 'includes/auth.php';
requireLogin();
?>
<?php require_once 'includes/header.php'; ?>
<?php require_once 'includes/navbar.php'; ?>

<div class="container">
    <h1 class="mb-4">Dashboard</h1>
    <h1>Bienvenue, <?= htmlspecialchars($_SESSION['name']) ?> !</h1>
    <p>Rôle : <strong><?= htmlspecialchars($_SESSION['role']) ?></strong></p>
</div>

<?php require_once 'includes/footer.php'; ?>