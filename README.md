# help_desk


### `login.php`
1. **Déjà connecté ?** `[SÉCURITÉ]`
   Vérifie si `$_SESSION['user_id']` existe → si oui, redirige vers `dashboard.php`
2. **Méthode GET**
   Affiche simplement le formulaire HTML de connexion
3. **Méthode POST — validation** `[VALIDATION]`
   Vérifie que username et password ne sont pas vides. Si vide → réaffiche le formulaire avec message d'erreur.
4. **Requête SQL** `[SÉCURITÉ]`
   `SELECT * FROM users WHERE username = :username` — requête préparée PDO, jamais de concaténation directe.
5. **Vérification mot de passe** `[SÉCURITÉ]`
   `password_verify($input, $hash)` — compare le mot de passe saisi avec le hash bcrypt en base.
6. **Succès → session + redirect**
   Enregistre user_id, username, name, role dans `$_SESSION`. Redirige vers `dashboard.php`.
7. **Échec → message générique** `[SÉCURITÉ]`
   Affiche "identifiant ou mot de passe incorrect" — sans préciser lequel. Évite d'informer un attaquant.

---

### `logout.php`
1. **startSession()**
   Démarre la session si elle ne l'est pas déjà — nécessaire pour pouvoir la manipuler.
2. **Vide $_SESSION** `[SÉCURITÉ]`
   `$_SESSION = []` — supprime toutes les variables de session en mémoire.
3. **Supprime le cookie** `[SÉCURITÉ]`
   `setcookie()` avec une date passée → force le navigateur à supprimer le cookie de session.
4. **session_destroy()** `[SÉCURITÉ]`
   Détruit la session côté serveur. Sans cette étape, la session reste active sur le serveur.
5. **Redirect → login.php**
   `header('Location: /login.php')` + `exit()` — l'exit() est obligatoire pour stopper l'exécution.

---

### `dashboard.php`
1. **requireLogin()** `[SÉCURITÉ]`
   Si pas de session active → redirige vers `login.php`. Premier appel obligatoire de toute page protégée.
2. **Requête SQL user**
   `SELECT * FROM users WHERE id = :id` — récupère les infos complètes depuis la base, plus fiable que la session seule.
3. **Affichage profil** `[XSS]`
   Nom, username, rôle, date de création. `htmlspecialchars()` sur chaque variable affichée.
4. **Bouton mot de passe**
   Désactivé (`disabled`) avec badge "Bientôt" — fonctionnalité non implémentée clairement signalée.

---

### `list.php`
1. **requireLogin()** `[SÉCURITÉ]`
   Vérifie la session. Pas connecté → `login.php`.
2. **Filtre par rôle** `[RÔLE]`
   Tuteur : `SELECT * FROM tickets`. Étudiant : `SELECT * WHERE user_id = :uid`. Le filtre est côté serveur, pas côté client.
3. **Affichage liste** `[XSS]`
   `foreach` sur `$tickets`. `htmlspecialchars()` sur title, author, category, priority, status.
4. **Bouton création** `[RÔLE]`
   Lien vers `create.php` visible uniquement si `$_SESSION['role'] === 'etudiant'`.

---

### `create.php`
1. **requireRole('etudiant')** `[SÉCURITÉ]` `[RÔLE]`
   Appelle `requireLogin()` puis vérifie que role === etudiant. Un tuteur est redirigé vers dashboard.
2. **Méthode GET**
   Affiche le formulaire vide.
3. **Méthode POST — validation** `[VALIDATION]`
   Vérifie : title non vide, description non vide, category dans [Cours/TD/TP], priority dans [Basse/Moyenne/Haute]. Côté serveur — pas de confiance aux valeurs HTML.
4. **Erreurs → réaffiche formulaire** `[XSS]`
   Les champs gardent leurs valeurs via `$_POST['field'] ?? ''`. `htmlspecialchars()` sur chaque valeur réaffichée.
5. **Succès → INSERT + redirect**
   Requête préparée PDO `INSERT INTO tickets`. Redirect vers `tickets.php` — pattern POST/Redirect/GET pour éviter la double soumission (F5).

---

### `detail.php`
1. **requireLogin()** `[SÉCURITÉ]`
   Vérifie la session active.
2. **Validation de l'ID** `[SÉCURITÉ]` `[VALIDATION]`
   `isset($_GET['id'])` + `ctype_digit()` → vérifie que l'ID existe et est bien un entier. Sinon redirect.
3. **Ticket existe ?** `[SÉCURITÉ]`
   `SELECT * FROM tickets WHERE id = :id`. Si `fetch()` retourne false → redirect `tickets.php`.
4. **Contrôle d'accès IDOR** `[SÉCURITÉ]` `[RÔLE]`
   Si rôle étudiant ET ticket.user_id ≠ session.user_id → redirect. Empêche de lire les tickets d'autrui en changeant l'URL.
5. **POST update_status** `[RÔLE]` `[VALIDATION]`
   Vérifie rôle tuteur + valeur dans [Ouvert/En cours/Résolu] → `UPDATE tickets SET status`. Redirect (POST/Redirect/GET).
6. **POST add_comment** `[VALIDATION]`
   Vérifie message non vide → `INSERT INTO comments`. Redirect pour éviter doublon au F5.
7. **Affichage** `[XSS]`
   `htmlspecialchars()` sur tous les champs. `nl2br()` après `htmlspecialchars()` sur description et messages.

---

### `auth.php`
1. **startSession()**
   Lance `session_start()` seulement si `PHP_SESSION_NONE` — évite l'erreur "session already started".
2. **requireLogin()** `[SÉCURITÉ]`
   Vérifie `isset($_SESSION['user_id'])`. Si absent → `header(Location)` + `exit()`.
3. **requireRole($role)** `[SÉCURITÉ]` `[RÔLE]`
   Appelle `requireLogin()` puis compare `$_SESSION['role']`. Rôle incorrect → redirect dashboard.
4. **login($user, $pass)** `[SÉCURITÉ]`
   SELECT par username → `password_verify()` → remplit `$_SESSION` si ok. Retourne true/false.
5. **logout()** `[SÉCURITÉ]`
   Vide `$_SESSION`, supprime cookie, `session_destroy()`.

---

### `db.php`
1. **getDB() — singleton**
   Variable static `$pdo = null`. Si null → crée la connexion PDO. Sinon → retourne la connexion existante. Une seule connexion par requête HTTP.
2. **DSN PostgreSQL**
   `pgsql:host=db;dbname=help_desk_db` — host=db car c'est le nom du service Docker, pas localhost.
3. **Options PDO**
   `ERRMODE_EXCEPTION` : les erreurs SQL lèvent une exception au lieu d'échouer silencieusement. `FETCH_ASSOC` : retourne des tableaux associatifs.
4. **Requêtes préparées partout** `[SÉCURITÉ]`
   Jamais de concaténation de variables dans le SQL. Toujours `prepare()` + `execute([:param => $val])`. Protège contre les injections SQL.
