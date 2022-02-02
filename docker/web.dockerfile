FROM nginx:1.15.2-alpine

ADD ./nginx/nginx.conf /etc/nginx/conf.d/default.conf

COPY public /var/www/public
