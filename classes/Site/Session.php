<?php
	namespace Site;

use Detection\Exception\MobileDetectException;
use Register\Customer;

	class Session Extends \BaseModel {
	
		public $code = '';						// Unique session code
		public $order = 0;						// Active Order ID for the session
		public $customer;						// Customer associated with the session (DEPRECATED)
		public $company;						// Company associated with the session for Multi-Tenancy
		public $domain_id;						// Domain ID associated with the session
		public $refer_url = '';					// Referrer URL
		public $refer_domain = '';				// Referrer Domain
		public $browser = '';					// Browser information
		public $prev_session = 0;				// Previous session ID
		public $email_id = 0;					// Email ID associated with the session
		public $admin = 0;						// Admin ID associated with the session
		public $message = '';					// Message associated with the session
		public $status = 0;						// Status of the session
		public $first_hit_date;					// First hit date of the session
		public $last_hit_date;					// Last hit date of the session
		public $super_elevation_expires;		// Super elevation expiration date
		public $isMobile = false;				// Is the session from a mobile device?
		public $isRemovedAccount = false;		// Is the account removed?
		public $timezone = 'America/New_York';	// Timezone for the session, defaults to 'America/New_York'
		public $location_id;					// ID of the location associated with this session
		public $customer_id;					// ID of the customer associated with this session
		private $csrfToken;						// Anti-CSRF Token for the session
		private bool $otpVerified = false;		// Is One Time Password verified?
		private $cookie_name;					// Name of the session cookie
		private $cookie_domain;					// Domain for the session cookie
		private $cookie_expires;				// Expiration time for the session cookie
		private $cookie_path;					// Path for the session cookie
		private $elevated = false;				// Is the session elevated?
		private $oauth2_state = null;			// OAuth2 state for the session
		
		/**
		 * Constructor
		 * @param int $id 
		 * @return void 
		 */
		public function __construct($id = 0) {
			$this->_tableName = 'session_sessions';
			$this->_cacheKeyPrefix = 'session';
			parent::__construct($id);
		}

		/** @method public start()
		 * @brief Start or Continue a Session
		 * Find a session based on cookie, or create a new one
		 * @return void 
		 */
		public function start() {
			// Clear Previous Errors
			$this->clearError();

			// Get Site Location Matching Server Name
			$location = new \Company\Location();
			$location->getByHost($_SERVER['SERVER_NAME']);
			$this->location_id = $location->id;

			if (! $location->id) {
				$this->error("Location ".$_SERVER['SERVER_NAME']." not configured");
				return null;
			}

			// Get Domain ID for Location
			if (! $location->domain()->id) {
				$this->error("No domain assigned to location '".$location->id."'");
				return null;
			}

			// Get Domain Object using Location's Domain ID
			$domain = new \Company\Domain($location->domain()->id);
			if ($domain->error()) {
				$this->error("Error finding domain: ".$domain->error());
				return null;
			}
			if (! $domain->id) {
				$this->error("Domain '".$domain->id."' not found for location ".$this->location()->id);
				return null;
			}
			$this->domain_id = $domain->id;

			// Get Site Company (Multi-Tenancy) from Domain
			// Probably always going to be 1
			$this->company = new \Company\Company($domain->company_id);
			if ($this->company->error()) {
				$this->error("Error finding company: ".$this->company->error());
				return null;
			}
			if (! $this->company->id) {
				$this->error("Company '".$domain->company_id."' not found");
				return null;
			}

			// Cookie Parameters
			if (isset($GLOBALS['_config']->session->domain)) $this->cookie_domain = $GLOBALS['_config']->session->domain;
			else $this->cookie_domain = $domain;
			if (isset($GLOBALS['_config']->session->cookie) && is_string($GLOBALS['_config']->session->cookie)) $this->cookie_name = $GLOBALS['_config']->session->cookie;
			else $this->cookie_name = "session_code";
			if (isset($GLOBALS['_config']->session->expires)) $this->cookie_expires = time() + $GLOBALS['_config']->session->expires;
			else $this->cookie_expires = time() + 36000;
			$this->cookie_path = "/";

			// Load Code from Cookie
			if (isset($_COOKIE[$this->cookie_name])) $request_code = $_COOKIE[$this->cookie_name];

			// Does the provided session code look valid?
			if (isset($request_code) && $this->validCode($request_code)) {
				app_log("Getting session ".$request_code,'trace',__FILE__,__LINE__);

				// See if we have the ID cached for this code
				$cached_id = $this->getIDFromCodeCache($request_code);
				if (!empty($cached_id)) {
					$this->id = $cached_id;
					if ($this->details()) {
						app_log("Loaded session ID ".$this->id." from code cache",'trace2',__FILE__,__LINE__);
					}
					else {
						app_log("Failed to load session ID ".$this->id." from code cache",'notice',__FILE__,__LINE__);
						$this->id = null;
					}
				}
				else {
					app_log("Session code $request_code not found in code cache",'trace2',__FILE__,__LINE__);
				}

				// If not cached, load from database
				if (! $this->id) {
					# Get Existing Session Information
					if ($this->get($request_code)) {
						app_log("Loaded session ".$this->id." from database",'trace2',__FILE__,__LINE__);
					}
					else {
						app_log("Session $request_code not found in database",'notice',__FILE__,__LINE__);
					}
				}

				// If Session Loaded, Update Timestamp
				if (!empty($this->id)) {
					app_log("Loaded session ".$this->id.", customer ".$this->customer->id,'debug',__FILE__,__LINE__);
					$this->timestamp($this->id);
				} else {
					app_log("Session $request_code not available or expired, deleting cookie for ".$domain->name,'notice',__FILE__,__LINE__);
					setcookie($this->cookie_name, $request_code, time() - 604800, $this->cookie_path, $_SERVER['SERVER_NAME'],false,true);
				}

				// Set ID in Code Cache if loaded
				if (! $cached_id && ! empty($this->id)) {
					app_log("Setting session ID ".$this->id." in code cache for code $request_code",'trace',__FILE__,__LINE__);
					$this->setIDInCodeCache();
				}
			}
			elseif (isset($request_code)) {
				app_log("Invalid session code '$request_code' sent from client",'notice',__FILE__,__LINE__);
			}
			else {
				app_log("No session code sent from client",'debug',__FILE__,__LINE__);
			}
			$this->clearError();

			if (! $this->id) {
				// Create New Session
				$this->create();
			}

			// Authenticate Customer if Credentials Provided
			if (!empty($_REQUEST['login']) && ! preg_match('/_register/',$_SERVER['REQUEST_URI']) && (! $this->customer->id)) {
				// Initialize Vars
				$login = '';
				$password = '';
				$validationClass = new \Register\Customer();
				if (! $validationClass->validCode($_REQUEST['login'])) {
					$this->error("Invalid login code provided");
					return 0;
				}
				elseif (empty($_REQUEST['password']) || ! $validationClass->validPassword($_REQUEST['password'])) {
					$this->error("Invalid password provided");
					return 0;
				}
				$login = $_REQUEST['login'];
				$password = $_REQUEST['password'];

				$customer = new \Register\Customer();
				$customer->authenticate($login,$password);
					
				if ($customer->error()) {
					$this->error("Error authenticating customer: ".$customer->error());
					return 0;
				}
				if ($customer->message) {
					$this->message = $customer->message;
					return;
				}
				if ($customer->id) {
					$this->customer_id = $customer->id;

					// Set Timezone for session
					app_log("Customer $login [".$this->customer_id."] logged in",'info',__FILE__,__LINE__);
					app_log("TimeZone set to '".$customer->timezone."'",'debug',__FILE__,__LINE__);
					$this->update($this->id,array("user_id" => $this->customer_id,"timezone" => $customer->timezone));

					// Redirect to login target if provided
					if ($_REQUEST['login_target']) {
						header("location: ".PATH.$_REQUEST['login_target']);
						exit;
					}
				}
			}
		}

		/** @method end() 
		 * End the current Session
		 * @return bool 
		 */
		public function end() {
			// Clear Previous Errors
			$this->clearError();

			// Clear OTP verification cache
			$this->clearOTPVerified();

			// Initialize Database Service
			$database = new \Database\Service();

			// Prepare Query to End Session
			$end_session_query = "
				UPDATE	session_sessions
				SET		code = 'logout'
				WHERE	id = ?
			";

			// Bind Parameters
			$database->AddParam($this->id);

			// Execute Query
			$database->Execute($end_session_query);
			if ($database->ErrorMsg()) {
				$this->SQLError($database->ErrorMsg());
				return false;
			}

			// Delete Cookie
			app_log("Deleting session_code cookie '".$GLOBALS['_SESSION_']->code."' for ".$GLOBALS['_SESSION_']->domain()->name,'notice');
			setcookie("session_code","",time() - 3600);

			return true;
		}

		/** @method setIDInCodeCache()
		 * Store Session ID in Code Cache
		 * @return void
		 */
		public function setIDInCodeCache(): void {
			$code_cache = new \Cache\Item($GLOBALS['_CACHE_'],"session_code[".$this->code."]");
			$code_cache->set(['id' => $this->id]);
		}

		/** @method getIDFromCodeCache()
		 * Get Session ID from Code Cache
		 * @param code string Session Code
		 * @return int|null
		 */
		public function getIDFromCodeCache($code): ?int {
			$code_cache = new \Cache\Item($GLOBALS['_CACHE_'],"session_code[".$code."]");
			if ($code_cache->error()) {
				app_log("Cache error in Site::Session::getIDFromCodeCache(): ".$code_cache->error(),'error',__FILE__,__LINE__);
				return null;
			}
			$object = $code_cache->get();
			return $object["id"] ?? null;
		}

		/** @method elevate()
		 * Elevate current session, override Roles
		 * @return void 
		 */
		function elevate() {
			$this->elevated = true;
		}

		/** @method elevated()
		 * See if Session is Elevated
		 * @return bool 
		 */
		function elevated(): bool {
			return $this->elevated;
		}

		/** @method validCode(code)
		 * See if a Given Session code looks valid
		 * @param string $request_code
		 * @return bool 
		 */
		function validCode($request_code): bool {
			# Test to See Session Code is 32 character hexadecimal
			if (preg_match("/^[0-9a-f]{64}$/i",$request_code)) return true;
			return false;
		}

		/** @method create()
		 * Create a New Session Record and return Cookie
		 * @return object 
		 */
		function create() {
			$this->clearError();

			// Initialize Database Service
			$database = new \Database\Service();

			// Generate New Session Code
			// Confirm it is unique
			$new_code = '';
			while (! $new_code) {

				# Get Large Random value
				$randval = mt_rand();		
	
				# Use hash to further bury session id
				$new_code = hash('sha256',$randval);

				# Make Sure Session Code Not Already Used
				if ($this->code_in_use($new_code)) $new_code = "";
			}
			app_log("Generated session code '$new_code'");

			if (! is_object($this->customer)) $this->customer = new \Register\Customer();

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

			// Bind Parameters
			$database->AddParam($new_code);
			$database->AddParam($this->customer->id);
			$database->AddParam($this->company->id);
			$database->AddParam($this->refer_url);
			$database->AddParam($_SERVER['HTTP_USER_AGENT']);
			$database->AddParam($this->prev_session);
			$database->AddParam($this->email_id);

			// Execute Query
			$database->Execute($query);
			if ($database->ErrorMsg()) {
				$this->SQLError($database->ErrorMsg());
				return null;
			}
			$this->id = $database->Insert_ID();

			// Add Session Cookie to Response
			if (setcookie($this->cookie_name, $new_code, $this->cookie_expires,$this->cookie_path,$_SERVER['SERVER_NAME'],false,true)) {
				app_log("New Session ".$this->id." created for ".$this->domain()->id." expires ".date("Y-m-d H:i:s",time() + 36000),'debug',__FILE__,__LINE__);
				app_log("Session Code ".$new_code,'debug',__FILE__,__LINE__);
			}
			else {
				app_log("Could not set session cookie",'error',__FILE__,__LINE__);
			}

			// Store Session ID in Code Cache
			$this->setIDInCodeCache();

			return $this->get($new_code);
		}
		
		/** @method details()
		 * Get Session Information
		 * @return bool 
		 */
		function details(): bool {
			// Clear Previous Errors
			$this->clearError();

			# Cached Customer Object, Yay!
			$cache = $this->cache();
			if ($cache->error()) app_log("Cache error in Site::Session::get(): ".$cache->error(),'error',__FILE__,__LINE__);
			elseif (($this->id) and ($foundObject = $cache->get())) {
				if (!empty($foundObject) && !empty($foundObject->code)) {
					$this->code = $foundObject->code;
					$this->company = new \Company\Company($foundObject->company_id);
					$this->customer = new \Register\Customer($foundObject->customer_id);
					$this->timezone = $foundObject->timezone;
					$this->browser = $foundObject->browser;
					$this->first_hit_date = $foundObject->first_hit_date;
					$this->last_hit_date = $foundObject->last_hit_date;
					$this->super_elevation_expires = $foundObject->super_elevation_expires;
					$this->refer_url = $foundObject->refer_url;
					$this->oauth2_state = $foundObject->oauth2_state;

					// Non-database properties
					if (!empty($foundObject->otpVerified)) $this->otpVerified = $foundObject->otpVerified;
					if (isset($foundObject->isMobile)) $this->isMobile = $foundObject->isMobile;
					if (empty($foundObject->csrfToken)) {
						$foundObject->csrfToken = $this->generateCSRFToken();
						$cache->set($foundObject,600);
					}
					$this->csrfToken = $foundObject->csrfToken;
					
					// Handle OTP verification status from separate cache
					$this->cached(true);
					return true;
				}
			}

			// Initialize Database Service
			$database = new \Database\Service();

			// Prepare Query
			$get_session_query = "
				SELECT	id,
						code,
						company_id,
						user_id customer_id,
						timezone,
						browser,
						first_hit_date,
						last_hit_date,
						super_elevation_expires,
						refer_url,
						oauth2_state
				FROM	session_sessions
				WHERE	id = ?
			";

			// Bind Parameters
			$database->AddParam($this->id);

			// Execute Query
			$rs = $database->Execute($get_session_query);
			if (! $rs) {
				$this->SQLError($database->ErrorMsg());
				return false;
			}
			if ($object = $rs->FetchNextObject(false)) {
				app_log("Found session ".$this->id." in database",'trace2',__FILE__,__LINE__);
				if (empty($object->customer_id)) $object->customer_id = 0;

				// Populate Instance from Database Object
				$this->code = $object->code;
				$this->company = new \Company\Company($object->company_id);
				$this->customer = new \Register\Customer($object->customer_id);
				$this->timezone = $object->timezone;
				$this->browser = $object->browser;
				$this->first_hit_date = $object->first_hit_date;
				$this->last_hit_date = $object->last_hit_date;
				$this->super_elevation_expires = $object->super_elevation_expires;
				$this->refer_url = $object->refer_url;
				$this->oauth2_state = $object->oauth2_state;

				// Detect Mobile Device
				require_once THIRD_PARTY.'/psr/simple-cache/src/CacheInterface.php';
				require_once THIRD_PARTY.'/psr/cache/src/CacheItemInterface.php';
				require_once THIRD_PARTY.'/mobiledetect/mobiledetectlib/src/MobileDetect.php';
				require_once THIRD_PARTY.'/mobiledetect/mobiledetectlib/src/Cache/Cache.php';
				require_once THIRD_PARTY.'/mobiledetect/mobiledetectlib/src/Cache/CacheItem.php';	
				$detect = new \Detection\MobileDetect;

				if ($detect->isMobile())
					$this->isMobile = true;
				else
					$this->isMobile = false;

				// Generate CSRF Token if not already set
				$this->generateCSRFToken();
				
				// Initialize OTP verification status based on user requirements
				if ($GLOBALS['_config']->register->use_otp && $this->customer && $this->customer->id > 0) {
					// If user requires OTP, default to not verified
					$this->otpVerified = false;
				} else {
					// If user doesn't require OTP, default to verified
					$this->otpVerified = true;
				}
				$object->otpVerified = $this->otpVerified;

				$cache->set($object,3600);

				// Add to Code Cache
				$this->setIDInCodeCache();
				return true;
			}
			else {
				app_log("Session ".$this->id." not found in database",'notice',__FILE__,__LINE__);
			}
			return false;
		}

		/** @method customer()
		 * Get the associated customer object
		 * @return Customer 
		 */
		public function customer(): \Register\Customer {
			return $this->customer;
		}

		/** @method code_in_use(request_code)
		 * See if Code is already in use
		 * @param mixed $request_code 
		 * @return int 
		 */
		function code_in_use(string $request_code) {
			$session = new \Site\Session();
			$session->get($request_code);
			if ($session->code) return 1;
			return 0;
		}

		/** @method assign(customer_id, isElevated, OTPRedirect)
		 * Assign a session to a customer
		 * @param int $customer_id
		 * @param bool $isElevated
		 * @param string $OTPRedirect, @TODO using refer_url as the TOPRedirect required value for now
		 */		
		function assign(int $customer_id, bool $isElevated = false, $OTPRedirect = ''): bool {
			app_log("Assigning session ".$this->id." to customer ".$customer_id,'debug',__FILE__,__LINE__);

			// Clear Previous Errors
			$this->clearError();

			// Initialize Database Service
			$database = new \Database\Service();

			// Validate Customer ID
			$customer = new \Register\Customer($customer_id);
			if (! $customer->id) $this->error("Customer not found");

			// Set customer_id and customer object in the session
			$this->customer_id = $customer->id;
			$this->customer = $customer;

			$cache_key = "session[".$this->id."]";
			$cache = new \Cache\Item($GLOBALS['_CACHE_'],$cache_key);
			$cache->delete();
			
			// Clear OTP verification cache when assigning to new customer
			$this->clearOTPVerified();

			// Prepare Query to Check if User is Already Logged In
			$check_session_query = "
				SELECT  user_id
				FROM    session_sessions
				WHERE   id = ?
			";

			// Bind Parameters
			$database->AddParam($this->id);

			// Execute Query
			$rs = $database->Execute(
				$check_session_query
			);
			if (! $rs) {
				$this->SQLError($database->ErrorMsg());
				return false;
			}
			list($assigned_to) = $rs->FetchRow();
			if ($assigned_to > 0) {
				$this->error("Cannot register when already logged in.  Please <a href=\"/_register/logout\">log out</a> to continue.");
				return false;
			}

			// Clear Previous Bind Parameters
			$database->resetParams();

			// Prepare Query to Update Session with Customer Information
			$update_session_query = "
				UPDATE  session_sessions
				SET     user_id = ?,
						timezone = ?,
						refer_url = ?
				WHERE   id = ?
			";

			if (empty($OTPRedirect)) {
				$OTPRedirect = $this->refer_url; // Use refer_url if no OTPRedirect provided
			}

			// Bind Parameters
			$database->AddParam($customer->id);
			$database->AddParam($customer->timezone);
			$database->AddParam($OTPRedirect);
			$database->AddParam($this->id);

			$database->Execute(
				$update_session_query
			);

			if ($database->ErrorMsg()) {
				$this->SQLError($database->ErrorMsg());
				return false;
			}
			
			if ($isElevated) {
				// Prepare Query to Update Super Elevation Expiration
				$update_session_query = "
					UPDATE  session_sessions
					SET     super_elevation_expires = ?
					WHERE   id = ?
				";

				// Bind Parameters
				$database->AddParam(date('Y-m-d H:i:s',time() + 900)); // Set to expire in 15 minutes
				$database->AddParam($this->id);
				$database->Execute(
					$update_session_query
				);

				if ($database->ErrorMsg()) {
					$this->SQLError($database->ErrorMsg());
					return false;
				}
			}

			return $this->details($this->id);
		}

		/**
		 * Grant Full, Temporary, Admin Rights for Installation
		 * @return bool True if successfull
		 */
		function superElevate(): bool {
			return $this->update(array('super_elevation_expires' => date('Y-m-d H:i:s',time() + 900)));
		}

		/**
		 * Check if session is super elevated
		 * @return bool True if super elevated
		 */
		function superElevated(): bool {
			if ($this->super_elevation_expires < date('Y-m-d H:i:s')) return false;
			app_log($this->super_elevation_expires." vs ".date('Y-m-d H:i:s'),'notice');
			return true;
		}

		/** @method touch()
		 * Record last time session was touched
		 * @return bool True if successful, false otherwise
		 */
		function touch(): bool {
			return $this->timestamp();
		}

		/** @method timestamp()
		 * Record last time session was touched
		 * We will not kill the cache here to reduce load
		 * @return int Unix timestamp
		 */
		function timestamp(): bool {
			// Clear Previous Errors
			$this->clearError();

			// Initialize Database Service
			$database = new \Database\Service();

			// Prepare Query to Update Last Hit Date
			$update_session_query = "
				UPDATE	session_sessions
				SET		last_hit_date = sysdate()
				WHERE	id = ?
			";

			// Bind Parameters
			$database->AddParam($this->id);

			// Execute Query
			$rs = $database->Execute(
				$update_session_query,
			);
			if (! $rs) {
				$this->SQLError($database->ErrorMsg());
				return false;
			}
			return true;
		}

		/** @method update(parameters)
		 * Update session details
		 * @param array $parameters Key-value pairs of session attributes to update
		 * @return bool True if successful, false otherwise
		 */
		function update ($parameters = []): bool {
			// Clear any existing errors
			$this->clearError();

			// Clear Cache
			$this->clearCache();

			// Initialize Database Service
			$database = new \Database\Service();

			// Preserve OTP verification status before deleting cache
			$preservedOTPVerified = $this->otpVerified;

			# Make Sure User Has Privileges to view other sessions
			if ($GLOBALS['_SESSION_']->id != $this->id && ! $GLOBALS['_SESSION_']->customer->can('manage sessions')) {
				$this->error("No privileges to change session");
				return false;
			}

			$ok_params = array(
				"user_id"	=> "user_id",
				"timezone"	=> "timezone",
				"super_elevation_expires" => "super_elevation_expires",
				"refer_url" => "refer_url",
				"oauth2_state"	=> "oauth2_state"
			);

			$update_session_query = "
				UPDATE	session_sessions
				SET		id = id";

			foreach ($parameters as $parameter => $value) {
				if ($ok_params[$parameter]) {
					$update_session_query .= ",
						`$parameter` = ?";
					$database->AddParam($value);
				}
			}

			$update_session_query .= "
				WHERE	id = ?
			";
			$database->AddParam($this->id);

			$rs = $database->Execute($update_session_query);
			if (! $rs) {
				$this->SQLError($database->ErrorMsg());
				return false;
			}

			return $this->details();
		}

		/** @method hits(id)
		 * Get all hits for a session
		 * @param int $id Session ID, defaults to current session ID
		 * @return array|null Array of hits or null on error
		 */
		function hits(): array {
			$hitlist = new HitList();
			$hits = $hitlist->find(
				array(
					"session_id" => $this->id
				)
			);
			if ($hitlist->error()) {
				$this->error($hitlist->error());
				return [];
			}
			return $hits;
		}

		/** @method hit()
		 * Record a hit for the current session
		 * @return Returns true on success, false on error
		 */
		function hit() {
			$hit = new Hit();
			$hit->add(
				array(
					"session_id" => $this->id
				)
			);
			if ($hit->error()) {
				$this->error($hit->error());
				return false;
			}
			return true;
		}

		/** @method hitCount()
		 * Get the number of hits for the current session
		 * @return int Number of hits
		 */
		function hitCount(): ?int {
			$hitlist = new HitList();
			$hits = $hitlist->find(
				array(
					"session_id" => $this->id
				)
			);
			if ($hitlist->error()) {
				$this->error($hitlist->error());
				return null;
			}
			return count($hits);
		}

		/** @method last_hit()
		 * Get the last hit for a session
		 * @return  Last hit object or null if not found
		 */
		function last_hit() {
			$hitList = new HitList();
			$hit = $hitList->last(
				array("session_id" => $this->id)
			);
			return $hit;
		}

		/** @method first_hit()
		 * Get the first hit for a session
		 * @return First hit object or null if not found
		 */
		function first_hit() {
			$hitList = new HitList();
			$hit = $hitList->first(
				array("session_id" => $this->id)
			);
			return $hit;
		}

		/** @method expire()
		 * Expire a session by deleting it from the database
		 * @return bool True if successful, false otherwise
		*/
		public function expire(): bool {
			// Clear any existing errors
			$this->clearError();
	
			// Prepare Database Service
			$database = new \Database\Service();

			// Check if session ID is set
			if (empty($this->id)) {
				$this->error("Session ID is not set for session::Session::expire");
				return false;
			}

			// Validate session ID
			if (! is_numeric($this->id)) {
				$this->error("Invalid session id for session::Session::expire");
				return false;
			}

			// Delete Hits
			$delete_hits_query = "
				DELETE
				FROM	session_hits
				WHERE	session_id = ?
			";
			$database->execute($delete_hits_query,array($this->id));
			if ($database->ErrorMsg()) {
				$this->SQLError($database->ErrorMsg());
				return false;
			}

			// Prepare Query to delete the session
			$delete_session_query = "
				DELETE
				FROM	session_sessions
				WHERE	id = ?
			";

			// Bind Parameters
			$database->AddParam($this->id);

			// Execute Query
			$database->execute($delete_session_query);
			if ($database->ErrorMsg()) {
				$this->SQLError($database->ErrorMsg());
				return false;
			}
			return true;
		}

		/** @method authenticated()
		 * Check if the session is authenticated
		 * @return bool True if authenticated, false otherwise
		 */
		public function authenticated(): bool {
			app_log("=== AUTHENTICATED() METHOD CALL ===", 'debug', __FILE__, __LINE__, 'otplogs');
			app_log("OTP Enabled: " . ($GLOBALS['_config']->register->use_otp ? 'true' : 'false'), 'debug', __FILE__, __LINE__, 'otplogs');
			app_log("Customer ID: " . ($this->customer->id ?? 'null'), 'debug', __FILE__, __LINE__, 'otplogs');
			app_log("Customer requires OTP: " . ($this->customer->requiresOTP() ? 'true' : 'false'), 'debug', __FILE__, __LINE__, 'otplogs');
			$otpStatus = $this->getOTPVerified();
			app_log("OTP verified status: " . ($otpStatus === false ? 'false' : ($otpStatus === true ? 'true' : 'null')), 'debug', __FILE__, __LINE__, 'otplogs');
			app_log("Current URI: " . $_SERVER['REQUEST_URI'], 'debug', __FILE__, __LINE__, 'otplogs');
			
			if ($GLOBALS['_config']->register->use_otp && isset($this->customer->id) && $this->customer->requiresOTP() && $this->customer->id > 0 && $this->getOTPVerified() === false) {
				// If OTP is required and not verified, redirect to OTP page
				// But don't redirect if we're already on the OTP page to prevent loops
				if (!preg_match('/\/_register\/otp/', $_SERVER['REQUEST_URI'])) {
					app_log("Customer logged in but OTP not verified, redirecting to OTP page",'debug',__FILE__,__LINE__, 'otplogs');
					header("Location: /_register/otp?target=".urlencode($_SERVER['REQUEST_URI']));
					exit;
				} else {
					app_log("Already on OTP page, not redirecting to prevent loop",'debug',__FILE__,__LINE__, 'otplogs');
				}
			}
			if (isset($this->customer->id) && $this->customer->id > 0) {
				app_log("Authentication successful", 'debug', __FILE__, __LINE__, 'otplogs');
				return true;
			} else {
				app_log("Authentication failed - no customer ID", 'debug', __FILE__, __LINE__, 'otplogs');
				return false;
			}
		}

		/** @method isUser(user_id)
		 * Check if the session belongs to a specific user
		 * @param int $user_id User ID to check against
		 * @return bool True if the session belongs to the user, false otherwise
		 */
		public function isUser($user_id): bool {
			if (!empty($this->customer) && $this->customer->id == $user_id) return true;
			return false;
		}

		/** @method isOrganization(organization_id)
		 * Check if the session belongs to a specific organization
		 * @param int $organization_id Organization ID to check against
		 * @return bool True if the session belongs to the organization, false otherwise
		 */
		public function isOrganization($organization_id): bool {
			if (!empty($this->customer) && !empty($this->customer->organization) && $this->customer->organization()->id == $organization_id) return true;
			return false;
		}

		/** @method isMobileBrowser(useragent)
		 * Determine whether a user is browsing with a mobile device via user agent string
		 * @param string $useragent User agent string to check
		 * @return bool True if mobile browser, false otherwise
		 */
		public function isMobileBrowser($useragent): bool {
			if(preg_match('/(android|bb\d+|meego).+mobile|avantgo|bada\/|blackberry|blazer|compal|elaine|fennec|hiptop|iemobile|ip(hone|od)|iris|kindle|lge |maemo|midp|mmp|mobile.+firefox|netfront|opera m(ob|in)i|palm( os)?|phone|p(ixi|re)\/|plucker|pocket|psp|series(4|6)0|symbian|treo|up\.(browser|link)|vodafone|wap|windows ce|xda|xiino/i',$useragent)||preg_match('/1207|6310|6590|3gso|4thp|50[1-6]i|770s|802s|a wa|abac|ac(er|oo|s\-)|ai(ko|rn)|al(av|ca|co)|amoi|an(ex|ny|yw)|aptu|ar(ch|go)|as(te|us)|attw|au(di|\-m|r |s )|avan|be(ck|ll|nq)|bi(lb|rd)|bl(ac|az)|br(e|v)w|bumb|bw\-(n|u)|c55\/|capi|ccwa|cdm\-|cell|chtm|cldc|cmd\-|co(mp|nd)|craw|da(it|ll|ng)|dbte|dc\-s|devi|dica|dmob|do(c|p)o|ds(12|\-d)|el(49|ai)|em(l2|ul)|er(ic|k0)|esl8|ez([4-7]0|os|wa|ze)|fetc|fly(\-|_)|g1 u|g560|gene|gf\-5|g\-mo|go(\.w|od)|gr(ad|un)|haie|hcit|hd\-(m|p|t)|hei\-|hi(pt|ta)|hp( i|ip)|hs\-c|ht(c(\-| |_|a|g|p|s|t)|tp)|hu(aw|tc)|i\-(20|go|ma)|i230|iac( |\-|\/)|ibro|idea|ig01|ikom|im1k|inno|ipaq|iris|ja(t|v)a|jbro|jemu|jigs|kddi|keji|kgt( |\/)|klon|kpt |kwc\-|kyo(c|k)|le(no|xi)|lg( g|\/(k|l|u)|50|54|\-[a-w])|libw|lynx|m1\-w|m3ga|m50\/|ma(te|ui|xo)|mc(01|21|ca)|m\-cr|me(rc|ri)|mi(o8|oa|ts)|mmef|mo(01|02|bi|de|do|t(\-| |o|v)|zz)|mt(50|p1|v )|mwbp|mywa|n10[0-2]|n20[2-3]|n30(0|2)|n50(0|2|5)|n7(0(0|1)|10)|ne((c|m)\-|on|tf|wf|wg|wt)|nok(6|i)|nzph|o2im|op(ti|wv)|oran|owg1|p800|pan(a|d|t)|pdxg|pg(13|\-([1-8]|c))|phil|pire|pl(ay|uc)|pn\-2|po(ck|rt|se)|prox|psio|pt\-g|qa\-a|qc(07|12|21|32|60|\-[2-7]|i\-)|qtek|r380|r600|raks|rim9|ro(ve|zo)|s55\/|sa(ge|ma|mm|ms|ny|va)|sc(01|h\-|oo|p\-)|sdk\/|se(c(\-|0|1)|47|mc|nd|ri)|sgh\-|shar|sie(\-|m)|sk\-0|sl(45|id)|sm(al|ar|b3|it|t5)|so(ft|ny)|sp(01|h\-|v\-|v )|sy(01|mb)|t2(18|50)|t6(00|10|18)|ta(gt|lk)|tcl\-|tdg\-|tel(i|m)|tim\-|t\-mo|to(pl|sh)|ts(70|m\-|m3|m5)|tx\-9|up(\.b|g1|si)|utst|v400|v750|veri|vi(rg|te)|vk(40|5[0-3]|\-v)|vm40|voda|vulc|vx(52|53|60|61|70|80|81|83|85|98)|w3c(\-| )|webc|whit|wi(g |nc|nw)|wmlb|wonu|x700|yas\-|your|zeto|zte\-/i',substr($useragent,0,4)))
				return true;
			else
				return false;
		}

		/** @method localtime(timestamp)
		 * Get local time fields for a given timestamp
		 * @param int $timestamp Unix timestamp, defaults to current time
		 * @return array Associative array with keys: timestamp, year, month, day, hour, minute, second, timezone
		 */
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

		/** @method oauthState(state)
		 * Get or set the OAuth2 state for the session
		 * @param string|null $state The state to set, or null to get the current state
		 * @return string The current OAuth2 state
		 */
		public function oauthState($state = null) {
			if (isset($state)) $this->update(array('oauth2_state' => $state));
			return $this->oauth2_state;
		}

		/** @method unsetOAuthState()
		 * Unset the OAuth2 state for the session
		 * @return bool True if successful, false otherwise
		 */
		public function unsetOAuthState() {
			$this->update(array('oauth2_state' => ''));
			return true;
		}

		/** @method verifyCSRFToken(token)
		 * Verify an Anti-CSRF token against the session's stored token
		 * @param string $csrfToken The CSRF token to verify
		 * @return bool True if the token matches, false otherwise
		 */
		public function verifyCSRFToken($csrfToken) {
			if (empty($csrfToken)) {
				app_log("No csrfToken provided",'debug');
				return false;
			}
			if (empty($this->csrfToken)) {
				app_log("No csrfToken exists for session",'debug');
				return false;
			}
			if ($this->csrfToken != $csrfToken) {
				app_log("csrfToken provided doesn't match session",'debug');
				return false;
			}
			return true;
		}

		/** @method generateCSRFToken()
		 * Generate a new CSRF token for the session
		 * @return string The generated CSRF token
		 */
		private function generateCSRFToken() {
			$data = bin2hex(openssl_random_pseudo_bytes(32));
			$token = htmlspecialchars($data, ENT_QUOTES | ENT_HTML401, 'UTF-8');
			app_log("Generated token '$token'",'debug');
			return $token;
		}

		public function getCSRFToken() {
			if (empty($this->csrfToken)) {
				$this->csrfToken = $this->generateCSRFToken();
				$cache = $this->cache();
				$cache->setElement('csrfToken', $this->csrfToken);
			}
			return $this->csrfToken;
		}

		/**
		 * Set OTP verification status in separate cache with 2-hour expiration
		 * @param bool $verified
		 * @return bool
		 */
		public function setOTPVerified(bool $verified): bool {
			$this->otpVerified = $verified;
		
			// Use separate cache key for OTP verification with 2-hour expiration (7200 seconds)
			$cache = $this->cache();
			$cache->setElement('otpVerified', $verified);

			app_log("OTP verification status updated in separate cache: " . ($verified ? 'true' : 'false'), 'debug', __FILE__, __LINE__, 'otplogs');
			return true;
		}

		/**
		 * Load OTP verification status from separate cache
		 * @return bool|null
		 */
		private function loadOTPVerifiedFromCache(): ?bool {
			$cache = $this->cache();
			$foundObject = $cache->get();
			if ($foundObject !== null && isset($foundObject->otpVerified)) {
				$this->otpVerified = $foundObject->otpVerified;
				app_log("OTP verification status loaded from session cache: " . ($this->otpVerified ? 'true' : 'false'), 'debug', __FILE__, __LINE__, 'otplogs');
				return $this->otpVerified;
			}
			else {
				return false;
			}
		}

		/**
		 * Get OTP verification status from cache
		 * @return bool|null
		 */
		public function getOTPVerified(): ?bool {
			return $this->otpVerified;
		}

		/**
		 * Check if OTP is verified (returns true if null - no OTP required)
		 * @return bool
		 */
		public function isOTPVerified(): bool {
			// If OTP verification is null, assume no OTP required (verified)
			return $this->otpVerified !== false;
		}

		/**
		 * Clear OTP verification status from cache
		 * @return bool
		 */
		public function clearOTPVerified(): bool {
			$cache = $this->cache();
			$cache->setElement('otpVerified', false);
			
			app_log("OTP verification status cleared from cache", 'debug', __FILE__, __LINE__, 'otplogs');
			return true;
		}

		public function location() {
			return new \Company\Location($this->location_id);
		}

		public function domain() {
			return new \Company\Domain($this->domain_id);
		}
	}
