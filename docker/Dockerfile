FROM php:7.1-apache

ARG BRANCH

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
	apt-get install -y git zip unzip vim nano
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

VOLUME /var/www/html

ENV WORDPRESS_VERSION 4.9.1
ENV WORDPRESS_SHA1 892d2c23b9d458ec3d44de59b753adb41012e903
ENV DEFAULT_BRANCH master
ENV DIRPATH /var/www/html
ENV PLUGIN_PACKAGE_NAME yoti-wordpress-edge.zip

RUN if [ "$BRANCH" = "" ]; then \
  $BRANCH = $DEFAULT_BRANCH; \
fi


RUN set -ex; \
	curl -o wordpress.tar.gz -fSL "https://wordpress.org/wordpress-${WORDPRESS_VERSION}.tar.gz"; \
	echo "$WORDPRESS_SHA1 *wordpress.tar.gz" | sha1sum -c -; \
# upstream tarballs include ./wordpress/ so this gives us /usr/src/wordpress
	tar -xzf wordpress.tar.gz -C /usr/src/; \
	rm wordpress.tar.gz; \
	chown -R www-data:www-data /usr/src/wordpress; \
        chown -R www-data:www-data /var/www/html

RUN set -ex; \
        git clone -b ${BRANCH} https://github.com/getyoti/yoti-wordpress.git --single-branch /usr/src/yoti-wordpress; \
        echo "Finished cloning ${BRANCH}"; \
	chown -R www-data:www-data /usr/src/yoti-wordpress; \
       	cd /usr/src/yoti-wordpress; \
        mkdir __sdk-sym; \
        ./pack-plugin.sh; \
   	echo "Finished packing the plugin"; \
        mv ./${PLUGIN_PACKAGE_NAME} /usr/src/wordpress/wp-content/plugins; \
        cd /usr/src/wordpress/wp-content/plugins; \
        unzip ${PLUGIN_PACKAGE_NAME}; \
        rm -f ${PLUGIN_PACKAGE_NAME}

COPY docker-entrypoint.sh /usr/local/bin/

RUN chmod +x /usr/local/bin/docker-entrypoint.sh

ENTRYPOINT ["/usr/local/bin/docker-entrypoint.sh"]
CMD ["apache2-foreground"]

EXPOSE 80
EXPOSE 443
