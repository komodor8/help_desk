<?php
// ── TRAITEMENT ──────────────────────────────────────────────
require_once 'includes/auth.php';
require_once 'includes/db.php';
requireRole('etudiant');   // seuls les étudiants créent des tickets

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $title       = trim($_POST['title']       ?? '');
    $description = trim($_POST['description'] ?? '');
    $category    = trim($_POST['category']    ?? '');
    $priority    = trim($_POST['priority']    ?? '');

    // Validation
    if (empty($title))                                          $errors[] = 'Le titre est obligatoire.';
    if (empty($description))                                    $errors[] = 'La description est obligatoire.';
    if (!in_array($category, ['Cours', 'TD', 'TP']))            $errors[] = 'Catégorie invalide.';
    if (!in_array($priority, ['Basse', 'Moyenne', 'Haute']))    $errors[] = 'Priorité invalide.';

    if (empty($errors)) {
        $pdo = getDB();
        $stmt = $pdo->prepare('
            INSERT INTO tickets (user_id, author, title, description, category, priority)
            VALUES (:user_id, :author, :title, :description, :category, :priority)
        ');
        $stmt->execute([
            ':user_id'     => $_SESSION['user_id'],
            ':author'      => $_SESSION['name'],
            ':title'       => $title,
            ':description' => $description,
            ':category'    => $category,
            ':priority'    => $priority,
        ]);

        header('Location: tickets.php');
        exit();
    }
}
?>
<!-- ── AFFICHAGE ─────────────────────────────────────────── -->
<?php require_once 'includes/header.php'; ?>
<?php require_once 'includes/navbar.php'; ?>

<div class="container">
    <h1 class="mb-4">Créer tickets</h1>

    <?php if (!empty($errors)): ?>
        <ul style="color:red;">
            <?php foreach ($errors as $e): ?>
                <li><?= htmlspecialchars($e) ?></li>
            <?php endforeach; ?>
        </ul>
    <?php endif; ?>

    <form method="POST" action="create_ticket.php">

        <label>Titre :
            <input type="text" name="title" value="<?= htmlspecialchars($_POST['title'] ?? '') ?>" required>
        </label><br>

        <label>Description :
            <textarea name="description" required><?= htmlspecialchars($_POST['description'] ?? '') ?></textarea>
        </label><br>

        <label>Catégorie :
            <select name="category" required>
                <option value="">-- Choisir --</option>
                <?php foreach (['Cours', 'TD', 'TP'] as $cat): ?>
                    <option value="<?= $cat ?>"
                        <?= (($_POST['category'] ?? '') === $cat) ? 'selected' : '' ?>>
                        <?= $cat ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </label><br>

        <label>Priorité :
            <select name="priority" required>
                <option value="">-- Choisir --</option>
                <?php foreach (['Basse', 'Moyenne', 'Haute'] as $p): ?>
                    <option value="<?= $p ?>"
                        <?= (($_POST['priority'] ?? '') === $p) ? 'selected' : '' ?>>
                        <?= $p ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </label><br>

        <button type="submit">Envoyer le ticket</button>
    </form>
</div>

<?php require_once 'includes/footer.php'; ?>