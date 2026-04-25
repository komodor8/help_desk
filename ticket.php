<?php
// ── TRAITEMENT ──────────────────────────────────────────────
require_once 'includes/auth.php';
require_once 'includes/db.php';
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
        header("Location: ticket.php?id=$id");
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
        header("Location: ticket.php?id=$id");
        exit();
    }
}
?>
<!-- ── AFFICHAGE ─────────────────────────────────────────── -->
<?php require_once 'includes/header.php'; ?>
<?php require_once 'includes/navbar.php'; ?>

<div class="container">
    <h1 class="mb-4">Mes tickets</h1>

    <!-- Entête du ticket -->
    <h1><?= htmlspecialchars($ticket['title']) ?></h1>



    <p>
        <strong>Auteur :</strong> <?= htmlspecialchars($ticket['author']) ?> |
        <strong>Catégorie :</strong> <?= htmlspecialchars($ticket['category']) ?> |
        <strong>Priorité :</strong> <?= htmlspecialchars($ticket['priority']) ?> |
        <strong>Statut :</strong> <?= htmlspecialchars($ticket['status']) ?> |
        <strong>Date :</strong> <?= htmlspecialchars($ticket['created_at']) ?>
    </p>

    <h2>Description</h2>
    <p><?= nl2br(htmlspecialchars($ticket['description'])) ?></p>

    <!-- Bloc de changement de statut — visible seulement pour le tuteur -->
    <?php if ($_SESSION['role'] === 'tuteur'): ?>
        <hr>
        <h2>Changer le statut</h2>

        <?php if (!empty($statusError)): ?>
            <p style="color:red;"><?= htmlspecialchars($statusError) ?></p>
        <?php endif; ?>

        <form method="POST" action="ticket.php?id=<?= $id ?>">
            <input type="hidden" name="action" value="update_status">

            <select name="status">
                <?php foreach (['Ouvert', 'En cours', 'Résolu'] as $s): ?>
                    <option value="<?= $s ?>"
                        <?= $ticket['status'] === $s ? 'selected' : '' ?>>
                        <?= $s ?>
                    </option>
                <?php endforeach; ?>
            </select>

            <button type="submit">Mettre à jour</button>
        </form>
    <?php endif; ?>

    <hr>
    <h2>Commentaires (<?= count($comments) ?>)</h2>

    <?php if (empty($comments)): ?>
        <p>Aucun commentaire pour l'instant.</p>
    <?php else: ?>
        <?php foreach ($comments as $comment): ?>
            <div style="border:1px solid #ccc; padding:8px; margin:8px 0;">
                <strong><?= htmlspecialchars($comment['author']) ?></strong>
                <em><?= htmlspecialchars($comment['created_at']) ?></em>
                <p><?= nl2br(htmlspecialchars($comment['message'])) ?></p>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>

    <!-- Formulaire commentaire — on le branchera en Phase 4 -->
    <?php if (!empty($commentError)): ?>
        <p style="color:red;"><?= htmlspecialchars($commentError) ?></p>
    <?php endif; ?>

    <form method="POST" action="ticket.php?id=<?= $id ?>">
        <input type="hidden" name="action" value="add_comment">

        <label>Ajouter un commentaire :
            <textarea name="message" rows="4" cols="50" required></textarea>
        </label><br>

        <button type="submit">Envoyer</button>
    </form>

</div>

<?php require_once 'includes/footer.php'; ?>