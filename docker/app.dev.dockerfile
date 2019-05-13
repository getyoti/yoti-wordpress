FROM docker_wordpress-base:latest

# Install and configure xdebug.
RUN yes | pecl install xdebug \
    && echo "zend_extension=$(find /usr/local/lib/php/extensions/ -name xdebug.so)" > /usr/local/etc/php/conf.d/xdebug.extension.ini
COPY ./docker/xdebug.ini /usr/local/etc/php/conf.d/xdebug.ini

# Install MySQL Client.
RUN apt-get install -y mysql-client

# Install PHP_CodeSniffer.
RUN composer require dealerdirect/phpcodesniffer-composer-installer
RUN composer require wp-coding-standards/wpcs

# Install and configure PHPUnit.
RUN composer require --dev phpunit/phpunit ^7
COPY ./phpunit.xml.dist .

# Copy PHP Code Sniffer configuration.
COPY ./phpcs.xml.dist .

# Install WordPress testing environment.
COPY ./bin/install-wp-tests.sh .
RUN chmod +x ./install-wp-tests.sh
