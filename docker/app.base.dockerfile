FROM php:7.3-apache AS wordpress_base

ADD default.conf /etc/apache2/sites-available/000-default.conf
COPY ./keys/server.crt /etc/apache2/ssl/server.crt
COPY ./keys/server.key /etc/apache2/ssl/server.key

# install the PHP extensions we need
RUN set -ex; \
	\
	apt-get update; \
	apt-get install -y \
		libjpeg-dev \
		libpng-dev \
	; \
	rm -rf /var/lib/apt/lists/*; \
	\
	docker-php-ext-configure gd --with-png-dir=/usr --with-jpeg-dir=/usr; \
	docker-php-ext-install gd mysqli opcache; \
 	apt-get update; \
	apt-get install -y git subversion zip unzip vim nano
# TODO consider removing the *-dev deps and only keeping the necessary lib* packages

# set recommended PHP.ini settings
# see https://secure.php.net/manual/en/opcache.installation.php
RUN { \
		echo 'opcache.memory_consumption=128'; \
		echo 'opcache.interned_strings_buffer=8'; \
		echo 'opcache.max_accelerated_files=4000'; \
		echo 'opcache.revalidate_freq=2'; \
		echo 'opcache.fast_shutdown=1'; \
		echo 'opcache.enable_cli=1'; \
	} > /usr/local/etc/php/conf.d/opcache-recommended.ini

RUN a2enmod rewrite expires ssl

ENV WORDPRESS_VERSION 5.1.1
ENV WORDPRESS_SHA1 f1bff89cc360bf5ef7086594e8a9b68b4cbf2192
ENV DIRPATH /var/www/html

RUN set -ex; \
	curl -o wordpress.tar.gz -fSL "https://wordpress.org/wordpress-${WORDPRESS_VERSION}.tar.gz"; \
	echo "$WORDPRESS_SHA1 *wordpress.tar.gz" | sha1sum -c -; \
# upstream tarballs include ./wordpress/ so this gives us /usr/src/wordpress
	tar -xzf wordpress.tar.gz -C /usr/src/; \
	rm wordpress.tar.gz; \
	chown -R www-data:www-data /usr/src/wordpress; \
        chown -R www-data:www-data ${DIRPATH}

# Install Composer
RUN curl -sS https://getcomposer.org/installer | php -- --install- dir=/usr/local/bin --filename=composer \
    && mv composer /usr/local/bin

COPY docker-entrypoint.sh /usr/local/bin/

RUN chmod +x /usr/local/bin/docker-entrypoint.sh

ENTRYPOINT ["/usr/local/bin/docker-entrypoint.sh"]
CMD ["apache2-foreground"]

EXPOSE 443
