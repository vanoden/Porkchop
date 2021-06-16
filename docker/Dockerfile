FROM ubuntu:latest
FROM memcached:latest
FROM php:7.4-apache

MAINTAINER Kevin Hinds <khinds10.work@gmail.com>

# Install apache, PHP, and supplimentary programs. openssh-server, curl, and lynx* are for debugging the container.
RUN apt-get update && apt-get -y upgrade && apt-get -y install apache2 curl lynx-common lynx git vim curl zlib1g-dev libmemcached-dev build-essential nodejs npm
RUN pecl install memcached && docker-php-ext-enable memcached
RUN pear config-set preferred_state beta
RUN pear install XML_Serializer

# Enable apache mods.
RUN a2enmod rewrite
RUN docker-php-ext-install pdo pdo_mysql mysqli

# Update the PHP.ini file, enable <? ?> tags and quieten logging.
RUN mv /usr/local/etc/php/php.ini-development /usr/local/etc/php/php.ini
RUN sed -i "s/short_open_tag = Off/short_open_tag = On/" /usr/local/etc/php/php.ini
RUN sed -i "s/error_reporting = .*$/error_reporting = E_ERROR | E_WARNING | E_PARSE/" /usr/local/etc/php/php.ini

RUN /usr/bin/npm install -g gulp
RUN /usr/bin/npm install -g bower
CMD ["gulp", "process"]

#Install Composer
RUN php -r "copy('https://getcomposer.org/installer', 'composer-setup.php');"
RUN php composer-setup.php --install-dir=. --filename=composer
RUN mv composer /usr/local/bin/

# Manually set up the apache environment variables
ENV APACHE_RUN_USER www-data
ENV APACHE_RUN_GROUP www-data
ENV APACHE_LOG_DIR /var/log/apache2
ENV APACHE_LOCK_DIR /var/lock/apache2
ENV APACHE_PID_FILE /var/run/apache2.pid

# Expose apache.
EXPOSE 80

# Update the default apache site with the config we created.
RUN mkdir /var/log/apache2/SpectrosWWW/
RUN touch /var/log/apache2/SpectrosWWW/application.log
RUN chmod 777 /var/log/apache2/SpectrosWWW/application.log
ADD config/apache-config.conf /etc/apache2/sites-enabled/000-default.conf

# move the docker config
#RUN cat /var/www/SpectrosWWW/config/config.php

# By default start up apache in the foreground, override with /bin/bash for interative.
CMD /usr/sbin/apache2ctl -D FOREGROUND
