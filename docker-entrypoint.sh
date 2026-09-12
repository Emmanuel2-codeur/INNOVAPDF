#!/bin/bash
set -e

echo "Application des migrations en attente..."
php artisan migrate --force

echo "Démarrage d'Apache..."
exec apache2-foreground