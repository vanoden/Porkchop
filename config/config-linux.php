<?php
	define ( 'BASE', '/home/khinds/Dropbox/Current-Projects/SpectrosInstruments.com/Workspace-Sites/SpectrosWWW/' );
	define ( 'ENV', '' );
	define ( 'PATH', '' );
	define ( 'HTML', BASE.'/html' );
	define ( 'INCLUDES', BASE.'/'.ENV.'/includes' );
	define ( 'MODULES', BASE.'/'.ENV.'/modules' );
	define ( 'THIRD_PARTY', BASE.'/third_party/vendor' );
	define ( 'RESOURCES', BASE.'/resources' );
	define ( 'CLASS_PATH', BASE.'/classes' );
	define ( 'API_LOG', '/var/log/apache2/SpectrosWWW/log');
	define ( 'APPLICATION_LOG', '/var/log/apache2/SpectrosWWW/application.log');
	define ( 'APPLICATION_LOG_LEVEL', 'debug');
	define ( 'APPLICATION_LOG_TYPE', 'file');
	define ( 'TEMPLATES', BASE.'/templates' );	
	define ( 'REPORTS',BASE.'/reports' );	
	define ( 'SPECTROS_LOCATION_ID', 1);
	define ( 'SPECTROS_VENDOR_ID', 0);
    define ( 'ENVIROMENT', 'QA'); // 'DEV' / 'QA' / 'PRODUCTION'
	define ( 'QA_CAPTCHA_BYPASS', 'GZdWHjJJk9xHJXHFPeu4gZs6YDfcEXyb');
	
	
        # Initialize config object
        $_config = new stdClass();

        # Site Configurations
        $_config->site = new stdClass();
        $_config->site->name = "SpectrosWWW";
        $_config->site->https = false;
        $_config->site->hostname = "kevin.spectrosinstruments.com";
        $_config->site->default_template = "default.html";
        $_config->site->support_email = 'service@spectrosinstruments.com';

        # Session Info
        $_config->session = new stdClass();
        $_config->session->cookie = 'spectros_session_code';
        $_config->session->domain = 'kevin.spectrosinstruments.com';
        $_config->session->expires = 86400;

        # Mail Server (SMTP)
        $_config->email = new stdClass();
        $_config->email->provider = 'Proxy';
        $_config->email->hostname = "tt15.rootseven.com";
        $_config->email->token    = "cech9Ich3s967Ouj3eDser";

        # Cache Mechanism (memcache or xcache)
        $_config->cache = new stdClass();
        $_config->cache->mechanism = "memcached";

        # Database
        $_config->database = new stdClass();
        $_config->database->driver              = 'mysqli';
        $_config->database->schema              = 'spectros_2022';
        $_config->database->master = (object) array(
                'hostname'      => '127.0.0.1',
                'username'      => "khinds",
                'password'      => "M03e151%",
                'port'          => "3306"
        );

        # ElasticSearch
        $_config->elasticsearch = new stdClass();
        $_config->elasticsearch->hosts = array('localhost:9200');

        # reCAPTCHA
        $_config->captcha = (object) array(
                "public_key"    => '6LeTdfgSAAAAAPZ5Fb-J6R_X9GctCVy8l2MrUCuO',
                "private_key"   => '6LeTdfgSAAAAAMfk9KEN2e2632bcb76FqnAaRMTB',
        );

        # Google Maps API
        $_config->google = (object) array(
                "maps_api" => array(
                        "key"   => "AIzaSyBSAWVQsOovimDZXB7r5d1pIOHrGLbCmsw"
                )
        );

        # Registration Module
        $_config->register = new stdClass();
        $_config->register->minimum_password_strength = 8;
        $_config->register->auth_target = '/_spectros/welcome';

        # Confirmation Email Info
        $_config->register->contact_us_confirmation = (object) array(
                "from"          => 'no-reply@spectrosinstruments.com',
                "subject"       => "Welcome to Spectros Instruments Web Site",
                "header"        => TEMPLATES."/contact_us_confirmation.html"
        );
        $_config->register->contact_us_notification = (object) array(
                "from"          => 'no-reply@spectrosinstruments.com',
                "subject"       => "Contact Us Form Submitted by Customer",
                "header"        => TEMPLATES."/contact_us_notification.html",
                "template"      => TEMPLATES."/contact_us_notify.html"
        );
        $_config->register->forgot_password = (object) array(
                "from"          => 'no-reply@spectrosinstruments.com',
                "subject"       => "Spectros Account Management",
                "template"      => TEMPLATES."/forgot_password.html"
        );
        $_config->register->verify_email = (object) array(
                "from"          => 'no-reply@spectrosinstruments.com',
                "subject"       => "Please Verify Your Email Address",
                "template"      => TEMPLATES."/registration/verify_email.html"
        );
        $_config->register->registration_notification = (object) array(
                "from"          => 'no-reply@spectrosinstruments.com',
                "subject"       => "[REGISTER]New customer registration submitted",
                "template"      => TEMPLATES."/registration/new_registration.html"
        );
        $_config->register->account_activation_notification = (object) array(
                "from"          => 'no-reply@spectrosinstruments.com',
                "subject"       => 'Your account has been activated',
                "template"      => TEMPLATES."/registration/account_activated.html"
        );

        $_config->engineering = new stdClass();
        $_config->engineering->internal_notification = (object) array(
                "from"          => 'no-reply@spectrosinstruments.com',
                "template"      => TEMPLATES."/engineering/internal_notification.html"
        );

        $_config->support = new stdClass();
        $_config->support->return_notification = (object) array(
                "from"          => 'no-reply@spectrosinstruments.com',
                "subject"       => 'Return Materials Authorization',
                "template"      => TEMPLATES."/support/return_authorized.html"
        );

        # Monitor Module Configurations
        $_config->monitor = new stdClass();
        $_config->monitor->collection_meta_keys = array(
                "name","location","customer","commodity","fumigant","temperature","temp_units","concentration","conc_units"
        );
        $_config->monitor->default_sensor_product = 'generic';
        $_config->monitor->default_dashboard = 'concept';

        # Spectros Module Configs
        $_config->spectros = new stdClass();
        $_config->spectros->calibration_product = 'cal_ver_credits';

        # Styles for Modules
        $_config->style = array(
                "action"        => "spectros",
                "monitor"       => "spectros",
                "contact"       => "spectros",
                "event"         => "spectros"
       );
