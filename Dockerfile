FROM php:8.2-cli

# 1. On met à jour la liste des paquets
# 2. On installe libpq-dev (les fameux fichiers .h dont PHP a besoin)
# 3. On installe les extensions PHP
RUN apt-get update && apt-get install -y \
    libpq-dev \
    && docker-php-ext-install pdo pdo_pgsql