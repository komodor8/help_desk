<?php
// ── TRAITEMENT ──────────────────────────────────────────────
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/db.php';
requireLogin();

// 1. Vérifier que l'ID existe dans l'URL et qu'il est bien un entier
if (!isset($_GET['id']) || !ctype_digit($_GET['id'])) {
    header('Location: tickets.php');
    exit();
}

$id  = (int) $_GET['id'];
$pdo = getDB();

// 2. Récupérer le ticket
$stmt = $pdo->prepare('SELECT * FROM tickets WHERE id = :id');
$stmt->execute([':id' => $id]);
$ticket = $stmt->fetch();

// 3. Le ticket existe ?
if (!$ticket) {
    header('Location: tickets.php');
    exit();
}

// 4. Contrôle d'accès : un étudiant ne peut voir QUE ses tickets
if ($_SESSION['role'] === 'etudiant' && $ticket['user_id'] !== $_SESSION['user_id']) {
    header('Location: tickets.php');
    exit();
}

// 5. Traitement du changement de statut (tuteur uniquement)
$statusError = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST'
    && isset($_POST['action'])
    && $_POST['action'] === 'update_status'
    && $_SESSION['role'] === 'tuteur'
) {
    $newStatus = $_POST['status'] ?? '';

    if (!in_array($newStatus, ['Ouvert', 'En cours', 'Résolu'])) {
        $statusError = 'Statut invalide.';
    } else {
        $stmt = $pdo->prepare('UPDATE tickets SET status = :status WHERE id = :id');
        $stmt->execute([':status' => $newStatus, ':id' => $id]);

        // Rechargement de la page pour voir le nouveau statut
        header("Location: detail.php?id=$id");
        exit();
    }
}

// 6. Récupérer les commentaires
$stmt = $pdo->prepare('SELECT * FROM comments WHERE ticket_id = :tid ORDER BY created_at ASC');
$stmt->execute([':tid' => $id]);
$comments = $stmt->fetchAll();

