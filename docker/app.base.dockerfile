FROM wordpress:5.5-php7.4-apache AS wordpress_base


RUN apt-get update; \
	apt-get install -y git subversion zip unzip vim nano

ADD default.conf /etc/apache2/sites-available/000-default.conf
RUN mkdir /etc/apache2/ssl/

COPY openssl.cnf /etc/apache2/ssl/openssl.localhost.cnf
RUN openssl req \
    -config /etc/apache2/ssl/openssl.localhost.cnf \
    -x509 \
    -nodes \
    -days 365 \
    -sha256 \
    -newkey rsa:2048 \
    -keyout /etc/apache2/ssl/server.key \
    -out /etc/apache2/ssl/server.crt


# Install Composer
RUN curl -sS https://getcomposer.org/installer | php -- --install- dir=/usr/local/bin --filename=composer \
    && mv composer /usr/local/bin

RUN a2enmod rewrite expires ssl

EXPOSE 443
