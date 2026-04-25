<?php
// ── TRAITEMENT ──────────────────────────────────────────────
require_once 'includes/auth.php';
require_once 'includes/db.php';
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
<?php require_once 'includes/header.php'; ?>
<?php require_once 'includes/navbar.php'; ?>

<div class="container">
    <h1 class="mb-4">Mes tickets</h1>

    <?php if ($_SESSION['role'] === 'etudiant'): ?>
        <a href="create_ticket.php">+ Nouveau ticket</a>
    <?php endif; ?>

    <?php if (empty($tickets)): ?>
        <p>Aucun ticket pour l'instant.</p>
    <?php else: ?>
        <table border="1" cellpadding="6">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Titre</th>
                    <th>Catégorie</th>
                    <th>Priorité</th>
                    <th>Statut</th>
                    <th>Date</th>
                    <th>Auteur</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($tickets as $ticket): ?>
                    <tr>
                        <td><?= $ticket['id'] ?></td>
                        <td>
                            <a href="ticket.php?id=<?= $ticket['id'] ?>">
                                <?= htmlspecialchars($ticket['title']) ?>
                            </a>
                        </td>
                        <td><?= htmlspecialchars($ticket['category']) ?></td>
                        <td><?= htmlspecialchars($ticket['priority']) ?></td>
                        <td><?= htmlspecialchars($ticket['status']) ?></td>
                        <td><?= htmlspecialchars($ticket['created_at']) ?></td>
                        <td><?= htmlspecialchars($ticket['author']) ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>


</div>

<?php require_once 'includes/footer.php'; ?>