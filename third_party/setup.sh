#!/bin/bash

# Install Composer Dependencies
sudo apt-get update
sudo apt-get -y install curl php-curl

# Get Composer
curl -sS https://getcomposer.org/installer -o composer-setup.php

# Validate Composer
# Get SHA-384 Key from https://composer.github.io/installer.sha384sum 
# Set environment variable 'HASH' to that key
curl -o hashfile https://composer.github.io/installer.sha384sum

HASH=`cat hashfile|awk '{print $1}'`
rm -f hashfile

# Run Validation
php -r "if (hash_file('SHA384', 'composer-setup.php') === '$HASH') { echo 'Installer verified'; } else { echo 'Installer corrupt'; unlink('composer-setup.php'); } echo PHP_EOL;"

# Install Composer
sudo php composer-setup.php --install-dir=/usr/local/bin --filename=composer

# Run Composer
composer install --no-dev
