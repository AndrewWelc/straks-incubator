#/bin/bash

docker build --cache-from straks/incubator-app -t straks/incubator-app . -f docker/app.dockerfile
docker build --cache-from straks/incubator-web -t straks/incubator-web . -f web.dockerfile



