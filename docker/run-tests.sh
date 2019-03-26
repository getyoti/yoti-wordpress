#!/bin/bash
docker-compose up -d wordpress-dev

# Coding Standards
docker-compose exec wordpress-dev sh -c "~/.composer/vendor/bin/phpcs --standard=WordPress --ignore=*/var/www/html/wp-content/plugins/yoti/sdk* ./wp-content/plugins/yoti"
