# Helpdesk — Mini application de gestion de tickets

Mini helpdesk en PHP réalisé dans le cadre du projet DAW (L3).

## Membres du groupe

- Farid LKHALDOUNI

## Choix techniques

| Technologie | Raison |
|---|---|
| **PHP 8.2** (sans framework) | Imposé par le sujet |
| **PostgreSQL 15 + Adminer** | Stockage relationnel et sa gestion |
| **PDO** | Accès sécurisé aux données (requêtes préparées) |
| **Docker + docker-compose** | Environnement reproductible, bonus conteneurisation |
| **Bootstrap 5.3** (CDN) | UI responsive rapide |
| **Bootstrap Icons** | Icônes légères et cohérentes |

## Prérequis

- [Docker Desktop](https://www.docker.com/products/docker-desktop/) installé
- Git installé (pour cloner)

## Installation et lancement

```bash
# 1. Cloner le projet
git clone <url-du-repo>
cd help_desk

# 2. Tout lancer d'un coup (conteneurs + base de données)
bash init.sh

# 3. Ouvrir l'application
open http://localhost:8000
```

## Accès

| Service | URL |
|---|---|
| Application | http://localhost:8000 |
| Adminer (BD) | http://localhost:8081 |

## Comptes de test

Tous les mots de passe sont : `password`

| Identifiant | Rôle |
|---|---|
| farid | Étudiant |
| Rayan | Étudiant |
| Lylia | Tuteur |

### Ou étape par étape

```bash
docker compose up -d
docker compose exec -T db psql -U farid -d help_desk_db < schema.sql
```

## Arrêter les conteneurs

```bash
docker compose down
```

## Structure du projet

```
help_desk/
├── Dockerfile
├── docker-compose.yml
├── schema.sql                # Structure BD + données de test
├── index.php                 # Redirection vers login ou dashboard
├── login.php                 # Page de connexion
├── logout.php                # Déconnexion + nettoyage session
├── includes/
│   ├── auth.php              # Sessions, login/logout, CSRF
│   ├── db.php                # Connexion PDO PostgreSQL
│   ├── header.php            # <head> avec Bootstrap CDN
│   ├── navbar.php            # Barre de navigation
│   └── footer.php            # Pied de page + scripts
└── tickets/
    ├── dashboard.php         # Profil utilisateur
    ├── list.php              # Liste des tickets (filtrée par rôle)
    ├── create.php            # Création d'un ticket (étudiant)
    └── detail.php            # Détail + commentaires + changement statut
```

## Perspectives d'Évolution

En raison des contraintes de temps, je me suis concentré sur la robustesse du cœur de l'application (sécurité, base de données, logique métier). Cependant, voici les fonctionnalités prévues qui constituent la suite logique du développement :

* **Filtres et tri avancés :** Ajout de filtres sur la page des tickets (par statut, catégorie ou priorité) pour faciliter la gestion et la recherche, particulièrement pour le rôle Tuteur.
* **Pagination :** Implémentation d'un système de pagination sur la liste des tickets afin d'optimiser le temps de chargement et l'expérience utilisateur lorsque le volume de données augmentera.
* **Création de compte (Inscription) :** Développement d'une page `register.php` sécurisée permettant à de nouveaux étudiants de s'inscrire de manière autonome sur la plateforme.
* **Modification du mot de passe :** Activation du bouton actuellement présent sur le tableau de bord pour permettre aux utilisateurs de mettre à jour leur mot de passe et de gérer la sécurité de leur propre compte.

---

## La logique métier du sujet

### Fonctionnalités accessibles sans être connecté

#### `login.php`

1. **Déjà connecté ?** `[SÉCURITÉ]`
   Vérifie si `$_SESSION['user_id']` existe → si oui, redirige vers `dashboard.php`.
2. **Méthode GET**
   Affiche simplement le formulaire HTML de connexion.
3. **Méthode POST — validation** `[VALIDATION]`
   Vérifie que *username* et *password* ne sont pas vides. Si vide → réaffiche le formulaire avec message d'erreur.
4. **Requête SQL** `[SÉCURITÉ]`
   `SELECT * FROM users WHERE username = :username` — requête préparée PDO, jamais de concaténation directe.
5. **Vérification mot de passe** `[SÉCURITÉ]`
   `password_verify($input, $hash)` — compare le mot de passe saisi avec le hash bcrypt en base.
6. **Succès → session + redirect**
   Enregistre `user_id`, `username`, `name`, `role` dans `$_SESSION`. Redirige vers `dashboard.php`.
7. **Échec → message générique** `[SÉCURITÉ]`
   Affiche "Identifiant ou mot de passe incorrect" — sans préciser lequel. Évite d'informer un attaquant.

#### `logout.php`

1. **startSession()**
   Démarre la session si elle ne l'est pas déjà — nécessaire pour pouvoir la manipuler.
2. **Vide $_SESSION** `[SÉCURITÉ]`
   `$_SESSION = []` — supprime toutes les variables de session en mémoire.
3. **Supprime le cookie** `[SÉCURITÉ]`
   `setcookie()` avec une date passée → force le navigateur à supprimer le cookie de session.
4. **session_destroy()** `[SÉCURITÉ]`
   Détruit la session côté serveur. Sans cette étape, la session reste active sur le serveur.
5. **Redirect → login.php**
   `header('Location: /login.php')` + `exit()` — le `exit()` est obligatoire pour stopper l'exécution du script.

---

### Fonctionnalités réutilisables

#### `auth.php`

1. **startSession()**
   Lance `session_start()` seulement si `PHP_SESSION_NONE` — évite l'erreur "session already started".
2. **requireLogin()** `[SÉCURITÉ]`
   Vérifie `isset($_SESSION['user_id'])`. Si absent → `header(Location)` + `exit()`.
3. **requireRole($role)** `[SÉCURITÉ]` `[RÔLE]`
   Appelle `requireLogin()` puis compare `$_SESSION['role']`. Rôle incorrect → redirect `dashboard.php`.
4. **login($user, $pass)** `[SÉCURITÉ]`
   SELECT par *username* → `password_verify()` → remplit `$_SESSION` si ok. Retourne `true` ou `false`.
5. **logout()** `[SÉCURITÉ]`
   Vide `$_SESSION`, supprime le cookie, `session_destroy()`.

#### `db.php`

1. **getDB() — singleton**
   Variable statique `$pdo = null`. Si null → crée la connexion PDO. Sinon → retourne la connexion existante. Une seule connexion par requête HTTP.
2. **DSN PostgreSQL**
   `pgsql:host=db;dbname=help_desk_db` — `host=db` car c'est le nom du service Docker, pas localhost.
3. **Options PDO**
   `ERRMODE_EXCEPTION` : les erreurs SQL lèvent une exception au lieu d'échouer silencieusement. `FETCH_ASSOC` : retourne des tableaux associatifs.
4. **Requêtes préparées partout** `[SÉCURITÉ]`
   Jamais de concaténation de variables dans le SQL. Toujours `prepare()` + `execute([':param' => $val])`. Protège contre les injections SQL.

---

### Les fonctionnalités liées au ticket

#### `dashboard.php`

1. **requireLogin()** `[SÉCURITÉ]`
   Si pas de session active → redirige vers `login.php`. Premier appel obligatoire de toute page protégée.
2. **Requête SQL user**
   `SELECT * FROM users WHERE id = :id` — récupère les infos complètes depuis la base, plus fiable que la session seule.
3. **Affichage profil** `[XSS]`
   Nom, username, rôle, date de création. Utilisation de `htmlspecialchars()` sur chaque variable affichée.
4. **Bouton mot de passe**
   Désactivé (`disabled`) avec badge "Bientôt" — fonctionnalité non implémentée clairement signalée.

#### `list.php`

1. **requireLogin()** `[SÉCURITÉ]`
   Vérifie la session. Pas connecté → `login.php`.
2. **Filtre par rôle** `[RÔLE]`
   * Tuteur : `SELECT * FROM tickets`
   * Étudiant : `SELECT * WHERE user_id = :uid`
   Le filtre s'applique strictement côté serveur, pas côté client.
3. **Affichage liste** `[XSS]`
   Boucle `foreach` sur `$tickets`. Utilisation de `htmlspecialchars()` sur `title`, `author`, `category`, `priority`, `status`.
4. **Bouton création** `[RÔLE]`
   Lien vers `create.php` visible uniquement si `$_SESSION['role'] === 'etudiant'`.

#### `create.php`

1. **requireRole('etudiant')** `[SÉCURITÉ]` `[RÔLE]`
   Appelle `requireLogin()` puis vérifie que le rôle est bien "etudiant". Un tuteur est immédiatement redirigé vers le dashboard.
2. **Méthode GET**
   Affiche le formulaire vide.
3. **Méthode POST — validation** `[VALIDATION]`
   Vérifie : titre non vide, description non vide, catégorie dans `[Cours, TD, TP]`, priorité dans `[Basse, Moyenne, Haute]`. Validations strictement côté serveur — on ne fait jamais confiance aux attributs HTML.
4. **Erreurs → réaffiche formulaire** `[XSS]`
   Les champs gardent leurs valeurs saisies via `$_POST['field'] ?? ''`. Utilisation de `htmlspecialchars()` sur chaque valeur réaffichée pour éviter l'injection HTML.
5. **Succès → INSERT + redirect**
   Requête préparée PDO `INSERT INTO tickets`. Redirection vers `tickets.php` — utilisation du pattern **POST/Redirect/GET** pour éviter la double soumission en cas de rafraîchissement (F5).

#### `detail.php`

1. **requireLogin()** `[SÉCURITÉ]`
   Vérifie la présence d'une session active.
2. **Validation de l'ID** `[SÉCURITÉ]` `[VALIDATION]`
   `isset($_GET['id'])` + `ctype_digit()` → vérifie que l'ID existe dans l'URL et est bien un entier. Sinon, redirection.
3. **Ticket existe ?** `[SÉCURITÉ]`
   `SELECT * FROM tickets WHERE id = :id`. Si `fetch()` retourne `false` → redirection vers `tickets.php`.
4. **Contrôle d'accès IDOR** `[SÉCURITÉ]` `[RÔLE]`
   Si rôle étudiant ET `ticket.user_id ≠ session.user_id` → redirection. Empêche un étudiant de lire les tickets d'autrui en changeant l'ID dans l'URL.
5. **POST update_status** `[RÔLE]` `[VALIDATION]`
   Vérifie rôle tuteur + valeur comprise dans `[Ouvert, En cours, Résolu]` → `UPDATE tickets SET status`. Redirection (POST/Redirect/GET).
6. **POST add_comment** `[VALIDATION]`
   Vérifie message non vide → `INSERT INTO comments`. Redirection pour éviter doublon au F5.
7. **Affichage** `[XSS]`
   `htmlspecialchars()` sur tous les champs affichés. Utilisation de `nl2br()` *après* `htmlspecialchars()` sur la description et les messages pour conserver les sauts de ligne sans faille de sécurité.