// 7. Traitement de l'ajout de commentaire
$commentError = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST'
    && isset($_POST['action'])
    && $_POST['action'] === 'add_comment'
) {
    $message = trim($_POST['message'] ?? '');

    if (empty($message)) {
        $commentError = 'Le commentaire ne peut pas être vide.';
    } else {
        $stmt = $pdo->prepare('
            INSERT INTO comments (ticket_id, user_id, author, message)
            VALUES (:ticket_id, :user_id, :author, :message)
        ');
        $stmt->execute([
            ':ticket_id' => $id,
            ':user_id'   => $_SESSION['user_id'],
            ':author'    => $_SESSION['name'],
            ':message'   => $message,
        ]);

        // Rechargement pour éviter la resoumission du formulaire (F5)
        header("Location: detail.php?id=$id");
        exit();
    }
}
$pageTitle = 'Dashboard';
?>
<!-- ── AFFICHAGE ─────────────────────────────────────────── -->
<?php require_once __DIR__ . '/../includes/header.php'; ?>
<?php require_once __DIR__ . '/../includes/navbar.php'; ?>

<div class="container py-4" style="max-width: 760px;">

    <!-- Breadcrumb -->
    <a href="list.php" class="text-muted text-decoration-none small">
        <i class="bi bi-arrow-left me-1"></i>Retour à la liste
    </a>

    <!-- ── CARTE PRINCIPALE DU TICKET ── -->
    <div class="card border-0 shadow-sm rounded-4 mt-3 mb-3">
        <div class="card-body p-4">

            <!-- Titre + numéro -->
            <h2 class="fw-semibold mb-1" style="font-size:1.25rem;">
                <?= htmlspecialchars($ticket['title']) ?>
            </h2>
            <p class="text-muted small mb-3">
                Ticket #<?= $ticket['id'] ?> — ouvert par
                <strong><?= htmlspecialchars($ticket['author']) ?></strong>
            </p>

            <!-- Badges meta -->
            <div class="d-flex flex-wrap align-items-center gap-2 pb-3 mb-3"
                 style="border-bottom: 1px solid var(--bs-border-color);">

                <?php
                // Badge catégorie
                echo '<span class="badge rounded-pill bg-primary bg-opacity-10 text-primary">'
                    . htmlspecialchars($ticket['category']) . '</span>';

                // Badge priorité
                $priorityClass = match($ticket['priority']) {
                    'Haute'   => 'bg-danger bg-opacity-10 text-danger',
                    'Moyenne' => 'bg-warning bg-opacity-10 text-warning',
                    default   => 'bg-success bg-opacity-10 text-success',
                };
                $priorityIcon = match($ticket['priority']) {
                    'Haute'   => 'bi-arrow-up-circle',
                    'Moyenne' => 'bi-dash-circle',
                    default   => 'bi-arrow-down-circle',
                };
                echo '<span class="badge rounded-pill ' . $priorityClass . '">'
                    . '<i class="bi ' . $priorityIcon . ' me-1"></i>'
                    . htmlspecialchars($ticket['priority']) . '</span>';

                // Badge statut
                $statusClass = match($ticket['status']) {
                    'Résolu'   => 'bg-success bg-opacity-10 text-success',
                    'En cours' => 'bg-warning bg-opacity-10 text-warning',
                    default    => 'bg-info bg-opacity-10 text-info',
                };
                echo '<span class="badge rounded-pill ' . $statusClass . '">'
                    . htmlspecialchars($ticket['status']) . '</span>';
                ?>

                <span class="text-muted small ms-auto">
                    <i class="bi bi-clock me-1"></i>
                    <?= htmlspecialchars((new DateTime($ticket['created_at']))->format('d/m/Y')) ?>
                </span>
            </div>

            <!-- Description -->
            <p class="text-muted small fw-semibold text-uppercase mb-2" style="letter-spacing:.05em">
                Description
            </p>
            <div class="rounded-3 p-3" style="background:var(--bs-secondary-bg); line-height:1.7;">
                <?= nl2br(htmlspecialchars($ticket['description'])) ?>
            </div>
        </div>
    </div>

    <!-- ── BLOC STATUT (tuteur uniquement) ── -->
    <?php if ($_SESSION['role'] === 'tuteur'): ?>
    <div class="card border-0 shadow-sm rounded-4 mb-3">
        <div class="card-body p-4">

            <p class="text-muted small fw-semibold text-uppercase mb-3" style="letter-spacing:.05em">
                <i class="bi bi-pencil-square me-1"></i>Changer le statut
            </p>

            <?php if (!empty($statusError)): ?>
                <div class="alert alert-danger py-2">
                    <?= htmlspecialchars($statusError) ?>
                </div>
            <?php endif; ?>

            <form method="POST" action="detail.php?id=<?= $id ?>"
                  class="d-flex align-items-center gap-3 flex-wrap">
                <input type="hidden" name="action" value="update_status">

                <select name="status" class="form-select w-auto">
                    <?php foreach (['Ouvert', 'En cours', 'Résolu'] as $s): ?>
                        <option value="<?= $s ?>"
                            <?= $ticket['status'] === $s ? 'selected' : '' ?>>
                            <?= $s ?>
                        </option>
                    <?php endforeach; ?>
                </select>

                <button type="submit" class="btn btn-dark px-4">
                    <i class="bi bi-check-lg me-1"></i>Mettre à jour
                </button>
            </form>
        </div>
    </div>
    <?php endif; ?>

    <!-- ── COMMENTAIRES ── -->
    <div class="card border-0 shadow-sm rounded-4">
        <div class="card-body p-4">

            <p class="text-muted small fw-semibold text-uppercase mb-3" style="letter-spacing:.05em">
                <i class="bi bi-chat-left-text me-1"></i>
                Commentaires (<?= count($comments) ?>)
            </p>

            <!-- Liste des commentaires -->
            <?php if (empty($comments)): ?>
                <p class="text-muted small fst-italic">Aucun commentaire pour l'instant.</p>
            <?php else: ?>
                <div class="d-flex flex-column gap-2 mb-4">
                    <?php foreach ($comments as $comment): ?>
                        <div class="rounded-3 p-3" style="background:var(--bs-secondary-bg)">
                            <div class="d-flex align-items-center gap-2 mb-1">
                                <i class="bi bi-person-circle text-muted"></i>
                                <span class="fw-semibold small">
                                    <?= htmlspecialchars($comment['author']) ?>
                                </span>
                                <span class="text-muted small ms-auto">
                                    <?= htmlspecialchars((new DateTime($comment['created_at']))->format('d/m/Y')) ?>


                                </span>
                            </div>
                            <p class="mb-0 small" style="line-height:1.6">
                                <?= nl2br(htmlspecialchars($comment['message'])) ?>
                            </p>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>

            <!-- Séparateur -->
            <div style="border-top:1px solid var(--bs-border-color); padding-top:20px;">

                <p class="text-muted small fw-semibold text-uppercase mb-2" style="letter-spacing:.05em">
                    Ajouter un commentaire
                </p>

                <?php if (!empty($commentError)): ?>
                    <div class="alert alert-danger py-2 mb-2">
                        <?= htmlspecialchars($commentError) ?>
                    </div>
                <?php endif; ?>

                <form method="POST" action="detail.php?id=<?= $id ?>">
                    <input type="hidden" name="action" value="add_comment">
                    <textarea name="message" class="form-control mb-2" rows="3"
                              placeholder="Écris ton commentaire ici…" required></textarea>
                    <button type="submit" class="btn btn-dark w-100">
                        <i class="bi bi-send me-2"></i>Envoyer le commentaire
                    </button>
                </form>
            </div>

        </div>
    </div>

</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>