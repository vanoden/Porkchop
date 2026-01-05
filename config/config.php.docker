<?php

        define ( 'BASE', '/var/www/html' );
        define ( 'ENV', '' );
        define ( 'PATH', '' );
        define ( 'HTML', BASE.'/html' );
        define ( 'INCLUDES', BASE.'/'.ENV.'/includes' );
        define ( 'MODULES', BASE.'/'.ENV.'/modules' );
        define ( 'THIRD_PARTY', BASE.'/third_party/vendor' );
        define ( 'RESOURCES', BASE.'/resources' );
        define ( 'CLASS_PATH', BASE.'/classes' );
        define ( 'API_LOG', '/var/log/apache2/log');
        define ( 'APPLICATION_LOG', '/var/log/apache2/access.log');
        define ( 'APPLICATION_LOG_LEVEL', 'debug');
        define ( 'APPLICATION_LOG_TYPE', 'file');
        define ( 'TEMPLATES', BASE.'/templates' );	
        define ( 'REPORTS',BASE.'/reports' );	
        define ( 'SPECTROS_LOCATION_ID', 1);
        define ( 'SPECTROS_VENDOR_ID', 0);
        define ( 'ENVIROMENT', 'QA'); // 'DEV' / 'QA' / 'PRODUCTION'
        define ( 'QA_CAPTCHA_BYPASS', 'GZdWHjJJk9xHJXHFPeu4gZs6YDfcEXyb');
        define ( 'USE_OTP', true); // enable OTP optionsfor login

	
        # Initialize config object
        $_config = new stdClass();

        # Site Configurations
        $_config->site = new stdClass();
        $_config->site->default_index = "home";
        $_config->site->name = "SpectrosWWW";
        $_config->site->https = false;
        $_config->site->hostname = "kevin.spectrosinstruments.com";
        $_config->site->default_template = "default.html";
        $_config->site->support_email = 'service@spectrosinstruments.com';
        $_config->site->debug = true; // Enable debug mode to see detailed error information

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

        # Cache Mechanism (file, memcache, xcache)
        $_config->cache = new stdClass();
        $_config->cache->mechanism   = "memcached";
        $_config->cache->host   = "memcached";
        $_config->cache->port   = "11211";
        $_config->cache->path = "/tmp/spectros_cache";
        $_config->cache->default_expire_seconds = 3600;
        $_config->cache->retry_attempts = 3;
        $_config->cache->retry_delay = 100000; // microseconds

        # Database
        $_config->database = new stdClass();
        $_config->database->driver              = 'mysqli';
        $_config->database->schema              = 'docker';
        $_config->database->master = (object) array(
                'hostname'      => 'database',
                'username'      => "docker",
                'password'      => "docker",
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
        $_config->register->use_otp = USE_OTP;

        # Confirmation Email Info
        $_config->support = new stdClass();

        # OTP Configurations
        $_config->otp = new stdClass();
        $_config->otp->cookie = 'spectros_otp';
        $_config->otp->uri = 'https://api.qrserver.com/v1/create-qr-code/?color=FFF&bgcolor=000&data=[DATA]&qzone=2&margin=0&size=300x300&ecc=M';
        $_config->otp->label = 'spectrosinstruments.com';

        $_config->register->contact_us_confirmation = (object) array(
                "from"          => 'no-reply@spectrosinstruments.com',
                "subject"       => 'Welcome to Spectros Instruments',
                "channel"               => '',
                "template"      => TEMPLATES."/contact_us_confirm.html"
        );
        $_config->register->contact_us_notification = (object) array(
                "from"          => 'no-reply@spectrosinstruments.com',
                "subject"       => 'Contact Form Submitted',
                "channel"               => '',
                "template"      => TEMPLATES."/contact_us_notify.html"
        );
        $_config->register->forgot_password = (object) array(
                "from"          => 'no-reply@spectrosinstruments.com',
                "subject"       => 'Spectros Account Management',
                "channel"               => '',
                "template"      => TEMPLATES."/forgot_password.html"
        );
        $_config->register->verify_email = (object) array(
                "from"          => 'no-reply@spectrosinstruments.com',
                "subject"       => 'Verify your Email',
                "channel"               => '',
                "template"      => TEMPLATES."/registration/verify_email.html"
        );
        $_config->register->registration_notification = (object) array(
                "from"          => 'no-reply@spectrosinstruments.com',
                "subject"       => '[REGISTER]New customer registration submitted',
                "channel"       => '#support-testing',
                "template"      => TEMPLATES."/registration/new_registration.slack"
        );
        $_config->register->account_activation_notification = (object) array(
                "from"          => 'no-reply@spectrosinstruments.com',
                "subject"       => 'Your account has been activated',
                "channel"               => '',
                "template"      => TEMPLATES."/registration/account_activated.html"
        );
        $_config->register->backup_codes = (object) array(
                "template" => TEMPLATES . "/registration/backup_codes.html"
        );
        $_config->register->otp_recovery = (object) array(
                "template" => TEMPLATES . "/registration/otp_recovery.html"
        );
        $_config->register->password_reset_notification = (object) array(
                "from"          => 'no-reply@spectrosinstruments.com',
                "subject"       => "Your Password Has Been Reset",
                "template"      => TEMPLATES . "/registration/password_reset_notification.html"
        );
        $_config->register->otp_reset_notification = (object) array(
                "from"          => 'no-reply@spectrosinstruments.com',
                "subject"       => "Two-Factor Authentication Reset",
                "template"      => TEMPLATES . "/registration/otp_reset_notification.html"
        );
        $_config->register->backup_code_used_notification = (object) array(
                "from"          => 'no-reply@spectrosinstruments.com',
                "subject"       => "Account Backup Code was used",
                "template"      => TEMPLATES . "/registration/backup_code_used_notification.html"
        );
        
        $_config->engineering = new stdClass();
        $_config->engineering->internal_notification = (object) array(
                "from"          => 'no-reply@spectrosinstruments.com',
                "subject"       => '',
                "channel"               => '',
                "template"      => TEMPLATES."/engineering/internal_notification.html"
        );

        $_config->support->return_notification = (object) array(
                "from"          => 'no-reply@spectrosinstruments.com',
                "subject"       => 'Return Materials Authorization',
                "channel"               => '',
                "template"  => TEMPLATES."/support/return_authorized.html"
        );
        $_config->support->unassigned_action = (object) array(
            "from"      => 'no-reply@spectrosinstruments.com',
            "subject"       => 'Unassigned support request created',
            "channel"               => '',
            "template"      => TEMPLATES."/support/unassigned_action.html"
        );
        $_config->support->admin_monitor_request = (object) array(
            "from"      => 'no-reply@spectrosinstruments.com',
            "subject"   => '!generated by application!',
                "channel"               => '#support-testing',
                "template"      => TEMPLATES."/support/admin_monitor_request.slack"
        );
        $_config->support->admin_other_request = (object) array(
            "from"      => 'no-reply@spectrosinstruments.com',
            "subject"   => '!generated by application!',
                "channel"               => '#support-testing',
                "template"      => TEMPLATES."/support/admin_other_request.slack"
        );
        $_config->support->monitor_request = (object) array(
            "from"      => 'no-reply@spectrosinstruments.com',
            "subject"   => '!generated by application!',
                "channel"               => '',
                "template"      => TEMPLATES."/support/monitor_request.html"
        );
        $_config->support->other_request = (object) array(
            "from"      => 'no-reply@spectrosinstruments.com',
            "subject"   => '!generated by application!',
                "channel"               => '',
                "template"      => TEMPLATES."/support/other_request.html"
        );
        $_config->support->admin_notify = (object) array(
            "from"      => 'no-reply@spectrosinstruments.com',
            "subject"   => '!generated by application!',
            "template"  => TEMPLATES."/support/admin_notify.html",
        );
        $_config->support->new_request_notification = (object) array(
            "from"      => 'no-reply@spectrosinstruments.com',
            "subject"   => '!generated by application!',
            "template"  => TEMPLATES."/support/new_request_notification.html",
            "channel"   => '#support-requests',
        );
        $_config->support->ticket_update_notification = (object) array(
            "from"      =>'no-reply@spectrosinstruments.com',
            "subject"   => '!generated by application!',
                "channel"               => '',
                "template"      => TEMPLATES."/support/ticket_update_notification.html"
        );
        $_config->support->ticket_event_notification = (object) array(
            "from"      =>'no-reply@spectrosinstruments.com',
            "subject"   => '!generated by application!',
                "channel"               => '',
                "template"      => TEMPLATES."/support/ticket_event_notification.html"
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

        # Sales Config
        $_config->sales = new stdClass();
        $_config->sales->default_currency = 'Dollar';

        # Site Auditing Setup
        $_config->auditing = new stdClass();
        $_config->auditing->auditedClasses =  array (
                "Monitor\\Asset",
                "Monitor\\Dashboard",
                "Monitor\\SensorDefinition",
                "Product\\Item",
                "Product\\Instance",
                "Register\\Customer",
                "Register\\Privilege",
                "Register\\Role",
                "Register\\Tag",
                "Sales\\Order",
                "Sales\\Order\\Event",
                "Site\\Page",
                "Support\\Request\\Item"
        );
