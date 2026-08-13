FROM php:8.3-apache

# Serve from public/ instead of the project root
RUN sed -i 's|/var/www/html|/var/www/html/public|g' \
        /etc/apache2/sites-available/000-default.conf \
    && sed -i 's|/var/www/html|/var/www/html/public|g' \
        /etc/apache2/apache2.conf \
    && a2enmod rewrite

WORKDIR /var/www/html
COPY . .

# data/ holds JSON score files — writable by Apache at runtime
RUN mkdir -p data/scores data/ratelimit \
    && chown -R www-data:www-data data \
    && chmod 775 data data/scores data/ratelimit

EXPOSE 80

HEALTHCHECK --interval=30s --timeout=5s --start-period=10s \
    CMD curl -f http://localhost/api/games.php || exit 1
