<?php
    namespace Site;
    
    class Page Extends \BaseModel {
	    public string $module = 'content';
	    public string $view = 'index';
	    public string $index = '';
	    public string $style = 'default';
	    public bool $auth_required = false;
	    public bool $ssl_required = false;
		public string $method = "";
	    public string $uri = "";
	    public string $title = "";
	    public $metadata;
	    public string $template = "";
	    public ?string $success = null;
		public string $instructions = "";
		public ?int $tou_id = null;
		public string $sitemap = "";
		private $_breadcrumbs = array();
	    private $_errors = array();
		private $_warnings = array();

	    public function __construct() {
			$this->_tableName = "page_pages";
			$this->_tableUKColumn = null;
			$this->_cacheKeyPrefix = "site.page";
			$this->_metaTableName = "page_metadata";
			$this->_tableMetaFKColumn = "page_id";
			$this->_tableMetaKeyColumn = "key";
            parent::__construct();
            
		    $args = func_get_args();
			if (func_num_args() == 1 && gettype($args[0]) == "integer") {
				$this->id = $args[0];
				$this->details();
			}
			elseif (func_num_args() == 1 && gettype( $args [0] ) == "string") {
			    $this->id = $args [0];
			    $this->details();
		    }
			elseif (func_num_args() == 1 && gettype($args [0] ) == "array") {
			    if (isset($args[0]['method'])) {
				    $this->method = $args[0]['method'];
					if ($this->validView($args[0]['view'])) $this->view = $args[0]['view'];
					if ($this->validIndex($args[0]['index'])) $this->index = $args[0]['index'];
			    }
		    }
			elseif (func_num_args() == 2 && gettype( $args[0] ) == "string" && gettype( $args[1] ) == "string" && isset( $args[2])) {
			    $this->getPage( $args[0], $args[1], $args[2] );
		    }
			elseif (func_num_args() == 2 && gettype( $args[0] ) == "string" && gettype( $args[1] ) == "string") {
			    $this->getPage( $args[0], $args[1] );
		    }
			else {
			    $this->fromRequest();
		    }
			$this->_addFields('tou_id');
	    }

		public function __call($name, $arguments) {
			if ($name == "get") return $this->getPage($arguments[0],$arguments[1],$arguments[2]);
			else if ($name == "setMetadata") return $this->setMetadataScalar($arguments[0],$arguments[1]);
			else $this->error("Method '$name' not found");
		}

	    public function fromRequest() {
		    return $this->getPage($GLOBALS['_REQUEST_']->module, $GLOBALS['_REQUEST_']->view, $GLOBALS['_REQUEST_']->index );
	    }
	    public function applyStyle() {
		    if (isset ( $GLOBALS ['_config']->style [$this->module()] )) $this->style = $GLOBALS['_config']->style[$this->module()];
	    }

		/** @method requireAuth()
		 * Check if the user is authenticated.  If not, redirect to the login page.
		 */
	    public function requireAuth(): bool {
			if ($this->module == 'register' && $this->view == 'login') return true;
		    if (! $GLOBALS['_SESSION_']->authenticated()) {
				$counter = new \Site\Counter("auth_redirect");
				$counter->increment();
				app_log("User not authenticated, redirecting to login page",'info');
			    header('location: /_register/login?target='.urlencode($_SERVER['REQUEST_URI']));
                exit;
				return false;	// Never gets here ;-)
		    }
			return true;
	    }

	    public function requireSuperElevation() {
		    if (! $GLOBALS ['_SESSION_']->customer->is_super_elevated()) {
				$counter = new \Site\Counter("auth_redirect");
				$counter->increment();
			    header('location: /_register/login?target=' . urlencode ( $_SERVER ['REQUEST_URI'] ) );
                exit;
		    }
	    }

	    public function requireRole($role) {	 
		    $this->requireAuth();
		    if ($this->module == 'register' && $this->view == 'login') {
			    // Do Nothing, we're Here
		    }
			elseif (! $GLOBALS ['_SESSION_']->customer->id) {
				$counter = new \Site\Counter("auth_redirect");
				$counter->increment();
			    header('location: /_register/login?target=' . urlencode ( $_SERVER ['REQUEST_URI'] ) );
			    exit;
		    }
			elseif (! $GLOBALS ['_SESSION_']->customer->has_role($role)) {
				$counter = new \Site\Counter("permission_denied");
				$counter->increment();
			    header('location: /_register/permission_denied' );
			    exit;
		    }
			else {
				return true;
			}
	    }

        public function requirePrivilege($privilege) {
		$this->requireAuth();
            if ($GLOBALS['_SESSION_']->customer->can($privilege)) {
				$counter = new \Site\Counter("auth_redirect");
				$counter->increment();
                return true;
            }
            elseif (!isset($GLOBALS['_SESSION_']->customer->id)) {
				$counter = new \Site\Counter("auth_redirect");
				$counter->increment();
				header('location: /_register/login?target=' . urlencode ( $_SERVER ['REQUEST_URI'] ) );
			    exit;
		    }
            else {
				$counter = new \Site\Counter("permission_denied");
				$counter->increment();
			    header('location: /_register/permission_denied' );
                exit;
			}
        }

		public function requireOrganization() {
			if (empty($GLOBALS['_SESSION_']->customer->organization()->id)) {
				$counter = new \Site\Counter("organization_required");
				$counter->increment();
			    header('location: /_register/organization_required');
			    exit;
			}
		}

		public function confirmTOUAcceptance() {
			if ($this->tou_id > 0) {
				$tou = $this->tou();
				$latest_version = $tou->latestVersion();
				if ($tou->error()) app_log($tou->error(),'error');
				elseif (!$latest_version) app_log('No published version of tou '.$tou->id);
				else {
					if (! $GLOBALS['_SESSION_']->customer->acceptedTOU($tou->id)) {
						app_log("Customer has not yet accepted version ".$latest_version->id." of TOU ".$tou->id);
						header("Location: /_site/terms_of_use_form?module=".$this->module()."&view=".$this->view()."&index=".$this->index());
						exit;
					}
					app_log("Customer has accepted version ".$latest_version->id." of TOU ".$tou->id,'trace');
				}
			}
			return true;
		}

	    public function getPage($module, $view, $index = null) {
			$this->clearError();

			$database = new \Database\Service();

		    if (empty($index) || strlen($index) < 1) $index = null;

		    // Prepare Query
		    $get_object_query = "
				    SELECT	id
				    FROM	page_pages
				    WHERE	module = ?
				    AND		view = ?
			    ";
			$database->AddParam($module);
			$database->AddParam($view);

		    if (isset ( $index )) {
			    $get_object_query .= "
				    AND		`index` = ?
				    ";
				$database->AddParam($index);
		    }
			else {
			    $get_object_query .= "
				    AND		(`index` is null or `index` = '')
				    ";
		    }

		    $rs = $database->Execute($get_object_query);
		    if (! $rs) {
			    $this->SQLError($database->ErrorMsg());
			    return ;
		    }
		    list($id) = $rs->FetchRow();

		    if (is_numeric($id)) {
			    $this->id = $id;
			    return $this->details();
		    }
			elseif ($module == "static") {
				// No Special Characters in static path
				if ($this->validView($view)) {
					// Store module and view
					$this->module = $module;
					$this->view = $view;
					return true;
				}
				else {
					app_log("Request for $module::$view view, not adding page: Invalid characters in static path",'notice');
					return false;
				}
			}
			elseif ($module == "content" && $view == "index") {
				$message = new \Content\Message();
				if ($message->get($index)) {
					return $this->add($module,$view,$index);
				}
				elseif ($GLOBALS['_SESSION_']->customer->can('edit content messages')) {
					return $this->getPage("site","content_block");
				}
				else return false;
			}
			else {
				// See if view exists...we should create it if it doesn't
				$file_path = MODULES."/".$module;
				if (isset($GLOBALS['_config']->style[$module])) $file_path .= "/".$GLOBALS['_config']->style[$module];
				else $file_path .= "/default";
				if (file_exists($file_path."/".$view.".php") || file_exists($file_path."/".$view."_mc.php")) {
					app_log("Request for $module::$view view, adding to pages",'notice');
					return $this->add($module,$view);
				}
				else {
					app_log("Request for $module::$view view, not adding page: No '$file_path/".$view.".php or $file_path/".$view."_mc.php",'notice');
				    return false;
				}
		    }
		    return true;
	    }

		/**
		 * Add a page by raw data parameters
		 * @param array $parameters
		 */
		public function addByParameters($parameters = []) {
			$this->clearError();
			parent::add($parameters);
		}

		/**
		 * Add a page by module, view, and index
		 * @param string $module
		 * @param string $view
		 * @param string $index
		 * @return bool True if successful
		 */
	    public function add($module = '', $view = '', $index = '') {
			$this->clearError();
app_log("Request to add page ".$module."::".$view."::".$index,'notice');
			// Initialize Database Service
			$database = new \Database\Service();

		    // Apply optional parameters
		    if (!empty($module) && $this->validModule($module)) {
			    $this->module = $module;
			    if (!empty($view) && $this->validView($view)) {
				    $this->view = $view;
				    if (!empty($index) && $this->validIndex($index)) $this->index = $index;
			    }
		    }
app_log("Adding page ".$this->module."::".$this->view."::".$this->index,'notice');
			// Prepare Query to Add Page
		    $add_object_query = "
			    INSERT
			    INTO	page_pages
			    (		module,view,`index`
			    )
			    VALUES
			    (		?,?,?)
		    ";

			// Bind Parameters
			$database->AddParam($this->module);
			$database->AddParam($this->view);
			$database->AddParam($this->index);

		    $database->Execute($add_object_query);
		    if ($database->ErrorMsg()) {
			    $this->SQLError($database->ErrorMsg());
			    return false;
		    }
		    $this->id = $database->Insert_ID();
			
            // audit the add event
            $auditLog = new \Site\AuditLog\Event();
            $auditLog->add(array(
                'instance_id' => $this->id,
                'description' => 'Added new '.$this->_objectName(),
                'class_name' => get_class($this),
                'class_method' => 'add'
            ));

		    app_log("Added page id ".$this->id);
		    return $this->details();
	    }

		/**
		 * Update a page by raw data parameters
		 * @param array $parameters
		 * @return bool True if successful
		 */
		public function update($parameters = []): bool {

			$this->clearError();

			// Initialize Database Service
			$database = new \Database\Service();

			// Prepare Query to Update Page
			$update_object_query = "
				UPDATE	`$this->_tableName`
				SET		`$this->_tableIDColumn` = `$this->_tableIDColumn`
			";
			if (isset($parameters['tou_id'])) {
				$update_object_query .= ",
						tou_id = ?";
				if ($parameters['tou_id'] < 1) $parameters['tou_id'] = '0';
				$database->AddParam($parameters['tou_id']);
			}
			if (isset($parameters['sitemap'])) {
				$update_object_query .= ",
						sitemap = ?";
				if ($parameters['sitemap'] == true || $parameters['sitemap'] == 1) $database->AddParam(1);
				else $database->AddParam(0);
			}

			$update_object_query .= "
				WHERE	`$this->_tableIDColumn` = ?";
			$database->AddParam($this->id);

			$this->clearCache();
			$database->Execute($update_object_query);
			if ($database->ErrorMsg()) {
				$this->SQLError($database->ErrorMsg());
				return false;
			}
			else {
				// audit the update event
				$auditLog = new \Site\AuditLog\Event();
				$auditLog->add(array(
					'instance_id' => $this->id,
					'description' => 'Updated '.$this->_objectName(),
					'class_name' => get_class($this),
					'class_method' => 'update'
				));

				return $this->details();
			} 
		}

		/**
		 * Delete a page
		 * @return bool 
		 */
		public function delete(): bool {
			// Delete Content Block for Page
			if (!empty($this->index)) {
				$block = new \Content\Message();
				if ($block->get($this->index)) {
					$block->drop();
				}
			}
			// Delete Metadata Records for Page
			$this->purgeMetadata();

			// Delete Page
			$database = new \Database\Service();
			$delete_object_query = "
				DELETE
				FROM	page_pages
				WHERE	id = ?
			";
			$database->AddParam($this->id);
			$database->Execute($delete_object_query);
			if ($database->ErrorMsg()) {
				$this->addError($database->ErrorMsg());
				return false;
			}
			
			// audit the delete event
			$auditLog = new \Site\AuditLog\Event();
			$auditLog->add(array(
				'instance_id' => $this->id,
				'description' => 'Deleted '.$this->_objectName(),
				'class_name' => get_class($this),
				'class_method' => 'delete'
			));		
				
			return true;
		}

		/**
		 * Get Page Details
		 * @return bool 
		 */
	    public function details(): bool {
			$this->clearError();

			$cache = $this->cache();
			$cachedData = $cache->get();
	
			if (!empty($cachedData)) {
				foreach ($cachedData as $key => $value) {
					$this->$key = $value;
				}
				$this->cached(true);
				$this->exists(true);
			}
			else {
				// Initialize Database Service
				$database = new \Database\Service();

				// Prepare Query to Get Page Details
				$get_details_query = "
					SELECT	id,
							module,
							view,
							tou_id,
							`index`,
							sitemap
					FROM	page_pages
					WHERE	id = ?
				";

				// Bind Parameters
				$database->AddParam($this->id);

				// Execute Query
				$rs = $database->Execute($get_details_query);
				if (! $rs) {
					$this->SQLError($database->ErrorMsg());
					return false;
				}
				$object = $rs->FetchNextObject(false);
				if (gettype($object) == 'object') {
					$this->module = $object->module;
					$this->view = $object->view;
					$this->tou_id = $object->tou_id;
					$this->index = $object->index;
					if ($object->sitemap == 1) $this->sitemap = true;
					else $this->sitemap = false;
					$this->exists(true);
					$cache->set($object);
				}
				else {
					$this->module = "";
					$this->view = "";
					$this->index = "";
					$this->tou_id = null;
					$this->sitemap = false;
					$this->exists(false);
				}
			}

		    if (isset($GLOBALS['_config']->style[$this->module] )) {
			    $this->style = $GLOBALS['_config']->style[$this->module];
		    }

			// Intranet style site, No public content
            if (isset($GLOBALS['_config']->site->private_mode) && $GLOBALS['_config']->site->private_mode) {
                $this->auth_required = true;
            }

		    // Make Sure Authentication Requirements are Met
		    if (($this->auth_required) and (! $GLOBALS ["_SESSION_"]->customer->id)) {
			    if (($this->module != "register") or (! in_array ( $this->view, array ('login', 'forgot_password', 'register', 'email_verify', 'resend_verify', 'invoice_login', 'thank_you' ) ))) {
				    // Clean Query Vars for this
				    $auth_query_vars = preg_replace ( "/\/$/", "", $GLOBALS['_REQUEST_']->query_vars );

				    if ($this->module == 'content' && $this->view == 'index' && ! $auth_query_vars) $auth_target = '';
				    else {
					    $auth_target = ":_" . $this->module . ":" . $this->view;
					    if ($auth_query_vars) $auth_target .= ":" . $auth_query_vars;
					    $auth_target = urlencode ( $auth_target );
				    }

				    // Build New URL
				    header ( "location: " . PATH . "/_register/login/" . $auth_target );
                    exit;
			    }
		    }

		    return true;
	    }

		public function module() {
			if (preg_match('/(\w[\w\_\.]*)/',$this->module,$matches)) return $matches[1];
		}

		public function view() {
			if (preg_match('/(\w[\w\_\.]*)/',$this->view,$matches)) return $matches[1];
		}

		public function index() {
			if (preg_match('/([\w\_\-]*)/',$this->index,$matches)) return $matches[1];
		}

		public function title($string = null) {
			if (isset($string)) $this->title = $string;

			if (!empty($this->title))
				return $this->title;
			if (!empty($this->getMetadata("title")))
				return $this->getMetadata("title");
			if (!empty($this->view))
				return ucwords(preg_replace('/[\-\.\_]+/'," ",$this->view()));
		}

		public function template() {
			$template = $this->getMetadata('template');
			if (preg_match('/(\w[\w\_\-\.]*\.html)/',$template,$matches)) return $matches[1];
			elseif (file_exists(HTML . "/" . $this->module() . "." . $this->view() . ".html")) return $this->module() . "." . $this->view() . ".html";
			elseif ($this->view == 'api' && file_exists ( HTML . "/_api.html")) return "_api.html";
			elseif (file_exists ( HTML . "/" . $this->module() . ".html")) return $this->module() . ".html";
			elseif (isset ( $GLOBALS ['_config']->site->default_template)) return $GLOBALS ['_config']->site->default_template;
			elseif (file_exists ( HTML . "/index.html")) return "index.html";
			elseif (file_exists ( HTML . "/install.html" )) return "install.html";
			else return null;
		}

		public function load_template() {
            $this->loadSiteHeaders();
			if ($this->module() == 'static') {
				return $this->parse(file_get_contents(HTML."/".$this->view));
			}
			if ($this->view() == 'api') {
				return $this->parse(file_get_contents(HTML."/api.html"));
			}
			$this->template = $this->template();
			if (!empty($this->template ) && file_exists(HTML."/".$this->template)) return $this->parse(file_get_contents(HTML."/".$this->template));
			elseif (!empty($this->template)) app_log("Template ".HTML."/".$this->template." not found!",'error');
			return $this->parse('<r7 object="page" property="view"/>');
	    }

	    public function parse($message) {
		    $module_pattern = "/<r7(\s[\w\-]+\=\"[^\"]*\")*\/>/is";
		    while ( preg_match( $module_pattern, $message, $matched ) ) {
			    $search = isset($matched[0]) ? $matched[0] : "";
			    $parse_message = "Replaced $search";
			    $replace_start = microtime( true );
			    $replace = $this->replace($search);
			    $message = str_replace( $search, $replace, $message );
		    }

			if (preg_match('/Input\sarray\shas\s\d+\sparams\,\sdoes\snot\smatch\squery\:/',$message)) {
				app_log("Database Input Array count missmatch at ".$this->module()."/".$this->view(),'error');
				app_log($message,'notice');
				$counter = new \Site\Counter("SQL.InputArray.error");
				$counter->increment();
				$message = "Application Error";
			}
			if (!empty($this->template())) header("X-Template: ".$this->template());
			if (!empty($this->module)) header("X-Module: ".$this->module());
			return $message;
	    }
	    
	    private function parse_element($string) {
	    
		    // Initialize Array to hold Parameters
		    $parameters = array ();

		    // Grab Parameters from Element
		    preg_match ( '/^<r7\s(.*)\/>/', $string, $matches );
		    $string = $matches [1];

		    // Tokenize Parameters
		    while (strlen ( $string ) > 0 ) {
		    
			    // Trim Leading Space
			    $string = ltrim ( $string );

			    // Grab Parameter Name
			    list ( $name, $string ) = preg_split ( '/\=/', $string, 2 );

			    // Grab Parameter Value (with optional surrounding double quotes)
			    if (substr ( $string, 0, 1 ) == '"') list ( $value, $string ) = preg_split ( '/\"/', substr ( $string, 1 ), 2 );
			    else list ( $value, $string ) = preg_split ( '/\s/', $string, 2 );

			    // Store Parameter in Array
			    $parameters [$name] = $value;
		    }
		    return $parameters;
	    }
	    
	    public function replace($string) {
			
		    // Initialize Replacement Buffer
		    $buffer = '';

		    // Parse Token
		    $parameter = $this->parse_element($string);

		    if (array_key_exists ( 'module', $parameter )) $module = $parameter ['module'];
		    if (array_key_exists ( 'object', $parameter )) $object = $parameter ['object'];
		    if (array_key_exists ( 'property', $parameter )) $property = $parameter ['property'];

		    app_log ( "Object: $object Property: $property", 'debug', __FILE__, __LINE__ );
		    if ($object == "constant") {
			    if ($property == "path") {
				    $buffer .= PATH;
			    }
				elseif ($property == "date") {
				    $buffer .= date ( 'm/d/Y h:i:s' );
			    }
				elseif ($property == "host") {
				    $buffer .= $_SERVER ['HTTP_HOST'];
			    }
		    }
			elseif ($this->view() == "api") {
				$api = new \API();
				if (empty($_REQUEST["method"])) {
					# Call the Specified Method
					$buffer = $api->_form();
				}
				else {
					$api->method($_REQUEST["method"]);
					exit;
				}
			}
			elseif ($object == "page") {
			    if ($property == "view") {
					$buffer = "<r7 object=\"" . $this->module() . "\" property=\"" . $this->view() . "\"/>";
				}
				elseif ($property == "errorblock") {
					error_log("FOUND errorblock");
					if ($this->errorCount() > 0) {
						$buffer = '<section id="form-message">
						<ul class="connectBorder errorText">
							<li>';
						$buffer .= $this->errorString();
						$buffer .= '</li>
						</ul>
						</section>';
					}
					elseif ($this->success) {
						$buffer = '<section id="form-message">
						<ul class="connectBorder progressText">
							<li>';
						$buffer .= $this->success;
						$buffer .= '</li>
						</ul>
						</section>';
					}
				}
				elseif ($property == "title") {
				    if (isset ( $this->metadata->title )) $buffer = $this->metadata->title;
			    }
				elseif ($property == "metadata") {
				    if ($this->getMetadata($parameter["field"])) $buffer = $this->getMetadata($parameter["field"]);
			    }
				elseif ($property == "navigation") {
					$menu = new \Site\Navigation\Menu();
					if ($menu->get($parameter["name"])) {
						$buffer .= $menu->asHTML($parameter['name']);
					}
					else {
						$this->error($menu->error());
						return '';
					}					
					$items = $menu->items();

				    if (count($items)) {
					    foreach ($items as $item) {
						    if (isset( $parameter ['class'] )) $button_class = $parameter ['class'];
						    else {
							    $button_class = "button_" . preg_replace ( "/\W/", "_", $menu->title );
						    }
						    $button_id = "button[" . $item->id . "]";
						    if (count ( $item->children )) {
							    $child_container_class = "child_container_" . preg_replace ( "/\W/", "_", $menu->title );
							    $child_container_id = "child_container[" . $item->id . "]";
							    $child_button_class = "child_button_" . preg_replace ( "/\W/", "_", $menu->title );

							    $buffer .= "<div" . " onMouseOver=\"expandMenu('$child_container_id')\"" . " onMouseOut=\"collapseMenu('$child_container_id')\"" . " id=\"$button_id\"" . " class=\"$button_class\"" . ">" . $item->title . "</div>\n";

							    $buffer .= "\t<div class=\"$child_container_class\" id=\"$child_container_id\">\n";
							    foreach ( $item->children as $child ) {
								    $buffer .= "\t\t" . "<a" . " onMouseOver=\"expandMenu('$child_container_id')\"" . " onMouseOut=\"collapseMenu('$child_container_id')\"" . ' href="' . $child->target . '"' . ' class="' . $child_button_class . '">' . $child->title . "</a>\n";
							    }
							    $buffer .= "\t</div>";
						    }
							else {
							    $buffer .= "<a" . " href=\"" . $item->target . "\"" . " class=\"$button_class\"" . ">" . $item->title . "</a>\n";
						    }
					    }
				    }
			    }
				elseif ($property == "message") {
				    $buffer .= "<div class=\"page_message\">" . $GLOBALS ['page_message'] . "</div>";
			    }
				elseif ($property == "error") {
				    $buffer .= "<div class=\"page_error\">" . $GLOBALS ['page_error'] . "</div>";
			    }
				elseif ($property == "not_authorized") {
				    $buffer .= "<div class=\"page_error\">Sorry, you are not authorized to see this view</div>";
			    }
				else {
				    $buffer = $this->loadViewFiles($buffer);
			    }
		    }
			elseif ($object == "navigation") {
			    if ($property == "menu") {
				    if ($parameter['code']) {
					    $menu = new \Site\Navigation\Menu ();
					    if ($menu->get($parameter['code'])) {
							if (!empty($parameter['version']) && $parameter['version'] == 'v2') {
								$buffer .= $menu->asHTMLV2($parameter);
							}
							else {
							    $buffer .= $menu->asHTML($parameter);
							}
					    }
				    }
					else {
					    app_log("navigation menu references without code");
				    }
			    }
				else {
				    $buffer = $this->loadViewFiles($buffer);
			    }
		    }
			elseif ($object == "content") {
			    if ($property == "index") {
				    app_log( "content::index", 'trace', __FILE__, __LINE__ );

					// Load Content Block with specified id
					if (isset($parameter['id']) && is_numeric($parameter["id"])) {
						$target = $parameter["id"];
						app_log("Load block id '$target' from parameter 'id'",'trace');
					}
					// Load Content Block with specified target
					elseif (isset( $parameter['target']) && preg_match("/^\w[\w\-\_]*$/", $parameter["target"])) {
						$target = $parameter["target"];
						app_log("Load block id '$target' from parameter 'target'",'trace');
					}
					// Load Content Block with URI path as target
					elseif (!empty($GLOBALS['_REQUEST_']->query_vars_array[0])) {
						$target = $GLOBALS['_REQUEST_']->query_vars_array[0];
						app_log("Load block target '$target' from URI",'trace');
					}
					// Load Content Block with Page Index as target
					else {
						$target = $this->index;
						app_log("Load block target '$target' from request 'index'",'trace');
					}

				    $block = new \Content\Block();
				    $block->get($target);
				    if ($block->error()) $buffer = "Error: " . $block->error();
				    elseif (! $block->id) {
						app_log("Message not found matching '$target', adding", 'info', __FILE__, __LINE__ );
						if ($GLOBALS['_SESSION_']->customer->can('edit content messages')) {
							$block->add(array("target" => $target));
						}
						else {
							$buffer = "Sorry, the page you requested was not found";
							app_log("Page not found: $target", 'error', __FILE__, __LINE__ );
					    }
				    }
					else {
						app_log("Found message ".$block->id);
					}
				    if ($block->cached()) {
						app_log("Loading from cache");
					    header("X-Object-Cached: true" );
				    }
				    if ($block->id) {
					    // Make Sure User Has Privileges
					    if (is_object($GLOBALS['_SESSION_']->customer) && $GLOBALS['_SESSION_']->customer->id && $GLOBALS['_SESSION_']->customer->can('edit content messages')) {
						    $buffer .= '<contentblock id="'.$block->id.'">' . $block->content . '</contentblock>';
						    $buffer .= '<a href="javascript:void(0)" class="btn_editContent" onclick="goToEditPage(\''.$block->target.'\')">Edit</a>';
					    }
						else {
						    $buffer .= $block->content;
					    }
				    }
			    }
				else {
				    $buffer = $this->loadViewFiles($buffer);
			    }
		    }
			elseif ($object == "product") {  
			    // Load Product Class if Not Already Loaded
			    if ($property == "thumbnail") {
				    $id = $GLOBALS['_REQUEST_']->query_vars;
				    $product = new \Product\Item($id);
				    if (! $id) {
					    $category_id = $product->defaultCategory();
					    if ($product->error()) {
						    print $product->error();
						    exit();
					    }
				    }
					else {
					    $category_id = $product->id;
				    }
					$category = new \Product\Item($category_id);
					$productList = new \Product\ItemList();
				    $products = $productList->find ( array ("category" => $category->code ) );

				    // Loop Through Products
				    foreach ( $products as $product ) {
					    $buffer .= "<r7_product.detail format=thumbnail id=".$product->id.">";
				    }
			    }
				elseif ($property == "detail") {
				    if (preg_match ( "/^\d+$/", $parameter ["id"] )) $id = $parameter ["id"];
				    elseif ($GLOBALS['_REQUEST_']->query_vars) $id = $GLOBALS['_REQUEST_']->query_vars;

				    $product = new \Product\Item( $id );
				    if ($parameter["format"] == "thumbnail") {
					    if ($product->type()->group) {
						    $buffer .= "<div id=\"product[" . $parameter ["id"] . "]\" class=\"product_thumbnail\">\n";
						    $buffer .= "\t<a href=\"/_product/thumbnail/" . $product->id . "\" class=\"product_thumbnail_name\">" . $product->name . "</a>\n";
						    $buffer .= "\t<div class=\"product_thumbnail_description\">" . $product->description . "</div>\n";
						    $buffer .= "\t<div class=\"product_thumbnail_retail\">" . $product->currentPrice() . "</div>\n";
						    if ($product->images()[0]->files->thumbnail->path) $buffer .= "\t\t<img src=\"" . $product->images()["0"]->files->thumbnail->path . "\" class=\"product_thumbnail_image\"/>\n";
						    $buffer .= "</div>\n";
					    }
						else {
						    $buffer .= "<div id=\"product[" . $parameter ["id"] . "]\" class=\"product_thumbnail\">\n";
						    $buffer .= "\t<a href=\"/_product/detail/" . $product->id . "\" class=\"product_thumbnail_name\">" . $product->name . "</a>\n";
						    $buffer .= "\t<div class=\"product_thumbnail_description\">" . $product->description . "</div>\n";
						    $buffer .= "\t<div class=\"product_thumbnail_retail\">" . $product->currentPrice(). "</div>\n";
						    if ($product->images()["0"]->files->thumbnail->path) $buffer .= "\t<div class=\"product_thumbnail_image\"><img src=\"" . $product->images()["0"]->files->thumbnail->path . "\" class=\"product_thumbnail_image\"/></div>\n";
						    $buffer .= "</div>\n";
					    }
				    }
					else {
					    $buffer .= "<div id=\"product[" . $parameter ["id"] . "]\" class=\"product_thumbnail\">\n";
					    $buffer .= "<a href=\"/_product/detail/" . $product->id . "\" class=\"product_thumbnail_name\">" . $product->name . "</a>\n";
					    $buffer .= "<div class=\"product_detail_description\">" . $product->description . "</div>\n";
					    $buffer .= "<div class=\"product_detail_retail\">" . $product->currentPrice(). "</div>\n";
					    if ($product->images()["0"]->files->large->path) $buffer .= "<img src=\"" . $product->images()["0"]->files->large->path . "\" class=\"product_thumbnail_image\"/>\n";
					    $buffer .= "</div>\n";
				    }
			    }
				elseif ($property == "navigation") {
				    if (preg_match ( "/^\d+$/", $parameter ["id"] )) $id = $parameter ["id"];
				    elseif ($GLOBALS['_REQUEST_']->query_vars) $id = $GLOBALS['_REQUEST_']->query_vars;

				    $_product = new \Product\Item($id);
				    if (! $id) {
					    $category_id = $_product->defaultCategory();
					    if ($_product->error()) {
						    print $_product->error();
						    exit();
					    }
				    }
					else {
					    $category_id = $_product->id;
				    }
					$category = new \Product\Item($category_id);
                    $productList = new \Product\ItemList();
				    $products = $productList->find( array ("category" => $category->code ) );

				    // Loop Through Products
				    foreach ($products as $product) {
					    if ($product->type->group) {
						    $buffer .= "<div id=\"product_navigation[" . $parameter ["id"] . "]\" class=\"product_navigation\">\n";
						    $buffer .= "<a href=\"/_product/thumbnail/" . $product->id . "\" class=\"product_navigation_name\">" . $product->name . "</a>\n";
						    if ($product->images ["0"]->files->icon->path) $buffer .= "<img src=\"" . $product->images ["0"]->files->icon->path . "\" class=\"product_navigation_image\"/>\n";
						    $buffer .= "</div>\n";
					    } else {
						    $buffer .= "<div id=\"product_navigation[" . $parameter ["id"] . "]\" class=\"product_navigation\">\n";
						    $buffer .= "<a href=\"/_product/detail/" . $product->id . "\" class=\"product_navigation_name\">" . $product->name . "</a>\n";
						    if ($product->images ["0"]->files->icon->path) $buffer .= "<img src=\"" . $product->images ["0"]->files->icon->path . "\" class=\"product_navigation_image\"/>\n";
						    $buffer .= "</div>\n";
					    }
				    }
			    } else {
				    $buffer = $this->loadViewFiles($buffer);
			    }
		    }
			elseif ($object == "monitor") {
				$buffer = $this->loadViewFiles($buffer);
		    }
			elseif ($object == "session") {
			    if ($property == "customer_id") $buffer = $GLOBALS ['_SESSION_']->customer->id;
			    elseif ($property == "loggedin") {
				    if (isset ( $GLOBALS ['_SESSION_']->customer->id )) $buffer = "true";
				    else $buffer = "false";
			    } else {
				    $buffer = $this->loadViewFiles($buffer);
			    }
		    }
			elseif ($object == "register") {
			    if (isset ( $parameter ['id'] ) and preg_match ( "/^\d+$/", $parameter ["id"] )) $id = $parameter ["id"];
			    elseif (isset ( $GLOBALS['_REQUEST_']->query_vars )) $id = $GLOBALS['_REQUEST_']->query_vars;

			    if ($property == "user") {
				    if ($parameter ['field'] == "name") {
					    $customer = new \Register\Customer ( $GLOBALS ['_SESSION_']->customer->id );
					    $buffer .= $customer->first_name . " " . $customer->last_name;
				    }
			    } elseif ($property == "welcomestring") {
				    if ($GLOBALS ['_SESSION_']->customer) {
					    $buffer .= "<span class=\"register_welcomestring\">Welcome " . $GLOBALS ['_SESSION_']->customer->first_name . " " . $GLOBALS ['_SESSION_']->customer->last_name . "</span>";
				    } else {
					    $buffer .= "<a class=\"register_welcomestring\" href=\"" . PATH . "/_register/login\">Log In</a>";
				    }
			    } else {
				    $buffer = $this->loadViewFiles($buffer);
			    }
		    }
			elseif ($object == "company") {
			    $companies = new \Company\CompanyList ();
			    list ( $company ) = $companies->find ();

			    if ($property == "name") {
				    $buffer .= $company->name;
			    }
				elseif ($property == "copyright") {
					$buffer = '&copy;'.date('Y')." ".$company->name;
				}
				else {
                    $buffer = $this->loadViewFiles($buffer);
			    }
		    }
			elseif ($object == "news") {
			    if ($property == "events") {
				    $eventlist = new \News\EventList();
				    if ($eventlist->error()) {
					    $this->error("Error fetching events: " . $eventlist->error());
				    } else {
					    $events = $eventlist->find ( array ('feed_id' => $parameter ['id'] ) );
					    if ($eventlist->error()) {
						    $this->error("Error fetching events: " . $eventlist->error());
					    } else if (count ( $events )) {
						    foreach ( $events as $event ) {
								$greenbar = '';
							    $buffer .= "<a class=\"value " . $greenbar . "newsWidgetEventValue\" href=\"" . PATH . "/_news/event/" . $event->id . "\">" . $event->name . "</a>";
							    if ($greenbar) $greenbar = '';
							    else $greenbar = 'greenbar ';
						    }
						    $buffer .= "<a class=\"value newsWidgetEventValue newsWidgetAddLink\" href=\"" . PATH . "/_news/new_event" . "\">Add</a>";
					    }
				    }
			    } else {
				    $buffer = $this->loadViewFiles($buffer);
			    }
		    }
			elseif ($object == "adminbar") {
			    if (role ( 'administrator' )) $buffer = "<div class=\"adminbar\" id=\"adminbar\" style=\"height:20px; width: 100%; position: absolute; top: 0px; left: 0px;\">Admin stuff goes here</div>\n";
		    }
			else {
                $buffer = $this->loadViewFiles($buffer);
		    }
		    return $buffer;
	    }
	    
        public function loadSiteHeaders() {
            $headerList = new \Site\HeaderList();
            $headers = $headerList->find();
            foreach ($headers as $header) {
                header($header->name.": ".$header->value);
            }
        }
        
        public function loadViewFiles($buffer = "") {
		    ob_start ();
            if (isset($this->style)) {
                if (file_exists(MODULES.'/'.$this->module().'/'.$this->style.'/'.$this->view.'_mc.php'))
                    $be_file = MODULES.'/'.$this->module().'/'.$this->style.'/'.$this->view.'_mc.php';
                elseif (file_exists(MODULES.'/'.$this->module().'/default/'.$this->view.'_mc.php'))
                    $be_file = MODULES.'/'.$this->module().'/default/'.$this->view.'_mc.php';
                if (file_exists(MODULES . '/' . $this->module() . '/' . $this->style . '/' . $this->view . '.php'))
                    $fe_file = MODULES . '/' . $this->module() . '/' . $this->style . '/' . $this->view . '.php';
                elseif (file_exists(MODULES . '/' . $this->module() . '/default/' . $this->view . '.php'))
                    $fe_file = MODULES . '/' . $this->module() . '/default/' . $this->view . '.php';
            }
		    app_log ( "Loading view " . $this->view() . " of module " . $this->module(), 'debug', __FILE__, __LINE__ );
		    if (isset($be_file) && file_exists($be_file)) {
				// Load Backend File
                $res = include($be_file);

				// Handle possible return codes
                if ($res == 403) {
                    http_response_code(403);
					$counter = new \Site\Counter("return403");
					$counter->increment();
                    return '<span class="label page_response_code">Permission Denied</span>';
                }
                elseif ($res == 500) {
                    http_response_code(500);
					$counter = new \Site\Counter("return500");
					$counter->increment();
                    return '<span class="label page_response_code">Internal Error</span>';
                }
				elseif ($res == 404) {
                    http_response_code(404);
					$counter = new \Site\Counter("return404");
					$counter->increment();
					return '<span class="label page_response_code">Resource not found</span>';
				}
            }
		    else app_log ( "Backend file '$be_file' for module " . $this->module() . " not found" );
            if (isset($fe_file) && file_exists ( $fe_file )) include ($fe_file);
		    $buffer .= ob_get_clean ();
            
            // if match "query: " then must be an ADODB error happening
            //      scrub out any non HTML characters BEFORE the first HTML tag to remove the standard output ADODB errors that end up getting printed on the page
            if (strpos($buffer, " query: ") !== false) {
                preg_match('/^[^<]*/', $buffer, $matches);
                if (!empty($matches[0])) $buffer = str_replace($matches[0], "",$buffer);
            }
            return $buffer;
        }
        
	    public function requires($role = '_customer') {
		    if ($role == '_customer') {
			    if ($GLOBALS ['_SESSION_']->customer->id) {
				    return true;
			    } else {
				    header ( "location: /_register/login?target=_" . $this->module() . ":" . $this->view() );
				    ob_flush ();
				    exit ();
			    }
		    } elseif ($GLOBALS ['_SESSION_']->customer->has_role ( $role )) {
			    return true;
		    } else {
			    header ( "location: /_register/not_authorized" );
			    ob_flush ();
			    exit ();
		    }
	    }

		// Return the Terms of Use object for this page
		public function tou() {
			return new \Site\TermsOfUse($this->tou_id);
		}

		/********************************************/
		/* Warning and Error Handling				*/
		/********************************************/
		// Add a warning to the page
		public function addWarning($msg) {
		    $trace = debug_backtrace ();
		    $caller = $trace [0];
		    $file = $caller ['file'];
		    $line = $caller ['line'];
		    app_log ( $msg, 'warn', $file, $line );
		    array_push ( $this->_warnings, $msg );
	    }

		// Return the serialized warning string
	    public function warningString($delimiter = "<br>\n") {
		    $warning_string = '';
		    foreach ( $this->_warnings as $warning ) {
			    if (strlen ( $warning_string )) $warning_string .= $delimiter;
			    $warning_string .= $warning;
		    }
		    return $warning_string;
	    }

		// Return the warning array
	    public function warnings() {
		    return $this->_warnings;
	    }

		// Return the number of warnings in the array
	    public function warningCount() {
		    if (empty ( $this->_warnings )) $this->_warnings = array();
		    return count ( $this->_warnings );
	    }

		// Add an errors to the page from an array
	    public function addErrors(array $errors) {
		    foreach ($errors as $error) $this->addError($error);
	    }

		// Add an error to the page
	    public function addError($error) {
		    $trace = debug_backtrace ();
		    $caller = $trace [0];
		    $file = $caller ['file'];
		    $line = $caller ['line'];
		    app_log ( $error, 'error', $file, $line );
		    array_push ( $this->_errors, $error );
	    }

		// Return the serialized error string
	    public function errorString($delimiter = "<br>\n") {
		    if (isset ( $this->error )) array_push ( $this->_errors, $this->error());
		    $error_string = '';
		    foreach ( $this->_errors as $error ) {
			    if (strlen ( $error_string )) $error_string .= $delimiter;
			    $called_from = debug_backtrace()[1];
			    // SQL errors in the error log, then output to page is standard "site error message"
			    if (preg_match ( '/SQL\sError/', $error ) || preg_match ( '/ query\:/', $error )) {
				    app_log ( $error, 'error',$called_from['file'],$called_from['line']);
				    $error_string .= "Internal site error";
			    } else {
				    $error_string .= $error;
			    }
		    }
		    return $error_string;
	    }

		// Return the error array
	    public function errors() {
		    return $this->_errors;
	    }

		// Return the number of errors in the array
	    public function errorCount() {
		    if (empty ( $this->_errors )) $this->_errors = array();
		    if (! empty ( $this->error )) array_push ($this->_errors, $this->error());
		    return count ( $this->_errors );
	    }

		// We don't keep an array of successes, just a string
		// Append a success message to the success string
		public function appendSuccess($string) {
			if (!empty($this->success)) $this->success .= "<br>\n";
			$this->success .= $string;
		}

		/************************************/
		/* Breadcrumb Methods				*/
		/************************************/
		public function showAdminPageInfo() {
			return "<div id='adminPageInfo'><div id='adminTitle'>".$this->showTitle()."\n".$this->showBreadcrumbs()."</div>".$this->showMessages()."</div>";
		}

		public function addBreadcrumb($name,$target = '') {
			$breadcrumb = array("name" => $name, "target" => $target);
			array_push($this->_breadcrumbs,$breadcrumb);
		}

		public function showBreadcrumbs() {
			if (count($this->_breadcrumbs) < 1) return "";
			$html = '';
			foreach ($this->_breadcrumbs as $breadcrumb) {
				if (!empty($breadcrumb['target'])) $html .= "\t\t<li><a href=\"".$breadcrumb['target']."\">".$breadcrumb['name']."</a></li>\n";
				else $html .= "\t\t<li>".$breadcrumb['name']."</li>";
			}
		    return "<nav id=\"breadcrumb\">\n\t<ul>\n$html\n\t</ul>\n</nav>\n";
		}

		public function showMessages() {
			$buffer = "";
			if ($this->errorCount() > 0) {
				$buffer .= "
          <section id=\"form-message\">
            <ul class=\"connectBorder errorText\">
              <li>".$this->errorString()."</li>
            </ul>
          </section>
			  ";
			}
			elseif (!empty($this->success)) {
				$buffer .= "
          <section id=\"form-message\">
            <ul class=\"connectBorder progressText\">
              <li>".$this->success."</li>
            </ul>
          </section>
			  ";
			}
			if ($this->warningCount() > 0) {
				$buffer .= "
          <section id=\"form-message\">
            <ul class=\"connectBorder warningText\">
              <li>".$this->warningString()."</li>
            </ul>
          </section>
			  ";
			}
			if (!empty($this->instructions)) {
				$buffer .= "
          <section id=\"form-message\">
            <ul class=\"connectBorder infoText\">
              <li>".$this->instructions."</li>
            </ul>
          </section>
        ";
			}
			elseif (!empty($this->getMetadata("instructions"))) {
				$buffer .= "
          <section id=\"form-message\">
            <ul class=\"connectBorder infoText\">
              <li>".$this->getMetadata("instructions")."</li>
            </ul>
          </section>
        ";
			}
			return $buffer;
		}

		public function showSearch() {
			return "<div id='searchBar'><input list='categories' type='search' id='site-search' name='q' placeholder='What are you looking for?'><datalist id='categories'><option value='Engineering'><option value='Support'><option value='Customer'><option value='Monitors'></datalist><input type='image' class='searchButton' src='/img/icons/icon_tools_search.svg' onclick='' /></div>";
		}

		public function showTitle() {
			$title = "<h1 id=\"page_title\">".$this->title()."</h1>";
            if ($GLOBALS['_SESSION_']->customer->can("edit site pages"))
                $title .= "<a id=\"icon_settings\" href=\"/_site/page?module=".$this->module()."&view=".$this->view()."&index=".$this->index."\"></a>";
            return $title;
		}
	
		public function uri() {
			if ($this->module() == 'content') return "/".$this->index();
			return "/_".$this->module()."/".$this->view()."/".$this->index();
		}

		/** @method public function rewrite()
		 * Rewrite old and producted URI's to new ones
		 * @return void
		 */
		public function rewrite() {
			if ($this->module() == 'static') {
				if ($this->view() == 'index.html') {
					$this->getPage('content','index','home');
				}
				elseif ($this->view() == 'products.html') {
					$this->getPage('content','index','products');
				}
				elseif ($this->view() == 'learning.html') {
					$this->getPage('content','index','learning');
				}
				elseif ($this->view() == 'contact_home.html') {
					$this->getPage('static','contact_sales.html');
				}
				elseif ($this->view() == 'contact_support.html') {
					$this->getPage('static','contact_sales.html');
				}
				elseif ($this->view() == 'distributors.html') {
					$this->getPage('content','index','distributors');
				}
				elseif ($this->view() == 'admin.html') {
					// Don't let people see admin template directly.
					// Not an actual risk, but SecureWorks called it out.
					http_response_code(404);
					exit;
				}
			}
		}

		public function name() {
			if ($this->module() == 'content' && $this->view() == 'index')
				return ucwords(preg_replace('/_/',' ',$this->index()));
			else
				return ucwords(preg_replace('/_/',' ',$this->view()));
		}

		/************************************/
		/* Validation Methods				*/
		/************************************/
		public function validModule($string) {
			if (preg_match('/^\w[\w]*$/',$string)) return true;
			else return false;
		}

		/**
		 * Validate view from path - Remember this could be used in static file serving
		 * @param mixed $string 
		 * @return false 
		 */
		public function validView($string) {
			// No Directory Traversal
			if (preg_match('/\.\./', $string)) return false;

			// Make Sure Only Ok Characters in Filename
			if (! preg_match('/^\w[\w\-\.\_]*$/',$string)) return false;

			// Make Sure Static File is in Docroot
			if ($this->module == 'static' && ! file_exists(HTML."/".$string)) return false;

			// Ok to go
			return true;
		}

		public function validIndex($string) {
            if (empty($string)) return true;
			if (preg_match('/^\w[\w\.\_\-]*$/',$string)) return true;
			else return false;
		}

		public function validStyle($string) {
			if (preg_match('/^\w[\w]*$/',$string)) return true;
			else return false;
		}

		public function validURI($string) {
			if (preg_match('/\.\./', $string)) return false;
			if (preg_match('/^[\w\-\.\_\/]+$/',$string)) return true;
			else return false;
		}

		public function validTitle($string) {
			if (empty(trim($string))) return false;
			if (preg_match('/[\<\>]/',urldecode($string))) return false;
			return true;
		}

		public function validTemplate($string) {
			if (preg_match('/\.\./', $string)) return false;
			if (preg_match('/^\w[\w\-\.\_]*\.html?$/',$string)) return true;
			else return false;
		}
    }
