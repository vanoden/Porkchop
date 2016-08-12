<?php
	class Session {
		public $code = '';
		public $id = 0;
		public $order = 0;
		public $customer_id = 0;
		public $company = 0;
		public $domain = '';
		public $refer_url = '';
		public $refer_domain = '';
		public $browser = '';
		public $prev_session;
		public $email_id = 0;
		public $admin = 0;
		public $error = '';
		public $message = '';
		public $status = 0;
		private $cookie_name;
		private $cookie_domain;
		private $cookie_expires;
		private $cookie_path;

		public function __construct($id = 0) {
			$schema = new SessionSchema();
			if ($schema->error) {
				$this->error = "Failed to initialize schema: ".$schema->error;
			}

			# Fetch Company Information
			$this->company();

			# See if we're in site-down
			if (! $this->company) app_log("Company not found!",'error',__FILE__,__LINE__);

			if ($this->error || ! $this->status) {
				if ($this->error) app_log("Error initializing session: ".$this->error,'error',__FILE__,__LINE__);
				else app_log("Site status is '".$this->status."'",'notice',__FILE__,__LINE__);
				header("location: /site-down.html");
				exit;
			}

			if ($id) {
				$this->details($id);
			}
		}
		public function load() {
			# Cookie Parameters
			if (isset($GLOBALS['_config']->session->domain)) $this->cookie_domain = $GLOBALS['_config']->session->domain;
			else $this->cookie_domain = $this->domain;
			if (isset($GLOBALS['_config']->session->cookie)) $this->cookie_name = $GLOBALS['_config']->session->cookie;
			else $this->cookie_name = "session_code";
			if (isset($GLOBALS['_config']->session->expires)) $this->cookie_expires = time() + $GLOBALS['_config']->session->expires;
			else $this->cookie_expires = time() + 36000;
			$this->cookie_path = "/";

			# Store Code from Cookie
			$request_code = $_COOKIE[$this->cookie_name];

			# Was a 'Valid looking' Session Given
			if ($this->valid_code($request_code)) {
				# Get Existing Session Information
				$this->get($request_code);
				if ($this->id) {
					app_log("Loaded session ".$this->id.", customer ".$this->customer_id,'debug',__FILE__,__LINE__);
					$this->timestamp($this->id);
				}
				else {
					app_log("Session $request_code not available or expired, deleting cookie for ".$this->domain,'notice',__FILE__,__LINE__);
					setcookie($this->cookie_name, $request_code, time() - 604800, $this->cookie_path, $this->cookie_domain);
				}
			}
			elseif ($request_code) {
				app_log("Invalid session code '$request_code' sent from client",'notice',__FILE__,__LINE__);
			}
			else {
				app_log("No session code sent from client",'debug',__FILE__,__LINE__);
			}

			if (! $this->id) {
				# Create New Session
				$this->create();
			}

			# Create Hit Record
			$this->hit();

			# Authentication
			if (($_SERVER['REQUEST_URI'] != '/_register/') and (! $this->customer_id)) {
				# Initialize Vars
				$login = '';
				$password = '';
				if (array_key_exists('login',$_REQUEST)) {
					$login = $_REQUEST['login'];
					$password = $_REQUEST['password'];
				}
				if ($login) {
					$customer = new Customer;
					$customer->authenticate($login,$password);
					if ($customer->error) {
						$this->error = "Error authenticating customer: ".$customer->error;
						error_log("Failed: ".$this->error);
						return 0;
					}
					if ($customer->message) {
						$this->message = $customer->message;
						return;
					}
					if ($customer->id) {
						$this->customer_id = $customer->id;
					}
				}
				if ($this->customer_id) {
					app_log("Customer $login [".$this->customer_id."] logged in");
					$this->update($this->id,array("user_id" => $this->customer_id,"timezone" => $customer->timezone));

					if ($_REQUEST['login_target']) {
						header("location: ".PATH.$_REQUEST['login_target']);
						exit;
					}
				}
			}
		}

		# End a Session
		public function end() {
			$end_session_query = "
				UPDATE	session_sessions
				SET		code = 'logout'
				WHERE	id = ".$this->id;

			$GLOBALS['_database']->Execute($end_session_query);
			if ($GLOBALS['_database']->ErrorMsg())
			{
				$this->error = "SQL Error in session::Session::end: ".$GLOBALS['_database']->ErrorMsg();
				return 0;
			}
		}

		# Get Company Information Based on Request Domain
		function company () {
			# Get Domain Name
			preg_match("/(\w+\.\w+)\$/",$_SERVER["HTTP_HOST"],$matches);
			$domain_name = $matches[1];

			$cache_key = "domain[".$domain_name."]";

			# Cached Customer Object, Yay!
			if ($domain = cache_get($cache_key))
			{
				$this->company = $domain->company_id;
				$this->location = $domain->location_id;
				$this->domain = $domain->domain_name;
				$this->status = $domain->status;
				$domain->_cached = 1;
				return $domain;
			}

			# Domain Name
			$get_company_query = "
				SELECT	company_id,
						location_id,
						domain_name,
						status
				FROM	company_domains
				WHERE	domain_name = '$domain_name'
			";

			$rs = $GLOBALS['_database']->Execute($get_company_query);
			if ($GLOBALS['_database']->ErrorMsg())
			{
				$this->error = "Error getting domain information: ".$GLOBALS['_database']->ErrorMsg();
				return null;
			}
			else
			{
				if ($rs->RecordCount() > 0)
				{
					$domain = $rs->FetchRow();
					$domain = (object) $domain;

					$this->company = $domain->company_id;
					$this->location = $domain->location_id;
					$this->domain = $domain->domain_name;
					$this->status = $domain->status;

					cache_set($cache_key,$domain);
					return $domain;
				}
				else
				{
					$this->error = "Company not configured for $domain_name";
					return null;
				}
			}
		}

		# See if a Given Session code looks valid
		function valid_code ($request_code) {
			# Test to See Session Code is 32 character hexadecimal
			if (preg_match("/^[0-9a-f]{64}$/i",$request_code)) return true;
			#error_log("Invalid session code: $request_code");
			return false;
		}

		# Create a New Session Record and return Cookie
		function create() {
			$new_code = '';
			while (! $new_code) {
				# Get Large Random value
				$randval = mt_rand();		
	
				# Use hash to further bury session id
				$new_code = hash('sha256',$randval);

				# Make Sure Session Code Not Already Used
				if ($this->code_in_use($new_code)) $new_code = "";
			}

			# Create The New Session
			$query = "
				INSERT
				INTO	session_sessions
				(		code,
						first_hit_date,
						last_hit_date,
						user_id,
						company_id,
						refer_url,
						browser,
						prev_session,
						e_id
				)
				VALUES
				(		'$new_code',
						sysdate(),
						sysdate(),
						'$this->customer_id',
						'$this->company',
						'$this->refer_url',
						".$GLOBALS['_database']->qstr($_SERVER['HTTP_USER_AGENT'],get_magic_quotes_gpc()).",
						'$this->prev_session',
						'$this->email_id'
				)
				";
			$rs = $GLOBALS['_database']->Execute($query);
			if ($GLOBALS['_database']->ErrorMsg())
			{
				$this->error = "Error creating session: ".$GLOBALS['_database']->ErrorMsg();
				return null;
			}
			$this->id = $GLOBALS['_database']->Insert_ID();

			# Set Session Cookie
			if (setcookie($this->cookie_name, $new_code, $this->cookie_expires,$this->cookie_path,$this->cookie_domain))
			{
				app_log("New Session ".$this->id." created for ".$this->domain." expires ".date("Y-m-d H:i:s",time() + 36000),'debug',__FILE__,__LINE__);
				app_log("Session Code ".$new_code,'debug',__FILE__,__LINE__);
			}
			else{
				app_log("Could not set session cookie",'error',__FILE__,__LINE__);
			}
			return $this->get($new_code);
		}

		function get($code) {
			$get_object_query = "
				SELECT	id
				FROM	session_sessions
				WHERE	code = ?
			";
			$rs = $GLOBALS['_database']->Execute(
				$get_object_query,
				array($code)
			);
			if (! $rs)
			{
				app_log("Error loading session: ".$GLOBALS['_database']->ErrorMsg(),'error',__FILE__,__LINE__);
				return null;
			}
			list($this->id) = $rs->FetchRow();
			return $this->details($this->id);
		}

		function find($parameters = array()) {
			$find_objects_query = "
				SELECT	id
				FROM	session_sessions
				WHERE	company_id = '".$this->company."'
			";

			if (isset($parameters['code']) and preg_match('/^\w+$/',$parameters['code'])) {
				$find_objects_query .= "
				AND		code = ".$GLOBALS['_database']->qstr($parameters['code'],get_magic_quotes_gpc());
			}
			if (isset($parameters['expired'])) {
				$find_objects_query .= "
				AND		last_hit_date < sysdate() - 86400
				";
			}
			if (isset($parameters['user_id']) && preg_match('/^\d+$/',$parameters['user_id'])) {
				$find_objects_query .= "
				AND		user_id = ".$parameters['user_id'];
			}
			if (isset($parameters['date_start']) && get_mysql_date($parameters['date_start'])) {
				$threshold = get_mysql_date($parameters['date_start']);
				$find_objects_query .= "
					AND	last_hit_date >= '$threshold'
				";
			}

			$rs = $GLOBALS['_database']->Execute($find_objects_query);
			if (! $rs) {
				$this->error = "Error finding session: ".$GLOBALS['_database']->ErrorMsg();
				return null;
			}
			$objects = array();
			while (list($id) = $rs->FetchRow()) {
				array_push($objects,$this->details($id));
			}
			return $objects;
		}

		function details($id) {
			# Name For Xcache Variable
			$cache_key = "session[".$id."]";
			cache_unset($cacke_key);

			# Cached Customer Object, Yay!	
			if (($id) and ($session = cache_get($cache_key))) {
				if ($session->code) {
					$this->code = $session->code;
					$this->company = $session->company;
					$this->customer_id = $session->customer_id;
					$this->timezone = $session->timezone;
					$this->browser = $session->browser;
					$this->first_hit_date = $session->first_hit_date;
					$this->last_hit_date = $session->last_hit_date;
					$this->_cached = 1;

					return array($this->id,$this->code);
				}
			}

			$get_session_query = "
				SELECT	code,
						company_id company,
						user_id customer_id,
						timezone,
						browser,
						first_hit_date,
						last_hit_date
				FROM	session_sessions
				WHERE	id = '$id'
			";
			$rs = $GLOBALS['_database']->Execute($get_session_query);
			if (! $rs) {
				$this->error = "Error getting session details: ".$GLOBALS['_database']->ErrorMsg();
				return;
			}
			if ($rs->RecordCount()) {
				$session = $rs->FetchNextObject(false);

				$this->code = $session->code;
				$this->company = $session->company;
				$this->customer_id = $session->customer_id;
				$this->timezone = $session->timezone;
				$this->browser = $session->browser;
				$this->first_hit_date = $session->first_hit_date;
				$this->last_hit_date = $session->last_hit_date;

				if ($id) cache_set($cache_key,$session,600);
				return $session;
			}
		}

		function code_in_use ($request_code) {
			$session = $this->get($request_code);
			if ($session->code) return 1;
			return 0;
		}
		
		function assign ($customer_id) {
			app_log("Assigning session ".$this->id." to customer ".$customer_id,'debug',__FILE__,__LINE__);

			$cache_key = "session[".$this->id."]";
			cache_unset($cache_key);

			$check_session_query = "
				SELECT  user_id
				FROM    session_sessions
				WHERE   id = ?
			";
			$rs = $GLOBALS['_database']->Execute(
				$check_session_query,
				array($this->id)
			);
			if (! $rs) {
				$this->error = "SQL Error checking for session in Session::assign: ".$GLOBALS['_database']->ErrorMsg();
				return null;
			}
			list($assigned_to) = $rs->FetchRow();
			if ($assigned_to > 0) {
				$this->error = "Cannot register when already logged in.  Please <a href=\"/_register/logout\">log out</a> to continue.";
				return null;
			}
			$update_session_query = "
				UPDATE  session_sessions
				SET     user_id = ?
				WHERE   id = ?
			";
			$GLOBALS['_database']->Execute(
				$update_session_query,
				array(
					  $customer_id,
					  $this->id
				)
			);
			if ($GLOBALS['_database']->ErrorMsg())
			{
				$this->error = "SQL Error updating session: ".$GLOBALS['_database']->ErrorMsg();
				return null;
			}
			#if ($parameters["user_id"]) $this->customer = $parameters["user_id"];
			return $this->details($this->id);
		}
		function timestamp($id) {
			$update_session_query = "
				UPDATE	session_sessions
				SET		last_hit_date = sysdate()
				WHERE	id = ?
			";

			$rs = $GLOBALS['_database']->Execute(
				$update_session_query,
				array($id)
			);
			if (! $rs)
			{
				$this->error = "SQL Error in Session::timestamp: ".$GLOBALS['_database']->ErrorMsg();
				return null;
			}
			#if ($parameters["user_id"]) $this->customer = $parameters["user_id"];
			return 1;
		}
		function update ($id,$parameters) {
			# Name For Xcache Variable
			$cache_key = "session[".$id."]";
			cache_unset($cache_key);
			app_log("Unset cache key $cache_key",'debug',__FILE__,__LINE__);

			# Make Sure User Has Privileges to view other sessions
			$_customer = new Customer();
			if (! role('session manager')) {
				$id = $this->id;
			}
			$cache_key = "session[".$this->id."]";
			cache_unset($cache_key);

			$ok_params = array(
				"user_id"	=> "user_id",
				"timezone"	=> "timezone"
			);

			$update_session_query = "
				UPDATE	session_sessions
				SET		id = id";

			foreach ($parameters as $parameter => $value) {
				if ($ok_params[$parameter]) {
					$update_session_query .= ",
						`$parameter` = '$value'";
				}
			}

			$update_session_query .= "
				WHERE	id = ?
			";

			if (isset($_GLOBALS['_config']->log_queries))
				app_log(preg_replace("/(\n|\r)/","",preg_replace("/\t/"," ",$update_session_query)),'debug',__FILE__,__LINE__);

			$rs = $GLOBALS['_database']->Execute(
				$update_session_query,
				array($id)
			);
			if (! $rs) {
				$this->error = "SQL Error in Session::update: ".$GLOBALS['_database']->ErrorMsg();
				return null;
			}

			return $this->details($id);
		}
		function hit() {
			$_hit = new SessionHit();
			$hit = $_hit->add(
				array(
					"session_id" => $this->id
				)
			);
			if ($_hit->error)
			{
				$this->error = $_hit->error;
				return null;
			}
			return 1;
		}
		function last_hit($session_id) {
			$_hit = new SessionHit();
			return $_hit->find(
				array(
					"session_id" => $session_id,
					"_limit"	=> 1,
					"_sort"		=> 'id'
				)
			);
		}
		public function expire_session($session_id) {
			if (! preg_match('/^\d+$/',$session_id))
			{
				$this->error = "Invalid session id for session::Session::expire_session";
				return 0;
			}
			# Delete Hits
			$delete_hits_query = "
				DELETE
				FROM	session_hits
				WHERE	session_id = '$session_id'
			";
			$GLOBALS['_database']->execute($delete_hits_query);
			if ($GLOBALS['_database']->ErrorMsg())
			{
				$this->error = "SQL Error in session::Session::expire_session: ".$GLOBALS['_database']->ErrorMsg();
				return 0;
			}

			# Delete Session
			$delete_session_query = "
				DELETE
				FROM	session_sessions
				WHERE	session_id = '$session_id'
			";
			$GLOBALS['_database']->execute($delete_session_query);
			if ($GLOBALS['_database']->ErrorMsg())
			{
				$this->error = "SQL Error in session::Session::expire_session: ".$GLOBALS['_database']->ErrorMsg();
				return 0;
			}
		}
	}
	class SessionHit {
		public $error;
		public $id;
		
		function __construct() {
			$this->error = '';
			$schema = new SessionSchema();
			if ($schema->error) {
				$this->error = "Failed to initialize schema: ".$schema->error;
			}
		}
		function add($parameters = array()) {
			if (! $parameters['session_id']) {
				$this->error = "session_id required for SessionHit::add";
				return null;
			}
			if (isset($_SERVER['HTTPS']) and $_SERVER['HTTPS']) $secure = 1;
			else $secure = 0;

			$insert_hit_query = "
				INSERT
				INTO	session_hits
				(		session_id,
						hit_date,
						remote_ip,
						secure,
						script,
						query_string
				)
				VALUES
				(		?,sysdate(),?,?,?,?
				)
			";
			$GLOBALS['_database']->Execute(
				$insert_hit_query,
				array(
					$parameters['session_id'],
					$_SERVER['REMOTE_ADDR'],
					$secure,
					$_SERVER['SCRIPT_NAME'],
					$_SERVER['REQUEST_URI']
				)
			);
			if ($GLOBALS['_database']->ErrorMsg()) {
				$this->error = "SQL Error in SessionHit::add: ".$GLOBALS['_database']->ErrorMsg();
				return null;
			}
			return 1;
		}
		function find($parameters = array()) {
			$find_objects_query .= "
				SELECT	id
				FROM	session_hits
				WHERE	id = id
			";

			if ($parameters['session_id'])
				$find_objects_query .= "
					AND	session_id = ".$GLOBALS['_database']->qstr($parameters['session_id'],get_magic_quotes_gpc);
			$find_objects_query .= "
				ORDER BY id desc
			";
			if (preg_match('/^\d+$/',$parameters['_limit']))
				$find_objects_query .= "
					limit ".$parameters['_limit'];
			$rs = $GLOBALS['_database']->Execute($find_objects_query);
			if (! $rs) {
				$this->error = "SQL Error in SessionHit::find: ".$GLOBALS['_database']->ErrorMsg();
				return null;
			}
			$hits = array();
			while (list($id) = $rs->FetchRow()) {
				array_push($hits,$this->details($id));
			}
			return $hits;
		}
		function details($id) {
			$get_object_query = "
				SELECT	*
				FROM	session_hits
				WHERE	id = ?
			";
			
			$rs = $GLOBALS['_database']->Execute(
				$get_object_query,
				array($id)
			);
			if (! $rs)
			{
				$this->error = "SQL Error in SessionHit::details: ".$GLOBALS['_database']->ErrorMsg();
				return null;
			}
			return $rs->FetchNextObject(false);
		}
	}

	class SessionSchema {
		public $errno;
		public $error;
		public $module = "Session";
		
		public function __construct() {
			$this->upgrade();
		}
		public function version() {
			# See if Schema is Available
			$schema_list = $GLOBALS['_database']->MetaTables();
			$info_table = strtolower($this->module)."__info";

			if (! in_array($info_table,$schema_list)) {
                # Create __info table
                $create_table_query = "
                    CREATE TABLE `$info_table` (
                        label   varchar(100) not null primary key,
                        value   varchar(255)
                    )
                ";
                $GLOBALS['_database']->Execute($create_table_query);
                if ($GLOBALS['_database']->ErrorMsg()) {
                    $this->error = "SQL Error creating info table in ".$this->module."Schema::version: ".$GLOBALS['_database']->ErrorMsg();
                    return null;
                }
            }

            # Check Current Schema Version
            $get_version_query = "
                SELECT  value
                FROM    `$info_table`
                WHERE   label = 'schema_version'
            ";

            $rs = $GLOBALS['_database']->Execute($get_version_query);
            if (! $rs) {
                $this->error = "SQL Error in ".$this->module."::version: ".$GLOBALS['_database']->ErrorMsg();
                return null;
            }

            list($version) = $rs->FetchRow();
            if (! $version) $version = 0;
            return $version;
		}
		public function upgrade() {
			$this->error = '';
			$info_table = strtolower($this->module)."__info";

			# See if Schema is Available
			$schema_list = $GLOBALS['_database']->MetaTables();

			if (! in_array($info_table,$schema_list)) {
				# Create company__info table
				$create_table_query = "
					CREATE TABLE `$info_table` (
						label	varchar(100) not null primary key,
						value	varchar(255)
					)
				";
				$GLOBALS['_database']->Execute($create_table_query);
				if ($GLOBALS['_database']->ErrorMsg()) {
					$this->error = "SQL Error creating info table in ".$this->module."Schema::upgrade: ".$GLOBALS['_database']->ErrorMsg();
					return null;
				}
			}

			# Check Current Schema Version
			$get_version_query = "
				SELECT	value
				FROM	`$info_table`
				WHERE	label = 'schema_version'
			";

			$rs = $GLOBALS['_database']->Execute($get_version_query);
			if (! $rs) {
				$this->error = "SQL Error in ".$this->module."Schema::upgrade: ".$GLOBALS['_database']->ErrorMsg();
				return null;
			}

			list($current_schema_version) = $rs->FetchRow();

			if ($current_schema_version < 1) {
				app_log("Upgrading session schema to version 1",'notice',__FILE__,__LINE__);
				$update_schema_query = "
					INSERT
					INTO	session__info
					VALUES	('schema_version',1)
					ON DUPLICATE KEY UPDATE
							value = 1
				";
				$GLOBALS['_database']->Execute($update_schema_query);
				if ($GLOBALS['_database']->ErrorMsg()) {
					$this->error = "SQL Error creating _info table in ".$this->module."Schema::upgrade: ".$GLOBALS['_database']->ErrorMsg();
					return null;
				}
				$current_schema_version = 1;
				$update_schema_version = "
					UPDATE	session__info
					SET		value = $current_schema_version
					WHERE	label = 'schema_version'
				";
				$GLOBALS['_database']->Execute($update_schema_version);
				if ($GLOBALS['_database']->ErrorMsg()) {
					$this->error = "SQL Error in ".$this->module."Schema::upgrade: ".$GLOBALS['_database']->ErrorMsg();
					return null;
				}
			}
			if ($current_schema_version < 2)
			{
				app_log("Upgrading ".$this->module." schema to version 2",'notice',__FILE__,__LINE__);
				$create_table_query = "
					CREATE TABLE IF NOT EXISTS `session_sessions` (
					  `active` int(1) NOT NULL DEFAULT '1',
					  `id` int(10) NOT NULL AUTO_INCREMENT,
					  `code` varchar(32) NOT NULL DEFAULT '',
					  `user_id` int(6) DEFAULT NULL,
					  `last_hit_date` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
					  `first_hit_date` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
					  `browser` varchar(255) DEFAULT NULL,
					  `company_id` int(5) NOT NULL DEFAULT '0',
					  `c_id` int(8) DEFAULT NULL,
					  `e_id` int(8) DEFAULT NULL,
					  `prev_session` varchar(100) NOT NULL DEFAULT '',
					  `refer_url` text,
					  PRIMARY KEY (`id`),
					  KEY `company_id` (`company_id`,`user_id`),
					  KEY `code` (`code`),
					  KEY `end_time` (`last_hit_date`),
					  KEY `idx_active` (`company_id`,`active`,`id`,`user_id`),
					  FOREIGN KEY `fk_company_id` (`company_id`) REFERENCES `company_companies` (`id`)
					)
				";
				$GLOBALS['_database']->Execute($create_table_query);
				if ($GLOBALS['_database']->ErrorMsg()) {
					$this->error = "SQL Error creating contact types table in ".$this->module."Schema::upgrade: ".$GLOBALS['_database']->ErrorMsg();
					return null;
				}
				$create_table_query = "
					CREATE TABLE IF NOT EXISTS `session_hits` (
					  `id` int(10) NOT NULL AUTO_INCREMENT,
					  `session_id` int(10) NOT NULL DEFAULT '0',
					  `server_id` int(11) NOT NULL DEFAULT '0',
					  `hit_date` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
					  `remote_ip` varchar(20) DEFAULT NULL,
					  `secure` int(1) NOT NULL DEFAULT '0',
					  `script` varchar(100) NOT NULL DEFAULT '',
					  `query_string` text,
					  `order_id` int(8) NOT NULL DEFAULT '0',
					  `module_id` int(3) NOT NULL,
					  PRIMARY KEY (`id`),
					  KEY `session_id` (`session_id`)
					)
				";
				$GLOBALS['_database']->Execute($create_table_query);
				if ($GLOBALS['_database']->ErrorMsg()) {
					$this->error = "SQL Error creating contact types table in ".$this->module."Schema::upgrade: ".$GLOBALS['_database']->ErrorMsg();
					return null;
				}
				$current_schema_version = 2;
				$update_schema_version = "
					UPDATE	`$info_table`
					SET		value = $current_schema_version
					WHERE	label = 'schema_version'
				";
				$GLOBALS['_database']->Execute($update_schema_version);
				if ($GLOBALS['_database']->ErrorMsg()) {
					$this->error = "SQL Error in ".$this->module."Schema::upgrade: ".$GLOBALS['_database']->ErrorMsg();
					return null;
				}
			}
			if ($current_schema_version < 3) {
				app_log("Upgrading ".$this->module." schema to version 3",'notice',__FILE__,__LINE__);
				$create_table_query = "
					ALTER TABLE `session_sessions` MODIFY `code` char(64) NOT NULL DEFAULT ''
				";
				$GLOBALS['_database']->Execute($create_table_query);
				if ($GLOBALS['_database']->ErrorMsg()) {
					$this->error = "SQL Error altering sessions table in ".$this->module."Schema::upgrade: ".$GLOBALS['_database']->ErrorMsg();
					return null;
				}
				$current_schema_version = 3;
				$update_schema_version = "
					UPDATE	`$info_table`
					SET		value = $current_schema_version
					WHERE	label = 'schema_version'
				";
				$GLOBALS['_database']->Execute($update_schema_version);
				if ($GLOBALS['_database']->ErrorMsg()) {
					$this->error = "SQL Error in ".$this->module."Schema::upgrade: ".$GLOBALS['_database']->ErrorMsg();
					return null;
				}
			}
			if ($current_schema_version < 4) {
				app_log("Upgrading ".$this->module." schema to version 4",'notice',__FILE__,__LINE__);
				$create_table_query = "
					ALTER TABLE `session_sessions` ADD `timezone` varchar(32) NOT NULL DEFAULT 'America/New_York'
				";
				$GLOBALS['_database']->Execute($create_table_query);
				if ($GLOBALS['_database']->ErrorMsg()) {
					$this->error = "SQL Error adding timezone to sessions table in ".$this->module."Schema::upgrade: ".$GLOBALS['_database']->ErrorMsg();
					return null;
				}
				$current_schema_version = 4;
				$update_schema_version = "
					UPDATE	`$info_table`
					SET		value = $current_schema_version
					WHERE	label = 'schema_version'
				";
				$GLOBALS['_database']->Execute($update_schema_version);
				if ($GLOBALS['_database']->ErrorMsg()) {
					$this->error = "SQL Error in ".$this->module."Schema::upgrade: ".$GLOBALS['_database']->ErrorMsg();
					return null;
				}
			}
		}
	}
?>
