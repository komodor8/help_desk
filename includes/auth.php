<?php

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