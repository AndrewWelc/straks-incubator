FROM registry.straks.app/straks/incubator-app:dev

MAINTAINER squbs <squbs@straks.tech> 

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
