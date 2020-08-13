#!/bin/bash
docker-compose up -d wordpress-dev

PLUGIN_PATH="/var/www/html/wp-content/plugins/yoti"

# Install WordPress testing environment.
docker-compose exec -w $PLUGIN_PATH wordpress-dev sh -c './bin/install-wp-tests.sh wordpress-test root $MYSQL_ROOT_PASSWORD wordpress-db $WORDPRESS_VERSION' >/dev/null 2>&1

# Coding Standards
docker-compose exec -w $PLUGIN_PATH wordpress-dev composer lint

# Run tests.
docker-compose exec -w $PLUGIN_PATH wordpress-dev composer test
