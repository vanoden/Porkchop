Porkchop CMS
===========

The Porkchop CMS is a content management system I've built over many years and used on many different projects.  It can act as a set of web services, an internal tool kit or as a complete external web site. It is very modular, and I find it is easy to build new modules and implement them taking advantage of the existing classes to rapidly prototype new functionality.

The CMS is divided into 'modules' and 'views'.  Each url contains the name of a module with a leading underscore followed by the name of a view.  The engine parses the URL on forward slashes and loads the appropriate view for the module.  It parses the remainder of the url as parameters.

For example:

 /_register/account/bobdole

would load the 'account' view of the 'register' module populated with account information for the user 'bobdole'.

Nearly every module as an 'api' view.  This exposes the modules' methods for REST applications.  There is little that cannot be accessed or updated via the api's by a user with the appropriate roles.

## Installation
Install Apache, cURL, PHP and PHP Modules
```
apt-get install apache2 php-common php-cli php-json php-mysql php-xml php-curl php-mysql php-pear curl
```
Enable mod-rewrite
```
sudo a2enmod rewrite
```
Clone the repository into a folder of your choice.  Configure your web server to use the 'html' subfolder as it's document root.

You will need to configure the server to rewrite urls beginning with '/_' to load '/core/index.php'.  Look in /misc for a sample Apache configuration.

### Third Party Modules

Install composer.phar
```
php -r "copy('https://getcomposer.org/installer', 'composer-setup.php');"
php -r "if (hash_file('SHA384', 'composer-setup.php') === '669656bab3166a7aff8a7506b8cb2d1c292f042046c5a994c43155c0be6190fa0355160742ab2e1c88d40d5be660b410') { echo 'Installer verified'; } else { echo 'Installer corrupt'; unlink('composer-setup.php'); } echo PHP_EOL;"
php composer-setup.php
php -r "unlink('composer-setup.php');"
```
Run composer
```
php composer.phar install --no-dev
```
Install XML Serializer
```
pear install channel://pear.php.net/XML_Serializer-0.21.0
```
### Configuration

Copy the file '/config/config.php.dist' to '/config/config.php'.  Edit the file with your database connection information and other things specific to your site.

### Setup

Call the Install script to run the initialization tasks:

http://your.domain.name/_install

Call the Upgrade script to apply recent updates to schema, etc:

http://your.domain.name/_upgrade

## Use

This package was developed and is maintained by Anthony Caravello with the help of contributors.  It is released under the MIT license.  See LICENSE.txt for more information.

## Docker Build Instructions

**Install Docker**

    https://docs.docker.com/get-docker/

**Building the container**

    $ cd docker/

**Getting it running**

    To start your environment, run this command in this folder of your local terminal: 
        $ docker-compose up

**Debugging**

    Useful commands to work with your environment
        $ docker-compose ps
        $ docker-compose logs
      
**Connect to Docker MySQL**
    $ mysql -h localhost -P 8082 --protocol=tcp -u spectros -p
