#!/bin/bash

# Update composer
cd ../../
composer update
cd ./tests/docker/

# Shutdown, then start
docker-compose -p neo-arango-wrap down

docker-compose -p neo-arango-wrap up -d