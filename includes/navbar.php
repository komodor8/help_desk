<?php
if (!isset($_SESSION['user_id'])) return;

$role = $_SESSION['role'];
$name = $_SESSION['name'];

// Badge couleur selon le rôle
$badgeClass = $role === 'tuteur' ? 'bg-warning text-dark' : 'bg-primary';
$roleLabel  = $role === 'tuteur' ? 'Tuteur' : 'Étudiant';
?>
<nav class="navbar navbar-expand-lg navbar-dark bg-dark mb-4">
    <div class="container">

        <!-- Logo / nom de l'app -->
        <a class="navbar-brand fw-bold" href="dashboard.php">
            <i class="bi bi-headset me-2"></i>Helpdesk
        </a>

        <!-- Bouton hamburger sur mobile -->
        <button class="navbar-toggler" type="button"
                data-bs-toggle="collapse" data-bs-target="#mainNav">
            <span class="navbar-toggler-icon"></span>
        </button>

        <div class="collapse navbar-collapse" id="mainNav">

            <!-- Liens de gauche -->
            <ul class="navbar-nav me-auto">
                <li class="nav-item">
                    <a class="nav-link" href="list.php">
                        <i class="bi bi-ticket me-1"></i>Tickets
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="http://localhost:8081" target="_blank">
                        <i class="bi bi-database me-1"></i>Adminer
                    </a>
                </li>
            </ul>

            <!-- Partie droite : utilisateur + déconnexion -->
            <ul class="navbar-nav ms-auto align-items-center gap-2">
                <li class="nav-item">
                    <span class="navbar-text text-white me-1">
                        <i class="bi bi-person-circle me-1"></i>
                        <?= htmlspecialchars($name) ?>
                    </span>
                    <span class="badge <?= $badgeClass ?>">
                        <?= $roleLabel ?>
                    </span>
                </li>
                <li class="nav-item">
                    <a class="btn btn-outline-light btn-sm" href="/logout.php">
                        <i class="bi bi-box-arrow-right me-1"></i>Déconnexion
                    </a>
                </li>
            </ul>

        </div>
    </div>
</nav>