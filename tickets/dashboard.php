<?php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/db.php';
requireLogin();

// Récupère les infos complètes depuis la base (plus fiable que la session)
$pdo  = getDB();
$stmt = $pdo->prepare('SELECT * FROM users WHERE id = :id');
$stmt->execute([':id' => $_SESSION['user_id']]);
$user = $stmt->fetch();

// Initiale pour l'avatar
$initiale = mb_strtoupper(mb_substr($_SESSION['name'], 0, 1));

// Label et couleur du rôle
$roleLabel = $_SESSION['role'] === 'tuteur' ? 'Tuteur' : 'Étudiant';
$roleBadge = $_SESSION['role'] === 'tuteur'
    ? 'text-warning bg-warning'
    : 'text-primary bg-primary';

// Date formatée
$since = $user['created_at']
    ? (new DateTime($user['created_at']))->format('d/m/Y')
    : '—';

$pageTitle = 'Dashboard';
?>
<?php require_once __DIR__ . '/../includes/header.php'; ?>
<?php require_once __DIR__ . '/../includes/navbar.php'; ?>

<div class="container py-4" style="max-width: 560px;">

    <!-- ── CARTE PROFIL ── -->
    <div class="card border-0 shadow-sm rounded-4 mb-3">
        <div class="card-body p-4">

            <p class="text-muted small fw-semibold text-uppercase mb-3" style="letter-spacing:.05em">
                Mon profil
            </p>

            <!-- Avatar + nom -->
            <div class="d-flex align-items-center gap-3 mb-4">
                <div class="rounded-circle d-flex align-items-center justify-content-center fw-bold text-white"
                     style="width:64px;height:64px;background:#1a1a2e;font-size:26px;flex-shrink:0">
                    <?= htmlspecialchars($initiale) ?>
                </div>
                <div>
                    <div class="fw-semibold" style="font-size:1.1rem">
                        <?= htmlspecialchars($_SESSION['name']) ?>
                    </div>
                    <span class="badge rounded-pill bg-opacity-10 <?= $roleBadge ?> mt-1">
                        <?= $roleLabel ?>
                    </span>
                </div>
            </div>

            <!-- Infos -->
            <?php
            $rows = [
                ['bi-person',        'Identifiant',   $_SESSION['username']],
                ['bi-id-card',       'Nom complet',   $_SESSION['name']],
                ['bi-shield-check',  'Rôle',          $roleLabel],
                ['bi-calendar3',     'Membre depuis', $since],
            ];
            foreach ($rows as [$icon, $label, $value]):
            ?>
                <div class="d-flex align-items-center gap-3 py-3"
                     style="border-bottom:1px solid var(--bs-border-color);">
                    <i class="bi <?= $icon ?> text-muted" style="width:18px;"></i>
                    <span class="text-muted small" style="width:130px;flex-shrink:0"><?= $label ?></span>
                    <span class="fw-medium small"><?= htmlspecialchars($value) ?></span>
                </div>
            <?php endforeach; ?>

        </div>
    </div>

    <!-- ── CARTE SÉCURITÉ ── -->
    <div class="card border-0 shadow-sm rounded-4">
        <div class="card-body p-4">

            <p class="text-muted small fw-semibold text-uppercase mb-3" style="letter-spacing:.05em">
                Sécurité
            </p>

            <div class="d-flex align-items-center justify-content-between flex-wrap gap-3">
                <div>
                    <div class="fw-medium small">Mot de passe</div>
                    <div class="text-muted" style="font-size:12px;margin-top:2px">
                        Dernière modification inconnue
                    </div>
                </div>
                <button class="btn btn-outline-secondary btn-sm d-flex align-items-center gap-2" disabled>
                    <i class="bi bi-lock"></i>
                    Changer le mot de passe
                    <span class="badge bg-warning text-dark" style="font-size:10px;">Bientôt</span>
                </button>
            </div>

        </div>
    </div>

</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>