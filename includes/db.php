<?php

function getDB(): PDO {
    static $pdo = null;

    if ($pdo === null) {
        $host   = 'db';             // nom du service dans docker-compose
        $dbname = 'help_desk_db';
        $user   = 'farid';
        $pass   = 'farid123';
        $dsn    = "pgsql:host=$host;dbname=$dbname";

        try {
            $pdo = new PDO($dsn, $user, $pass, [
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            ]);
        } catch (PDOException $e) {
            die('Connexion impossible : ' . $e->getMessage());
        }
    }

    return $pdo;
}