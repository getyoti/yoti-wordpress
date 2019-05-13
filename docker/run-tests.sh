#!/bin/bash
docker-compose up -d wordpress-dev

# Install WordPress testing environment.
docker-compose exec wordpress-dev sh -c './install-wp-tests.sh wordpress-test root root wordpress-db $WORDPRESS_VERSION'

# Coding Standards
docker-compose exec wordpress-dev sh -c "./vendor/bin/phpcs ./wp-content/plugins/yoti"

# Run tests.
docker-compose exec wordpress-dev ./vendor/bin/phpunit
