# Porkchop CMS

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

## Local Enviroment Settings (LAMP stack)

**apache configuration file**

From the `config/apache2.config.dist` create your own local instance of apache2 config
$ cp config/apache-config.conf /etc/apache2/sites-enabled/000-default.conf

# Manually set up the apache environment variables
ENV APACHE_RUN_USER www-data
ENV APACHE_RUN_GROUP www-data
ENV APACHE_LOG_DIR /var/log/apache2
ENV APACHE_LOCK_DIR /var/lock/apache2
ENV APACHE_PID_FILE /var/run/apache2.pid

# Install Dependancies
$ apt-get update && apt-get -y upgrade && apt-get -y install apache2 curl lynx-common lynx git vim curl zlib1g-dev libmemcached-dev build-essential nodejs npm
$ pecl install memcached && docker-php-ext-enable memcached
$ pear config-set preferred_state beta
$ pear install XML_Serializer

# Enable apache mods.
$ a2enmod rewrite

# Update the PHP.ini file, enable <? ?> tags and quieten logging.
$ sed -i "s/short_open_tag = Off/short_open_tag = On/" /usr/local/etc/php/php.ini
$ sed -i "s/error_reporting = .*$/error_reporting = E_ERROR | E_WARNING | E_PARSE/" /usr/local/etc/php/php.ini

# Run NPM to build the project
$ /usr/bin/npm install -g gulp
$ /usr/bin/npm install -g bower

# Update the default apache site with the config we created.
$ mkdir /var/log/apache2/SpectrosWWW/
$ touch /var/log/apache2/SpectrosWWW/application.log
$ chmod 777 /var/log/apache2/SpectrosWWW/application.log

### Gulp
gulp is a preprocessor we use to do some customization of static content, ie global headers and footers, js versioning, company branding.

Make sure no existing node_modules folder
```
rm -rf node_modules/
```
Install npm
```
sudo apt-get install npm
```
Install gulp and includes
```
npm install -g gulp
npm install gulp-cli gulp-template gulp-data gulp-debug
```
### Configuration

Copy the file '/config/config.php.dist' to '/config/config.php'.  Edit the file with your database connection information and other things specific to your site.

### Setup

Call the Install script to run the initialization tasks:

http://your.domain.name/_install

Call the Upgrade script to apply recent updates to schema, etc:

http://your.domain.name/_upgrade

**Basic Database Setup [Spectros Instruments Example] http://localhost/**

/** INSERT FIXTURE DATA TO START **/
INSERT INTO `company_companies` VALUES (1,'Spectros Instruments','',1,1,0);
INSERT INTO `company_locations` VALUES (1,1,'localhost','','','',0,0,0,'',0,0,0,0,0,0,0,0,0,'',0,0,1,'localhost');
INSERT INTO `company_domains` VALUES (1,1,'',1,'localhost','0000-00-00','0000-00-00 00:00:00','0000-00-00',0,'',1);

INSERT INTO `register_users` VALUES (1,'ACTIVE','Spectros User',NULL,'Spectros User','spectros','*A8D4C1EB4499988FAB79F9C0991FD568FBC6054E','',0,1,0,'2014-10-01 01:04:24','2020-08-22 19:52:06','0000-00-00 00:00:00','local','','d02fe6499e2e7b6edf5a1f88036d9e84',NULL,'America/New_York',0);
INSERT INTO `register_organizations` VALUES (1, 'Spectros Instruments','12345','ACTIVE','0000-00-00 00:00:00','0','0','');
INSERT INTO `register_roles` VALUES ('1','register manager','Manage accounts and roles'),('2','register reporter','c'),('3','content operator','Can add/edit pages'),('4','content developer','c'),('5','product manager','c'),('6','product reporter','c'),('11','media manager','c'),('12','media reporter','c'),('13','media developer','c'),('14','monitor manager','c'),('15','monitor reporter','c'),('16','monitor admin','c'),('17','support manager','c'),('18','support reporter','c'),('19','email manager','c'),('20','contact admin','c'),('87','action manager','c'),('88','action user','c'),('89','monitor asset','c'),('90','storage manager','c'),('91','storage upload','c'),('92','package manager','c'),('93','issue admin','c'),('94','engineering manager','c'),('95','engineering user','c'),('96','administrator','c'),('97','support user','See and Update Customer Support Requests'),('100','developer','c'),('101','operator','c'),('102','manager','c'),('103','engineering reporter','c'),('104','credit manager','c'),('105','build manager','b'),('106','build user','b'),('107','geography manager','geography manager'),('108','geography user','geography user'),('109','location manager','Can view and manage location entries'),('110','shipping manager','Can browse all shipments'),('111','alert manager','Can view/edit assets, sensors and collections'),('112','alert reporter','Can view assets, sensors and collections'),('113','alert admin','Full access to alert data'),('114','alert asset','Holding role for actual devices that can post data.');
INSERT INTO `register_roles_privileges` VALUES ('3','13'),('3','14'),('3','15');
INSERT INTO `register_users_roles` VALUES (1,1),(1,2),(1,3),(1,4),(1,5),(1,6),(1,11),(1,12),(1,13),(1,14),(1,15),(1,16),(1,17),(1,18),(1,19),(1,20),(1,87),(1,88),(1,90),(1,91),(1,92),(1,93),(1,95),(1,96),(1,97),(1,103);

INSERT INTO `monitor_sensors` VALUES (1,1,'Kevin Monitor Sensor',1,NULL,'celcius',NULL,'decimal',1,'0.00','1.00',0);
INSERT INTO `monitor_sensors` VALUES (2,2,'Kevin Monitor Sensor Humidity',1,NULL,'mg/L',NULL,'integer',1,'0.00','1.00',0);
INSERT INTO `monitor_sensor_models` VALUES ('1','Temperature Sensor','Temp Sensor','deg. celcius','decimal',NULL,NULL,'{type: \'linear\',offset: 0,multiplier: 1}',NULL,NULL);
INSERT INTO `monitor_sensor_models` VALUES ('2','Humidity Sensor','Humidity Sensor','mg/L','decimal',NULL,NULL,'{type: \'linear\',offset: 0,multiplier: 1}',NULL,NULL);
INSERT INTO `product_products` VALUES ('1','RPI-KEVIN-ZERO','Raspberry PI Zero','Small Temperature Monitor','unique','ACTIVE','0.00',NULL,'0.00',NULL,'0.00','0.00');
INSERT INTO `monitor_assets` VALUES ('1','KEV-RPI-TEMP','1','Raspberry Pi Zero W w/DHT 11','1','1','0');

INSERT INTO `monitor_collections` VALUES (1,'ABCD1234',1,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'ACTIVE','2021-01-01 00:00:00','DEV-COLLECTION-SITE','America/New_York',1609502400,1893499200,NULL,'time span');
INSERT INTO `monitor_collection_sensors` VALUES (1,1,'RPI Temp',NULL,NULL);
INSERT INTO `monitor_collection_sensors` VALUES (1,2,'RPI Humdity',NULL,NULL)

## Use
This package was developed and is maintained by Anthony Caravello with the help of contributors.  It is released under the MIT license.  See LICENSE.txt for more information.
