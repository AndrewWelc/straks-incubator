#!/bin/bash

cd /opt/straks/incubator
./shutdown.sh
docker pull registry.straks.app/straks/incubator-app:dev
docker pull registry.straks.app/straks/incubator-web:dev
./run.sh
