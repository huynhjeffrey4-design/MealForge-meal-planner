FROM php:8.1-cli

RUN docker-php-ext-install pdo pdo_mysql mysqli

WORKDIR /var/www/html

EXPOSE 8080

CMD ["php", "-S", "0.0.0.0:8080", "-t", "/var/www/html"]
