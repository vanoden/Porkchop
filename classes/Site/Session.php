<?php
	namespace Site;

	class Session {
		public $code = '';
		public $id = 0;
		public $order = 0;
		public $customer;
		public $company;
		public $domain = '';
		public $refer_url = '';
		public $refer_domain = '';
		public $browser = '';
		public $prev_session = 0;
		public $email_id = 0;
		public $admin = 0;
		public $error = '';
		public $message = '';
		public $status = 0;
		public $first_hit_date;
		public $last_hit_date;
		public $isMobile = false;
		public $isRemovedAccount = false;
		private $cookie_name;
		private $cookie_domain;
		private $cookie_expires;
		private $cookie_path;
		private $_cached = 0;
		private $elevated = 0;

		public function __construct($id = 0) {
			if ($id > 0) {
				$this->id = $id;
				$this->details();
			}
		}

		public function cached() {
			return $this->_cached;
		}

		public function start() {
			# Fetch Company Information
			$this->location = new \Company\Location();
			$this->location->getByHost($_SERVER['SERVER_NAME']);
			
			if (! $this->location->id) {
				$this->error = "Location ".$_SERVER['SERVER_NAME']." not configured";
				return null;
			}
			if (! $this->location->domain->id) {
				$this->error = "No domain assigned to location '".$this->location->id."'";
				return null;
			}

			$this->domain = new \Company\Domain($this->location->domain->id);
			if ($this->domain->error) {
				$this->error = "Error finding domain: ".$this->domain->error;
				return null;
			}
			if (! $this->domain->id) {
				$this->error = "Domain '".$this->domain->id."' not found for location ".$this->location->id;
				return null;
			}
			
			$this->company = new \Company\Company($this->domain->company->id);
			if ($this->company->error) {
				$this->error = "Error finding company: ".$this->company->error;
				return null;
			}
			if (! $this->company->id) {
				$this->error = "Company '".$this->domain->company->id."' not found";
				return null;
			}

			# Cookie Parameters
			if (isset($GLOBALS['_config']->session->domain)) $this->cookie_domain = $GLOBALS['_config']->session->domain;
			else $this->cookie_domain = $this->domain;
			if (isset($GLOBALS['_config']->session->cookie) && is_string($GLOBALS['_config']->session->cookie)) $this->cookie_name = $GLOBALS['_config']->session->cookie;
			else $this->cookie_name = "session_code";
			if (isset($GLOBALS['_config']->session->expires)) $this->cookie_expires = time() + $GLOBALS['_config']->session->expires;
			else $this->cookie_expires = time() + 36000;
			$this->cookie_path = "/";

			# Store Code from Cookie
			if (isset($_COOKIE[$this->cookie_name])) $request_code = $_COOKIE[$this->cookie_name];

			# Was a 'Valid looking' Session Given
			if (isset($request_code) && $this->valid_code($request_code)) {
				# Get Existing Session Information
				$this->get($request_code);
				if ($this->id) {
					app_log("Loaded session ".$this->id.", customer ".$this->customer->id,'debug',__FILE__,__LINE__);
					$this->timestamp($this->id);
				}
				else {
					app_log("Session $request_code not available or expired, deleting cookie for ".$this->domain->name,'notice',__FILE__,__LINE__);
					setcookie($this->cookie_name, $request_code, time() - 604800, $this->cookie_path, $_SERVER['HTTP_HOST']);
				}
			}
			elseif (isset($request_code)) {
				app_log("Invalid session code '$request_code' sent from client",'notice',__FILE__,__LINE__);
			}
			else {
				app_log("No session code sent from client",'debug',__FILE__,__LINE__);
			}

			if (! $this->id) {
				# Create New Session
				$this->add();
			}

			# Authentication
			if (($_SERVER['REQUEST_URI'] != '/_register/') and (! $this->customer->id)) {
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
				if ($this->customer->id) {
					app_log("Customer $login [".$this->customer_id."] logged in",'info',__FILE__,__LINE__);
					app_log("TimeZone set to '".$customer->timezone."'",'debug',__FILE__,__LINE__);
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
		# Remove this function after testing
		function companyremoveme() {
			# Get Domain Name
			preg_match("/(\w+\.\w+)\$/",$_SERVER["HTTP_HOST"],$matches);
			$domain_name = $matches[1];

			$cache_key = "domain[".$domain_name."]";

			$cache = new \Cache\Item($GLOBALS['_CACHE_'],$cache_key);
			if ($cache->error) {
				app_log("Error in cache mechanism: ".$cache->error,'error',__FILE__,__LINE__);
			}

			# Cached Customer Object, Yay!
			if ($domain = $cache->get()) {
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
			if ($GLOBALS['_database']->ErrorMsg()) {
				$this->error = "Error getting domain information: ".$GLOBALS['_database']->ErrorMsg();
				return null;
			}
			else {
				if ($rs->RecordCount() > 0) {
					$domain = $rs->FetchRow();
					$domain = (object) $domain;

					$this->company = $domain->company_id;
					$this->location = $domain->location_id;
					$this->domain = $domain->domain_name;
					$this->status = $domain->status;

					$cache->set($domain);
					return $domain;
				}
				else {
					$this->error = "Company not configured for $domain_name";
					return null;
				}
			}
		}

		# Override Roles
		function elevate() {
			$this->elevated = 1;
		}

		function elevated() {
			return $this->elevated;
		}

		# See if a Given Session code looks valid
		function valid_code ($request_code) {
			# Test to See Session Code is 32 character hexadecimal
			if (preg_match("/^[0-9a-f]{64}$/i",$request_code)) return true;
			#error_log("Invalid session code: $request_code");
			return false;
		}

		# Create a New Session Record and return Cookie
		function add() {
			$new_code = '';
			while (! $new_code) {
				# Get Large Random value
				$randval = mt_rand();		
	
				# Use hash to further bury session id
				$new_code = hash('sha256',$randval);

				# Make Sure Session Code Not Already Used
				if ($this->code_in_use($new_code)) $new_code = "";
			}

			if (! is_object($this->customer)) {
				$this->customer = new \Register\Customer();
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
				(		?,
						sysdate(),
						sysdate(),
						?,
						?,
						?,
						?,
						?,
						?
				)
			";
			$rs = $GLOBALS['_database']->Execute(
				$query,
				array($new_code,
					  $this->customer->id,
					  $this->company->id,
					  $this->refer_url,
					  $_SERVER['HTTP_USER_AGENT'],
					  $this->prev_session,
					  $this->email_id
				)
			);
			if ($GLOBALS['_database']->ErrorMsg()) {
				$this->error = "Error creating session: ".$GLOBALS['_database']->ErrorMsg();
				return null;
			}
			$this->id = $GLOBALS['_database']->Insert_ID();

			# Set Session Cookie
			if (setcookie($this->cookie_name, $new_code, $this->cookie_expires,$this->cookie_path,$_SERVER['HTTP_HOST'])) {
				app_log("New Session ".$this->id." created for ".$this->domain->id." expires ".date("Y-m-d H:i:s",time() + 36000),'debug',__FILE__,__LINE__);
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
			if (! $rs) {
				app_log("SQL Error in Session::Session::get(): ".$GLOBALS['_database']->ErrorMsg(),'error',__FILE__,__LINE__);
				return null;
			}
			list($this->id) = $rs->FetchRow();
			return $this->details();
		}
		function details() {
			# Name For Xcache Variable
			$cache_key = "session[".$this->id."]";

			# Cached Customer Object, Yay!
			$cache = new \Cache\Item($GLOBALS['_CACHE_'],$cache_key);
			if ($cache->error) {
				app_log("Cache error in Site::Session::get(): ".$cache->error,'error',__FILE__,__LINE__);
			}
			elseif (($this->id) and ($session = $cache->get())) {
				if ($session->code) {
					$this->code = $session->code;
					$this->company = new \Company\Company($session->company_id);
					$this->customer = new \Register\Customer($session->customer_id);
					$this->timezone = $session->timezone;
					$this->browser = $session->browser;
					$this->first_hit_date = $session->first_hit_date;
					$this->last_hit_date = $session->last_hit_date;
                    if (isset($session->isMobile)) $this->isMobile = $session->isMobile;
					$this->_cached = 1;
					return $this->code;
				}
			}

			$get_session_query = "
				SELECT	id,
						code,
						company_id,
						user_id customer_id,
						timezone,
						browser,
						first_hit_date,
						last_hit_date
				FROM	session_sessions
				WHERE	id = ?
			";
			$rs = $GLOBALS['_database']->Execute(
				$get_session_query,
				array($this->id)
			);
			if (! $rs) {
				$this->error = "SQL Error in Session::Session::details(): ".$GLOBALS['_database']->ErrorMsg();
				return;
			}
			if ($rs->RecordCount()) {
				$session = $rs->FetchNextObject(false);

				$this->code = $session->code;
				$this->company = new \Company\Company($session->company_id);
				$this->customer = new \Register\Customer($session->customer_id);
				$this->timezone = $session->timezone;
				$this->browser = $session->browser;
				$this->first_hit_date = $session->first_hit_date;
				$this->last_hit_date = $session->last_hit_date;

                require_once THIRD_PARTY.'/mobiledetect/mobiledetectlib/Mobile_Detect.php';
                $detect = new \Mobile_Detect;

                if ($detect->isMobile())
                    $this->isMobile = true;
                else
                    $this->isMobile = false;

				if ($session->id) $cache->set($session,600);
				return $session;
			}
		}

		function code_in_use ($request_code) {
			$session = new \Site\Session();
			$session->get($request_code);
			if ($session->code) return 1;
			return 0;
		}
		
		function assign ($customer_id) {
			app_log("Assigning session ".$this->id." to customer ".$customer_id,'debug',__FILE__,__LINE__);

			$customer = new \Register\Customer($customer_id);
			if (! $customer->id) {
				$this->error = "Customer not found";
			}

			$cache_key = "session[".$this->id."]";
			$cache = new \Cache\Item($GLOBALS['_CACHE_'],$cache_key);
			$cache->delete();

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
				SET     user_id = ?,
						timezone = ?
				WHERE   id = ?
			";
			$GLOBALS['_database']->Execute(
				$update_session_query,
				array(
					  $customer->id,
					  $customer->timezone,
					  $this->id
				)
			);
			if ($GLOBALS['_database']->ErrorMsg()) {
				$this->error = "SQL Error updating session: ".$GLOBALS['_database']->ErrorMsg();
				return null;
			}
			#if ($parameters["user_id"]) $this->customer = $parameters["user_id"];
			return $this->details($this->id);
		}
		function touch() {
			$this->timestamp();
		}
		function timestamp() {
			$update_session_query = "
				UPDATE	session_sessions
				SET		last_hit_date = sysdate()
				WHERE	id = ?
			";

			$rs = $GLOBALS['_database']->Execute(
				$update_session_query,
				array($this->id)
			);
			if (! $rs) {
				$this->error = "SQL Error in Session::timestamp: ".$GLOBALS['_database']->ErrorMsg();
				return null;
			}
			#if ($parameters["user_id"]) $this->customer = $parameters["user_id"];
			return 1;
		}
		function update ($parameters) {
			# Name For Xcache Variable
			$cache_key = "session[".$this->id."]";
			if ($cache) $cache->unset();
			app_log("Unset cache key $cache_key",'debug',__FILE__,__LINE__);

			# Make Sure User Has Privileges to view other sessions
			if (! $GLOBALS['_SESSION_']->customer->has_role('session manager')) {
				$this->error = "No privileges to change session";
				return null;
			}

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
				$this->error = "SQL Error in Session::Session::update(): ".$GLOBALS['_database']->ErrorMsg();
				return null;
			}

			return $this->details();
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
			$hit = new Hit();
			$hit->add(
				array(
					"session_id" => $this->id
				)
			);
			if ($hit->error) {
				$this->error = $hit->error;
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
			if (isset($this->customer->id) && $this->customer->id > 0) return true;
			else return false;
		}

        public function isMobileBrowser($useragent) {
            if(preg_match('/(android|bb\d+|meego).+mobile|avantgo|bada\/|blackberry|blazer|compal|elaine|fennec|hiptop|iemobile|ip(hone|od)|iris|kindle|lge |maemo|midp|mmp|mobile.+firefox|netfront|opera m(ob|in)i|palm( os)?|phone|p(ixi|re)\/|plucker|pocket|psp|series(4|6)0|symbian|treo|up\.(browser|link)|vodafone|wap|windows ce|xda|xiino/i',$useragent)||preg_match('/1207|6310|6590|3gso|4thp|50[1-6]i|770s|802s|a wa|abac|ac(er|oo|s\-)|ai(ko|rn)|al(av|ca|co)|amoi|an(ex|ny|yw)|aptu|ar(ch|go)|as(te|us)|attw|au(di|\-m|r |s )|avan|be(ck|ll|nq)|bi(lb|rd)|bl(ac|az)|br(e|v)w|bumb|bw\-(n|u)|c55\/|capi|ccwa|cdm\-|cell|chtm|cldc|cmd\-|co(mp|nd)|craw|da(it|ll|ng)|dbte|dc\-s|devi|dica|dmob|do(c|p)o|ds(12|\-d)|el(49|ai)|em(l2|ul)|er(ic|k0)|esl8|ez([4-7]0|os|wa|ze)|fetc|fly(\-|_)|g1 u|g560|gene|gf\-5|g\-mo|go(\.w|od)|gr(ad|un)|haie|hcit|hd\-(m|p|t)|hei\-|hi(pt|ta)|hp( i|ip)|hs\-c|ht(c(\-| |_|a|g|p|s|t)|tp)|hu(aw|tc)|i\-(20|go|ma)|i230|iac( |\-|\/)|ibro|idea|ig01|ikom|im1k|inno|ipaq|iris|ja(t|v)a|jbro|jemu|jigs|kddi|keji|kgt( |\/)|klon|kpt |kwc\-|kyo(c|k)|le(no|xi)|lg( g|\/(k|l|u)|50|54|\-[a-w])|libw|lynx|m1\-w|m3ga|m50\/|ma(te|ui|xo)|mc(01|21|ca)|m\-cr|me(rc|ri)|mi(o8|oa|ts)|mmef|mo(01|02|bi|de|do|t(\-| |o|v)|zz)|mt(50|p1|v )|mwbp|mywa|n10[0-2]|n20[2-3]|n30(0|2)|n50(0|2|5)|n7(0(0|1)|10)|ne((c|m)\-|on|tf|wf|wg|wt)|nok(6|i)|nzph|o2im|op(ti|wv)|oran|owg1|p800|pan(a|d|t)|pdxg|pg(13|\-([1-8]|c))|phil|pire|pl(ay|uc)|pn\-2|po(ck|rt|se)|prox|psio|pt\-g|qa\-a|qc(07|12|21|32|60|\-[2-7]|i\-)|qtek|r380|r600|raks|rim9|ro(ve|zo)|s55\/|sa(ge|ma|mm|ms|ny|va)|sc(01|h\-|oo|p\-)|sdk\/|se(c(\-|0|1)|47|mc|nd|ri)|sgh\-|shar|sie(\-|m)|sk\-0|sl(45|id)|sm(al|ar|b3|it|t5)|so(ft|ny)|sp(01|h\-|v\-|v )|sy(01|mb)|t2(18|50)|t6(00|10|18)|ta(gt|lk)|tcl\-|tdg\-|tel(i|m)|tim\-|t\-mo|to(pl|sh)|ts(70|m\-|m3|m5)|tx\-9|up(\.b|g1|si)|utst|v400|v750|veri|vi(rg|te)|vk(40|5[0-3]|\-v)|vm40|voda|vulc|vx(52|53|60|61|70|80|81|83|85|98)|w3c(\-| )|webc|whit|wi(g |nc|nw)|wmlb|wonu|x700|yas\-|your|zeto|zte\-/i',substr($useragent,0,4)))
                return 1;
            else
                return 0;
        }

		public function localtime($timestamp = 0) {
			if ($timestamp == 0) $timestamp = time();
			$datetime = new \DateTime('@'.$timestamp,new \DateTimeZone('UTC'));
			$datetime->setTimezone(new \DateTimeZone($this->timezone));
			return array(
				'timestamp'		=> $timestamp,
				'year'			=> $datetime->format('Y'),
				'month'			=> $datetime->format('m'),
				'day'			=> $datetime->format('d'),
				'hour'			=> $datetime->format('H'),
				'minute'		=> $datetime->format('i'),
				'second'		=> $datetime->format('s'),
				'timezone'		=> $this->timezone
			);
		}
	}
