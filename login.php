<?php
require_once 'includes/auth.php';
startSession();

// Si déjà connecté, pas besoin d'être ici
if (isset($_SESSION['user_id'])) {
    header('Location: dashboard.php');
    exit();
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // Récupération et nettoyage des données du formulaire
    $username = trim($_POST['username'] ?? '');
    $password = trim($_POST['password'] ?? '');

    // Validation : champs vides ?
    if (empty($username) || empty($password)) {
        $error = 'Veuillez remplir tous les champs.';
    } else {
        // Lecture des utilisateurs depuis le JSON
        $users = json_decode(file_get_contents('data/users.json'), true);

        $found = null;
        foreach ($users as $user) {
            if ($user['username'] === $username && $user['password'] === $password) {
                $found = $user;
                break;
            }
        }

        if ($found) {
            // Connexion réussie : on remplit la session
            $_SESSION['user_id']   = $found['id'];
            $_SESSION['username']  = $found['username'];
            $_SESSION['role']      = $found['role'];
            $_SESSION['name']      = $found['name'];

            header('Location: dashboard.php');
            exit();
        } else {
            $error = 'Identifiant ou mot de passe incorrect.';
        }
    }
}
?>
<?php require_once 'includes/header.php'; ?>
<?php require_once 'includes/navbar.php'; ?>

<div class="container">
    <h1 class="mb-4">Connexion</h1>

    <?php if (!empty($error)): ?>
        <p style="color: red;"><?= htmlspecialchars($error) ?></p>
    <?php endif; ?>

    <form method="POST" action="login.php">
        <label>Identifiant :
            <input type="text" name="username" required>
        </label><br>
        <label>Mot de passe :
            <input type="password" name="password" required>
        </label><br>
        <button type="submit">Se connecter</button>
    </form>

</div>

<?php require_once 'includes/footer.php'; ?>