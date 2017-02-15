<?php
	namespace Site;

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
		private $_cached = 0;

		public function __construct($id = 0) {
			$schema = new Schema();
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
					$customer = new \Register\Customer();
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
			return $this->details();
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

		function details() {
			$id = $this->id;
			# Name For Xcache Variable
			$cache_key = "session[".$id."]";

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

					return $this->code;
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
			$_customer = new \Register\Customer();
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
		function hits($id = 0) {
			if ($id < 1) $id = $this->id;
			$hitlist = new HitList();
			$hits = $hitlist->find(
				array(
					"session_id" => $id
				)
			);
			if ($hitlist->error) {
				$this->error = $hitlist->error;
				return null;
			}
			return $hits;
		}
		function hit() {
			$_hit = new Hit();
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
			$_hit = new Hit();
			return $_hit->find(
				array(
					"session_id" => $session_id,
					"_limit"	=> 1,
					"_sort"		=> 'id'
				)
			);
		}
		public function expire_session($session_id) {
			if (! preg_match('/^\d+$/',$session_id)) {
				$this->error = "Invalid session id for session::Session::expire_session";
				return null;
			}
			# Delete Hits
			$delete_hits_query = "
				DELETE
				FROM	session_hits
				WHERE	session_id = '$session_id'
			";
			$GLOBALS['_database']->execute($delete_hits_query);
			if ($GLOBALS['_database']->ErrorMsg()) {
				$this->error = "SQL Error in session::Session::expire_session: ".$GLOBALS['_database']->ErrorMsg();
				return null;
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
				return null;
			}
		}
		public function authenticated() {
			if (isset($this->customer->id) && $this->customer->id > 0) return 1;
			else return 0;
		}
	}
?>