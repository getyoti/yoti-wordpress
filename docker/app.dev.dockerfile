FROM docker_wordpress-base:latest

# Install and configure xdebug.
RUN yes | pecl install xdebug \
    && echo "zend_extension=$(find /usr/local/lib/php/extensions/ -name xdebug.so)" > /usr/local/etc/php/conf.d/xdebug.extension.ini
COPY ./docker/xdebug.ini /usr/local/etc/php/conf.d/xdebug.ini

# Install PHP_CodeSniffer
RUN composer global require dealerdirect/phpcodesniffer-composer-installer
RUN composer global require wp-coding-standards/wpcs
