<?php
// ── TRAITEMENT ──────────────────────────────────────────────
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/db.php';
requireRole('etudiant');   // seuls les étudiants créent des tickets

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();

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

        header('Location: list.php');
        exit();
    }
}
?>
<!-- ── AFFICHAGE ─────────────────────────────────────────── -->
<?php require_once __DIR__ . '/../includes/header.php'; ?>
<?php require_once __DIR__ . '/../includes/navbar.php'; ?>

<div class="container">
    <h1 class="mb-4">Créer un ticket</h1>

    <?php if (!empty($errors)): ?>
        <ul style="color:red;">
            <?php foreach ($errors as $e): ?>
                <li><?= htmlspecialchars($e) ?></li>
            <?php endforeach; ?>
        </ul>
    <?php endif; ?>

    <form method="POST" action="create.php">
        <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">

        <!-- Titre -->
        <div class="mb-3">
            <label class="form-label">
                <i class="bi bi-type me-1"></i>Titre <span class="text-danger">*</span>
            </label>
            <input type="text" name="title" class="form-control"
                placeholder="Ex : Erreur sur l'exercice 3 du TD2"
                maxlength="120"
                value="<?= htmlspecialchars($_POST['title'] ?? '') ?>" required>
        </div>

        <!-- Description -->
        <div class="mb-3">
            <label class="form-label">
                <i class="bi bi-text-paragraph me-1"></i>Description <span class="text-danger">*</span>
            </label>
            <textarea name="description" class="form-control" rows="5"
                    placeholder="Décris le problème, ce que tu as essayé, et le contexte..."
                    required><?= htmlspecialchars($_POST['description'] ?? '') ?></textarea>
        </div>

        <!-- Catégorie — radio cards -->
        <div class="mb-3">
            <label class="form-label d-block">
                <i class="bi bi-folder me-1"></i>Catégorie <span class="text-danger">*</span>
            </label>
            <div class="d-flex gap-2">
                <?php foreach (['Cours' => 'bi-book', 'TD' => 'bi-pencil-square', 'TP' => 'bi-cpu'] as $cat => $icon): ?>
                    <div class="flex-fill">
                        <input type="radio" class="btn-check" name="category"
                            id="cat-<?= strtolower($cat) ?>" value="<?= $cat ?>"
                            <?= (($_POST['category'] ?? '') === $cat) ? 'checked' : '' ?> required>
                        <label class="btn btn-outline-secondary w-100 d-flex flex-column align-items-center py-2"
                            for="cat-<?= strtolower($cat) ?>">
                            <i class="bi <?= $icon ?> fs-5 mb-1"></i>
                            <?= $cat ?>
                        </label>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>

        <!-- Priorité — radio cards colorées -->
        <div class="mb-4">
            <label class="form-label d-block">
                <i class="bi bi-flag me-1"></i>Priorité <span class="text-danger">*</span>
            </label>
            <div class="d-flex gap-2">
                <?php
                $priorities = [
                    'Basse'   => ['icon' => 'bi-arrow-down-circle', 'color' => 'success'],
                    'Moyenne' => ['icon' => 'bi-dash-circle',        'color' => 'warning'],
                    'Haute'   => ['icon' => 'bi-arrow-up-circle',    'color' => 'danger'],
                ];
                foreach ($priorities as $p => $cfg): ?>
                    <div class="flex-fill">
                        <input type="radio" class="btn-check" name="priority"
                            id="p-<?= strtolower($p) ?>" value="<?= $p ?>"
                            <?= (($_POST['priority'] ?? '') === $p) ? 'checked' : '' ?> required>
                        <label class="btn btn-outline-<?= $cfg['color'] ?> w-100 d-flex flex-column align-items-center py-2"
                            for="p-<?= strtolower($p) ?>">
                            <i class="bi <?= $cfg['icon'] ?> fs-5 mb-1"></i>
                            <?= $p ?>
                        </label>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>

        <button type="submit" class="btn btn-dark w-100 py-2">
            <i class="bi bi-send me-2"></i>Envoyer le ticket
        </button>

    </form>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>