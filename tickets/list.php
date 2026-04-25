<?php
// ── TRAITEMENT ──────────────────────────────────────────────
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/db.php';
requireLogin();

$pdo = getDB();

if ($_SESSION['role'] === 'tuteur') {
    // Le tuteur voit tout
    $stmt = $pdo->query('SELECT * FROM tickets ORDER BY created_at DESC');
} else {
    // L'étudiant voit seulement ses tickets
    $stmt = $pdo->prepare('SELECT * FROM tickets WHERE user_id = :uid ORDER BY created_at DESC');
    $stmt->execute([':uid' => $_SESSION['user_id']]);
}

$tickets = $stmt->fetchAll();
?>
<!-- ── AFFICHAGE ─────────────────────────────────────────── -->
<?php require_once __DIR__ . '/../includes/header.php'; ?>
<?php require_once __DIR__ . '/../includes/navbar.php'; ?>

<div class="container py-4" style="max-width: 760px;">

    <!-- En-tête -->
    <div class="d-flex align-items-center justify-content-between mb-4">
        <div>
            <h1 class="fw-semibold mb-0" style="font-size:1.35rem;">
                <?= $_SESSION['role'] === 'tuteur' ? 'Liste des tickets' : 'Mes tickets' ?>
            </h1>
            <p class="text-muted small mb-0">
                <?= count($tickets) ?> ticket<?= count($tickets) > 1 ? 's' : '' ?> au total
            </p>
        </div>

        <?php if ($_SESSION['role'] === 'etudiant'): ?>
            <a href="create.php" class="btn btn-dark px-4">
                <i class="bi bi-plus-lg me-1"></i>Nouveau ticket
            </a>
        <?php endif; ?>
    </div>

    <p><small>Les filtres arrivent bientôt</small></p>

    <!-- État vide -->
    <?php if (empty($tickets)): ?>
        <div class="text-center py-5 rounded-4"
             style="background:var(--bs-body-bg); border: 1.5px dashed var(--bs-border-color);">
            <i class="bi bi-inbox" style="font-size:2.5rem; color:var(--bs-secondary-color)"></i>
            <p class="text-muted mt-3 mb-0">Aucun ticket pour l'instant.</p>
            <?php if ($_SESSION['role'] === 'etudiant'): ?>
                <a href="create.php" class="btn btn-dark btn-sm mt-3">Créer mon premier ticket</a>
            <?php endif; ?>
        </div>

    <?php else: ?>

        <!-- Liste des tickets -->
        <?php foreach ($tickets as $ticket):

            // Couleurs catégorie
            $catClass = match($ticket['category']) {
                'TD'    => 'text-primary bg-primary',
                'TP'    => 'text-danger bg-danger',
                default => 'text-purple bg-purple',  // Cours
            };

            // Couleurs priorité
            [$priClass, $priIcon] = match($ticket['priority']) {
                'Haute'   => ['text-danger  bg-danger',  'bi-arrow-up-circle'],
                'Moyenne' => ['text-warning bg-warning', 'bi-dash-circle'],
                default   => ['text-success bg-success', 'bi-arrow-down-circle'],
            };

            // Couleurs statut
            $staClass = match($ticket['status']) {
                'Résolu'   => 'text-success bg-success',
                'En cours' => 'text-warning bg-warning',
                default    => 'text-info bg-info',
            };

            // Date formatée
            $date = (new DateTime($ticket['created_at']))->format('d/m/Y');
        ?>

        <a href="detail.php?id=<?= $ticket['id'] ?>"
           class="text-decoration-none d-flex align-items-center gap-3 p-3 mb-2 rounded-4 shadow-sm"
           style="background:var(--bs-body-bg); transition: box-shadow .18s, transform .12s;"
           onmouseover="this.style.transform='translateY(-1px)';this.style.boxShadow='0 4px 16px rgba(0,0,0,.1)'"
           onmouseout="this.style.transform='';this.style.boxShadow=''">

            <!-- ID -->
            <span class="text-muted small fw-semibold" style="min-width:32px;">
                #<?= $ticket['id'] ?>
            </span>

            <!-- Titre + auteur -->
            <div class="flex-fill overflow-hidden">
                <div class="fw-semibold text-truncate" style="color:var(--bs-body-color)">
                    <?= htmlspecialchars($ticket['title']) ?>
                </div>
                <div class="small text-muted">
                    <i class="bi bi-person me-1"></i><?= htmlspecialchars($ticket['author']) ?>
                </div>
            </div>

            <!-- Badges -->
            <span class="badge rounded-pill bg-opacity-10 <?= $catClass ?> d-none d-md-inline">
                <?= htmlspecialchars($ticket['category']) ?>
            </span>

            <span class="badge rounded-pill bg-opacity-10 <?= $priClass ?>">
                <i class="bi <?= $priIcon ?> me-1"></i><?= htmlspecialchars($ticket['priority']) ?>
            </span>

            <span class="badge rounded-pill bg-opacity-10 <?= $staClass ?>">
                <?= htmlspecialchars($ticket['status']) ?>
            </span>

            <!-- Date -->
            <span class="text-muted small d-none d-md-inline" style="white-space:nowrap">
                <?= $date ?>
            </span>

            <!-- Chevron -->
            <i class="bi bi-chevron-right text-muted"></i>

        </a>

        <?php endforeach; ?>

    <?php endif; ?>
    <small>La pagination arrive bientôt</small>

</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>