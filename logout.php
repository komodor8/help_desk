<?php
require_once 'includes/auth.php';
startSession();

// Vider toutes les variables de session
$_SESSION = [];

// Détruire le cookie de session côté navigateur
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}

// Détruire la session côté serveur
session_destroy();

header('Location: login.php');
exit();