FROM php:7.4-fpm

COPY ./conf/php.ini /usr/local/etc/php/

RUN apt update
RUN apt install -y \
    libpng-dev \
    libzip-dev \
    libxml2-dev \
    libpq-dev \
    libicu-dev

RUN apt-get install -y wkhtmltopdf xvfb

RUN docker-php-ext-install \
        intl \
        pdo \
        pdo_pgsql \
        xml \
        zip \
        gd \
        soap \
        bcmath \
        sockets

RUN pecl install xdebug-3.1.0

RUN docker-php-ext-enable --ini-name 05-opcache.ini opcache xdebug

COPY --from=composer:latest /usr/bin/composer /usr/local/bin/composer
RUN chmod +x /usr/local/bin/composer

RUN curl https://cs.symfony.com/download/php-cs-fixer-v2.phar --output /usr/local/bin/php-cs-fixer
RUN chmod +x /usr/local/bin/php-cs-fixer
#RUN chown -R www-data /var/www/html/var

