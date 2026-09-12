FROM php:8.2-apache

RUN docker-php-ext-install pdo_mysql \
    && a2enmod rewrite

WORKDIR /var/www/html
COPY . /var/www/html/

RUN mkdir -p /var/www/html/assets \
    && curl -fL "https://drive.google.com/uc?export=download&id=1KIDs-kuskk5i4iGTNpj3Wru5iBTjtBP5" -o /var/www/html/index.php \
    && curl -fL "https://drive.google.com/uc?export=download&id=1DsPeS93GcUjwu0Lt334dgAaFGhUCjH4N" -o /var/www/html/assets/style.css \
    && chown -R www-data:www-data /var/www/html

EXPOSE 80
CMD ["apache2-foreground"]
