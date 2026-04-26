-- 1. Table des utilisateurs
CREATE TABLE users (
    id         SERIAL PRIMARY KEY,
    username   VARCHAR(100) NOT NULL UNIQUE,
    password   VARCHAR(255) NOT NULL,
    name       VARCHAR(100) NOT NULL,
    role       VARCHAR(20)  NOT NULL CHECK (role IN ('etudiant', 'tuteur')),
    created_at TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP
);

-- 2. Table des tickets
CREATE TABLE tickets (
    id          SERIAL PRIMARY KEY,
    user_id     INTEGER      NOT NULL REFERENCES users(id),
    author      VARCHAR(100) NOT NULL,
    title       VARCHAR(255) NOT NULL,
    description TEXT         NOT NULL,
    category    VARCHAR(10)  NOT NULL CHECK (category IN ('Cours', 'TD', 'TP')),
    priority    VARCHAR(10)  NOT NULL CHECK (priority IN ('Basse', 'Moyenne', 'Haute')),
    status      VARCHAR(10)  NOT NULL DEFAULT 'Ouvert'
                             CHECK (status IN ('Ouvert', 'En cours', 'Résolu')),
    created_at  TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP
);

-- 3. Table des commentaires
CREATE TABLE comments (
    id         SERIAL PRIMARY KEY,
    ticket_id  INTEGER      NOT NULL REFERENCES tickets(id) ON DELETE CASCADE,
    user_id    INTEGER      NOT NULL REFERENCES users(id),
    author     VARCHAR(100) NOT NULL,
    message    TEXT         NOT NULL,
    created_at TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP
);

-- ============================================================
-- Données de test
-- Tous les mots de passe sont : password
-- (hash généré avec password_hash('password', PASSWORD_BCRYPT))
-- ============================================================
INSERT INTO users (username, password, name, role) VALUES
(
    'farid',
    '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi',
    'Farid',
    'etudiant'
),
(
    'Rayan',
    '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi',
    'Rayan',
    'etudiant'
),
(
    'Lylia',
    '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi',
    'Prof. ABROUK',
    'tuteur'
);
