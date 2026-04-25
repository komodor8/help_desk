<?php
require_once __DIR__ . '/db.php';
/**
 * Démarre la session si elle n'est pas déjà active.
 * À appeler en tout premier dans chaque fichier PHP.
 */
function startSession() {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
}

/**
 * Redirige vers login.php si l'utilisateur n'est pas connecté.
 * À appeler en haut de chaque page protégée.
 */
function requireLogin() {
    startSession();
    if (!isset($_SESSION['user_id'])) {
        header('Location: login.php');
        exit();
    }
}

/**
 * Redirige vers login.php si l'utilisateur n'a pas le bon rôle.
 * Exemple : requireRole('tuteur') sur une page réservée au tuteur.
 */
function requireRole(string $role) {
    requireLogin();
    if ($_SESSION['role'] !== $role) {
        header('Location: dashboard.php');
        exit();
    }
}


function login(string $username, string $password): bool {
    // Recherche de l'utilisateur en base par son username
    $pdo  = getDB();
    $stmt = $pdo->prepare('SELECT * FROM users WHERE username = :username');
    $stmt->execute([':username' => $username]);
    $user = $stmt->fetch();

    // password_verify() compare le mot de passe saisi avec le hash en base
    if (!$user || !password_verify($password, $user['password'])) {
        return false;
    }

    // Connexion réussie : on remplit la session
    $_SESSION['user_id']  = $user['id'];
    $_SESSION['username'] = $user['username'];
    $_SESSION['role']     = $user['role'];
    $_SESSION['name']     = $user['name'];

    return true;
}

function logout(): void {
    startSession();
    $_SESSION = [];
    if (ini_get('session.use_cookies')) {
        $p = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000,
            $p['path'], $p['domain'], $p['secure'], $p['httponly']);
    }
    session_destroy();
}