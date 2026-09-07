# Utiliser l'image officielle PHP avec Apache
FROM php:8.3-apache

# 1. Installer les dépendances système requises
RUN apt-get update && apt-get install -y \
    libpng-dev \
    libjpeg-dev \
    libfreetype6-dev \
    libzip-dev \
    libpq-dev \
    unzip \
    git \
    curl

# 2. Installer Node.js (indispensable pour compiler Vite et Tailwind)
RUN curl -fsSL https://deb.nodesource.com/setup_20.x | bash - \
    && apt-get install -y nodejs

# 3. Installer les extensions PHP (pdo_pgsql pour votre base de données Supabase, gd pour que Dompdf gère les images)
RUN docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install pdo pdo_pgsql pgsql zip gd

# 4. Activer le module de réécriture d'Apache pour que le routage Laravel fonctionne
RUN a2enmod rewrite

# 5. Configurer Apache pour pointer vers le dossier "public" de Laravel
ENV APACHE_DOCUMENT_ROOT /var/www/html/public
RUN sed -ri -e 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/sites-available/*.conf
RUN sed -ri -e 's!/var/www/!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/apache2.conf /etc/apache2/conf-available/*.conf

# 6. Installer Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# 7. Définir le répertoire de travail et copier le code
WORKDIR /var/www/html
COPY . .

# 8. Installer les dépendances et compiler le frontend
RUN composer install --no-dev --optimize-autoloader
RUN npm install
RUN npm run build

# 9. Ajuster les permissions pour que Laravel puisse écrire dans ses dossiers
RUN chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache
RUN chmod -R 775 /var/www/html/storage /var/www/html/bootstrap/cache

# 10. Exposer le port 80 pour Render
EXPOSE 80