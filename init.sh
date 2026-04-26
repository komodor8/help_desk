#!/bin/bash
# init.sh — Initialisation complète du projet Helpdesk
# Usage : bash init.sh

set -e

echo "🚀 Démarrage des conteneurs..."
docker compose up -d

echo "⏳ Attente du démarrage de PostgreSQL..."
sleep 3

echo "📦 Import du schéma et des données de test..."
docker compose exec -T db psql -U farid -d help_desk_db < schema.sql

echo ""
echo "✅ Application prête !"
echo "   http://localhost:8000"
echo ""
echo "   Adminer : http://localhost:8081"
echo ""
echo "   Comptes de test (mot de passe : password) :"
echo "   - farid   → Étudiant"
echo "   - Rayan   → Étudiant"
echo "   - Lylia   → Tuteur"
