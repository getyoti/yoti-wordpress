#!/bin/bash
docker-compose up -d wordpress-dev

PLUGIN_PATH="/var/www/html/wp-content/plugins/yoti"

# Install WordPress testing environment.
docker-compose exec -w $PLUGIN_PATH wordpress-dev sh -c './bin/install-wp-tests.sh wordpress-test root root wordpress-db $WORDPRESS_VERSION'

# Coding Standards
docker-compose exec -w $PLUGIN_PATH wordpress-dev ./vendor/bin/phpcs

# Run tests.
docker-compose exec -w $PLUGIN_PATH wordpress-dev ./vendor/bin/phpunit
