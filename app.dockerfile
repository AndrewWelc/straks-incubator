FROM php:7.2-fpm-alpine 

MAINTAINER squbs <squbs@straks.tech> 

RUN apk add --no-cache --virtual .build-deps \ 
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

COPY composer.lock composer.json /var/www/

COPY database /var/www/database

COPY packages /var/www/packages

WORKDIR /var/www

#RUN php -r "copy('https://getcomposer.org/installer', 'composer-setup.php');" \
#    && php -r "if (hash_file('SHA384', 'composer-setup.php') === '544e09ee996cdf60ece3804abc52599c22b1f40f4323403c44d44fdfdd586475ca9813a858088ffbc1f233e9b180f061') { echo 'Installer verified'; } else { echo 'Installer corrupt'; unlink('composer-setup.php'); } echo PHP_EOL;" \
#    && php composer-setup.php \
#    && php -r "unlink('composer-setup.php');" \
#    && php composer.phar require nunomaduro/collision --dev \
#    && php composer.phar install \
#    && rm composer.phar

COPY . /var/www

RUN chown -R www-data:www-data \
        /var/www
RUN chmod -R 755 /var/www
        
RUN composer update

RUN composer install
