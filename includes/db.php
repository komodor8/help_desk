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


// CREATE TABLE tickets (
//     id          SERIAL PRIMARY KEY,
//     user_id     INTEGER      NOT NULL,
//     author      VARCHAR(100) NOT NULL,
//     title       VARCHAR(255) NOT NULL,
//     description TEXT         NOT NULL,
//     category    VARCHAR(10)  NOT NULL CHECK (category IN ('Cours', 'TD', 'TP')),
//     priority    VARCHAR(10)  NOT NULL CHECK (priority IN ('Basse', 'Moyenne', 'Haute')),
//     status      VARCHAR(10)  NOT NULL DEFAULT 'Ouvert'
//                              CHECK (status IN ('Ouvert', 'En cours', 'Résolu')),
//     created_at  TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP
// );


// CREATE TABLE comments (
//     id         SERIAL PRIMARY KEY,
//     ticket_id  INTEGER      NOT NULL REFERENCES tickets(id) ON DELETE CASCADE, // si un ticket est supprimé, tous ses commentaires le sont automatiquement.
//     user_id    INTEGER      NOT NULL,
//     author     VARCHAR(100) NOT NULL,
//     message    TEXT         NOT NULL,
//     created_at TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP
// );

// CREATE TABLE users (
//     id         SERIAL PRIMARY KEY,
//     username   VARCHAR(100) NOT NULL UNIQUE,
//     password   VARCHAR(255) NOT NULL,
//     name       VARCHAR(100) NOT NULL,
//     role       VARCHAR(20)  NOT NULL CHECK (role IN ('etudiant', 'tuteur')),
//     created_at TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP
// );


// INSERT INTO users (username, password, name, role) VALUES
// (
//     'farid',
//     '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi',
//     'Farid le F',
//     'etudiant'
// ),
// (
//     'Rayan',
//     '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi',
//     'Rayan le R',
//     'etudiant'
// ),
// (
//     'Lylia',
//     '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi',
//     'Prof. ABROUK',
//     'tuteur'
// );