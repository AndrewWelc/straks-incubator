FROM php:7.2-fpm-alpine 

MAINTAINER squbs <squbs@straks.tech> 

RUN apk add --update --no-cache --virtual .build-deps \ 
        $PHPIZE_DEPS \ 
        curl-dev \ 
        imagemagick-dev \ 
        libtool \ 
        libxml2-dev \ 
        postgresql-dev \ 
        sqlite-dev \ 
        freetype-dev \
        libjpeg-turbo-dev \
        libpng-dev \
        gmp-dev \
    && apk add --no-cache \ 
        curl \ 
        git \ 
        imagemagick \ 
        mysql-client \ 
        postgresql-libs \ 
        libintl \ 
        icu \ 
        icu-dev \ 
    && pecl install imagick \ 
    && docker-php-ext-enable imagick \ 
    && docker-php-ext-configure gd --with-freetype-dir=/usr/include/ --with-jpeg-dir=/usr/include/ \
    && docker-php-ext-install \ 
        curl \ 
        iconv \ 
        mbstring \ 
        pdo \ 
        pdo_mysql \ 
        pdo_pgsql \ 
        pdo_sqlite \ 
        pcntl \ 
        tokenizer \ 
        xml \ 
        zip \ 
        intl \
        gd \
        gmp \
    && curl -s https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin/ --filename=composer \
    && apk del -f .build-deps
    
    COPY . /var/www
    RUN composer update -d /var/www
    RUN composer install -d /var/www
    RUN chown -R www-data:www-data /var/www
    RUN chmod -R 755 /var/www
    
