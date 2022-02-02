#!/bin/bash

docker run --rm \
    -p 80:80 \
    -p 443:443 \
    --name letsencrypt \
    -v certs:/etc/letsencrypt \
    -e "LETSENCRYPT_EMAIL=squbs@straks.tech" \
    -e "LETSENCRYPT_DOMAIN1=kt9rrdbcbpyctkiv7uf.com" \
    blacklabelops/letsencrypt install
