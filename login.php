<?php
require_once 'includes/auth.php';
startSession();

if (isset($_SESSION['user_id'])) {
    header('Location: /tickets/dashboard.php');
    exit();
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    $username = trim($_POST['username'] ?? '');
    $password = trim($_POST['password'] ?? '');

    if (empty($username) || empty($password)) {
        $error = 'Veuillez remplir tous les champs.';
    } elseif (!login($username, $password)) {
        // On ne précise pas si c'est l'username ou le mot de passe qui est faux
        // C'est voulu : éviter de donner des infos à un attaquant
        $error = 'Identifiant ou mot de passe incorrect.';
    } else {
        header('Location: /tickets/dashboard.php');
        exit();
    }
}
?>
<?php require_once __DIR__ . '/includes/header.php'; ?>

<div class="container mt-5">
    <div class="row justify-content-center">
        <div class="col-md-4">
            <h2 class="mb-4 text-center">Connexion</h2>

            <?php if (!empty($error)): ?>
                <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
            <?php endif; ?>

            <form method="POST" action="/login.php">
                <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">
                <div class="mb-3">
                    <label class="form-label">Identifiant</label>
                    <input type="text" name="username" class="form-control"
                           value="<?= htmlspecialchars($_POST['username'] ?? '') ?>" required>
                </div>
                <div class="mb-3">
                    <label class="form-label">Mot de passe</label>
                    <input type="password" name="password" class="form-control" required>
                </div>
                <button type="submit" class="btn btn-dark w-100">Se connecter</button>
            </form>
            <hr class="my-4">

            <div class="text-center">
                <button class="btn btn-outline-secondary w-100" disabled>
                    Création de compte (Arrive bientôt)
                </button>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>