Porkchop CMS
===========

The Porkchop CMS is a content management system I've built over many years and used on many different projects.  It can act as a set of web services, an internal tool kit or as a complete external web site. It is very modular, and I find it is easy to build new modules and implement them taking advantage of the existing classes to rapidly prototype new functionality.

The CMS is divided into 'modules' and 'views'.  Each url contains the name of a module with a leading underscore followed by the name of a view.  The engine parses the URL on forward slashes and loads the appropriate view for the module.  It parses the remainder of the url as parameters.

For example:

 /_register/account/bobdole

would load the 'account' view of the 'register' module populated with account information for the user 'bobdole'.

Nearly every module as an 'api' view.  This exposes the modules' methods for REST applications.  There is little that cannot be accessed or updated via the api's by a user with the appropriate roles.

## Installation

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

## Configuration

Copy the file '/config/config.php.dist' to '/config/config.php'.  Edit the file with your database connection information and other things specific to your site.

## Use

This package was developed by Anthony Caravello.  It is released under the MIT license.  See LICENSE.txt for more information.
