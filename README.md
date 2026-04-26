# Helpdesk — Mini application de gestion de tickets

Mini helpdesk en PHP réalisé dans le cadre du projet DAW (L3).

## Membres du groupe

- Farid
- Rayan
- Lylia

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
