#!/bin/bash

LE_DIR=/etc/letsencrypt docker-compose exec app chown -R www-data:www-data ./
LE_DIR=/etc/letsencrypt docker-compose exec app find . -type f -exec chmod 664 {} \; 
LE_DIR=/etc/letsencrypt docker-compose exec app find . -type d -exec chmod 775 {} \;
LE_DIR=/etc/letsencrypt docker-compose exec app chmod -R ug+rwx storage bootstrap/cache

