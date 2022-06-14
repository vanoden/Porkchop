<?php
    namespace Site;
    
    class Page {
    
	    public $id;
	    public $module = 'content';
	    public $view = 'index';
	    public $index = '';
	    public $style = 'default';
	    public $auth_required = false;
	    public $ssl_required;
	    public $error;
	    public $uri;
	    public $title;
	    public $metadata;
	    public $template;
	    public $success;
	    private $_errors = array ();
	    
	    public function __construct() {
		    $args = func_get_args ();
			if (func_num_args() == 1 && gettype($args[0]) == "integer") {
				$this->id = $args[0];
				$this->details();
			} elseif (func_num_args () == 1 && gettype ( $args [0] ) == "string") {
			    $this->id = $args [0];
			    $this->details ();
		    } elseif (func_num_args () == 1 && gettype ( $args [0] ) == "array") {
			    if (isset($args [0] ['method'])) {
				    $this->method = $args [0] ['method'];
				    $this->view = $args [0] ['view'];
				    if ($args [0] ['index']) $this->index = $args [0] ['index'];
			    }
		    } elseif (func_num_args () == 2 && gettype ( $args [0] ) == "string" && gettype ( $args [1] ) == "string" && isset ( $args[2])) {
			    $this->get ( $args [0], $args [1], $args [2] );
		    } elseif (func_num_args () == 2 && gettype ( $args [0] ) == "string" && gettype ( $args [1] ) == "string") {
			    $this->get ( $args [0], $args [1] );
		    } else {
			    $this->fromRequest ();
		    }
	    }
	    public function fromRequest() {
		    return $this->get ( $GLOBALS ['_REQUEST_']->module, $GLOBALS ['_REQUEST_']->view, $GLOBALS ['_REQUEST_']->index );
	    }
	    public function applyStyle() {
		    if (isset ( $GLOBALS ['_config']->style [$this->module] )) $this->style = $GLOBALS ['_config']->style [$this->module];
	    }
	    public function requireAuth() {
		    if (! $GLOBALS ['_SESSION_']->customer->id > 0) {
				$counter = new \Site\Counter("auth_redirect");
				$counter->increment();
			    header ( 'location: /_register/login?target=' . urlencode ( $_SERVER ['REQUEST_URI'] ) );
		    }
	    }
	    public function requireRole($role) {
		    if ($this->module == 'register' && $this->view == 'login') {
			    // Do Nothing, we're Here
		    } elseif (! $GLOBALS ['_SESSION_']->customer->id) {
				$counter = new \Site\Counter("auth_redirect");
				$counter->increment();
			    header ( 'location: /_register/login?target=' . urlencode ( $_SERVER ['REQUEST_URI'] ) );
			    exit ();
		    } elseif (! $GLOBALS ['_SESSION_']->customer->has_role ( $role )) {
				$counter = new \Site\Counter("permission_denied");
				$counter->increment();
			    header ( 'location: /_register/permission_denied' );
			    exit ();
		    }
	    }
        public function requirePrivilege($privilege) {
            if ($GLOBALS['_SESSION_']->customer->can($privilege)) {
                return true;
            }
            elseif ($GLOBALS['_SESSION_']->customer->can('do everything')) {
                return true;
            }
            elseif (! $GLOBALS ['_SESSION_']->customer->id) {
			    header ( 'location: /_register/login?target=' . urlencode ( $_SERVER ['REQUEST_URI'] ) );
			    exit ();
		    }
            else {
			    header ( 'location: /_register/permission_denied' );
			    exit ();
		    }
        }
	    public function get($module, $view, $index = null) {
		    $parameters = array ($module, $view );
		    if (strlen ( $index ) < 1) $index = null;
		    // Prepare Query
		    $get_object_query = "
				    SELECT	id
				    FROM	page_pages
				    WHERE	module = ?
				    AND		view = ?
			    ";
		    if (isset ( $index )) {
			    $get_object_query .= "
				    AND		`index` = ?
				    ";
			    array_push ( $parameters, $index );
		    } else {
			    $get_object_query .= "
				    AND		(`index` is null or `index` = '')
				    ";
		    }
		    query_log($get_object_query, $parameters);
		    $rs = $GLOBALS ['_database']->Execute ( $get_object_query, $parameters );
		    if (! $rs) {
			    $this->addError ( "SQL Error in Page::get: " . $GLOBALS ['_database']->ErrorMsg () );
			    return null;
		    }
		    list ( $id ) = $rs->FetchRow ();

		    if (is_numeric ( $id )) {
			    $this->id = $id;
			    return $this->details ();
		    }
			elseif ($module == "content" && $view == "index") {
				$message = new \Content\Message();
				if ($message->get($index)) {
					return $this->add($module,$view,$index);
				}
				elseif ($GLOBALS['_SESSION_']->customer->can('edit content messages')) {
					return $this->get("site","content_block");
				}
				else return false;
			}
			else {
				// See if view exists...we should create it if it does
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
	    public function add($module = '', $view = '', $index = '') {
		    // Apply optional parameters
		    if ($module) {
			    $this->module = $module;
			    if ($view) {
				    $this->view = $view;
				    if ($index) $this->index = $index;
			    }
		    }

		    $add_object_query = "
				    INSERT
				    INTO	page_pages
				    (		module,view,`index`
				    )
				    VALUES
				    (		?,?,?)
			    ";
		    $GLOBALS ['_database']->Execute ( $add_object_query, array ($this->module, $this->view, $this->index ) );
		    if ($GLOBALS ['_database']->ErrorMsg ()) {
			    $this->error = "SQL Error in Site::Page::add(): " . $GLOBALS ['_database']->ErrorMsg ();
				app_log($this->error,'error');
			    return false;
		    }
		    $this->id = $GLOBALS ['_database']->Insert_ID ();
		    app_log ( "Added page id " . $this->id );
		    return $this->details();
	    }
	    public function details() {
		    $get_details_query = "
				    SELECT	id,
						    module,
						    view,
						    `index` idx
				    FROM	page_pages
				    WHERE	id = ?
			    ";
		    $rs = $GLOBALS ['_database']->Execute ( $get_details_query, array ($this->id ) );
		    if (! $rs) {
			    $this->error = "SQL Error in Site::Page::details(): " . $GLOBALS ['_database']->ErrorMsg ();
			    return null;
		    }
		    $object = $rs->FetchNextObject ( false );
		    if (gettype ( $object ) == 'object') {
			    $this->module = $object->module;
			    $this->view = $object->view;
			    $this->index = $object->idx;
		    } else {
			    // Just Let The Defaults Go
		    }
		    if (isset ( $GLOBALS ['_config']->style [$this->module] )) {
			    $this->style = $GLOBALS ['_config']->style [$this->module];
		    }

			// Intranet style site, No public content
            if (isset($GLOBALS['_config']->site->private_mode) && $GLOBALS['_config']->site->private_mode) {
                $this->auth_required = true;
            }

		    // Make Sure Authentication Requirements are Met
		    if (($this->auth_required) and (! $GLOBALS ["_SESSION_"]->customer->id)) {
			    if (($this->module != "register") or (! in_array ( $this->view, array ('login', 'forgot_password', 'register', 'email_verify', 'resend_verify', 'invoice_login', 'thank_you' ) ))) {
				    // Clean Query Vars for this
				    $auth_query_vars = preg_replace ( "/\/$/", "", $this->query_vars );

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

		public function template() {
			$template = $this->getMetadata('template');
			if (!empty($template)) return $template;
			elseif (file_exists(HTML . "/" . $this->module . "." . $this->view . ".html")) return $this->module . "." . $this->view . ".html";
			elseif ($this->view == 'api' && file_exists ( HTML . "/_api.html")) return "_api.html";
			elseif (file_exists ( HTML . "/" . $this->module . ".html")) return $this->module . ".html";
			elseif (isset ( $GLOBALS ['_config']->site->default_template)) return $GLOBALS ['_config']->site->default_template;
			elseif (file_exists ( HTML . "/index.html")) return "index.html";
			elseif (file_exists ( HTML . "/install.html" )) return "install.html";
			else return null;
		}

		public function load_template() {
			$template = $this->template();
			if (isset ( $template ) && file_exists(HTML."/".$template)) return $this->parse(file_get_contents(HTML."/".$template));
			elseif (isset ($template)) app_log("Template ".HTML."/".$template." not found!",'error');
			return $this->parse('<r7 object="page" property="view"/>');
	    }

	    public function parse($message) {
		    $module_pattern = "/<r7(\s[\w\-]+\=\"[^\"]*\")*\/>/is";
		    while ( preg_match ( $module_pattern, $message, $matched ) ) {
			    $search = $matched [0];
			    $parse_message = "Replaced $search";
			    $replace_start = microtime ( true );
			    $replace = $this->replace ( $matched [0] );
			    // app_log($parse_message." with $replace in ".sprintf("%0.4f",(microtime(true) - $replace_start))." seconds",'debug',__FILE__,__LINE__);
			    $message = str_replace ( $search, $replace, $message );
		    }

		    // Return Messsage
		    return $message;
	    }
	    
	    private function parse_element($string) {
		    // Initialize Array to hold Parameters
		    $parameters = array ();

		    // Grab Parameters from Element
		    preg_match ( '/^<r7\s(.*)\/>/', $string, $matches );
		    $string = $matches [1];

		    // Tokenize Parameters
		    while ( strlen ( $string ) > 0 ) {
		    
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
		    $parameter = $this->parse_element ( $string );

		    if (array_key_exists ( 'module', $parameter )) $module = $parameter ['module'];
		    if (array_key_exists ( 'object', $parameter )) $object = $parameter ['object'];
		    if (array_key_exists ( 'property', $parameter )) $property = $parameter ['property'];

		    app_log ( "Object: $object Property: $property", 'debug', __FILE__, __LINE__ );
		    if ($object == "constant") {
			    if ($property == "path") {
				    $buffer .= PATH;
			    } elseif ($property == "date") {
				    $buffer .= date ( 'm/d/Y h:i:s' );
			    } elseif ($property == "host") {
				    $buffer .= $_SERVER ['HTTP_HOST'];
			    }
		    } elseif ($object == "page") {
			    if ($property == "view") {
				    $buffer = "<r7 object=\"" . $this->module . "\" property=\"" . $this->view . "\"/>";
			    } elseif ($property == "title") {
				    if (isset ( $this->metadata->title )) $buffer = $this->metadata->title;
			    } elseif ($property == "metadata") {
				    if (isset ( $this->metadata->$parameter ["field"] )) $buffer = $this->metadata->$parameter ["field"];
			    } elseif ($property == "navigation") {
				    $menuList = new \Navigation\ItemList();
				    if ($menuList->error) {
					    $this->error = "Error initializing navigation module: " . $menuList->error;
					    return '';
				    }

				    $menus = $menuList->find ( array ("name" => $parameter ["name"] ) );
				    if ($menuList->error) {
					    app_log ( "Error displaying menus: " . $menuList->error, 'error', __FILE__, __LINE__ );
					    $this->error = $menuList->error;
					    return '';
				    }

				    $menu = $menus [0];

				    if (count ( $menu->item )) {
					    foreach ( $menu->item as $item ) {
						    if (isset ( $parameter ['class'] )) $button_class = $parameter ['class'];
						    else {
							    $button_class = "button_" . preg_replace ( "/\W/", "_", $menu->name );
						    }
						    $button_id = "button[" . $item->id . "]";
						    if (count ( $item->children )) {
							    $child_container_class = "child_container_" . preg_replace ( "/\W/", "_", $menu->name );
							    $child_container_id = "child_container[" . $item->id . "]";
							    $child_button_class = "child_button_" . preg_replace ( "/\W/", "_", $menu->name );

							    $buffer .= "<div" . " onMouseOver=\"expandMenu('$child_container_id')\"" . " onMouseOut=\"collapseMenu('$child_container_id')\"" . " id=\"$button_id\"" . " class=\"$button_class\"" . ">" . $item->title . "</div>\n";

							    $buffer .= "\t<div class=\"$child_container_class\" id=\"$child_container_id\">\n";
							    foreach ( $item->children as $child ) {
								    $buffer .= "\t\t" . "<a" . " onMouseOver=\"expandMenu('$child_container_id')\"" . " onMouseOut=\"collapseMenu('$child_container_id')\"" . ' href="' . $child->target . '"' . ' class="' . $child_button_class . '">' . $child->title . "</a>\n";
							    }
							    $buffer .= "\t</div>";
						    } else {
							    $buffer .= "<a" . " href=\"" . $item->target . "\"" . " class=\"$button_class\"" . ">" . $item->title . "</a>\n";
						    }
					    }
				    }
			    } elseif ($property == "message") {
				    $buffer .= "<div class=\"page_message\">" . $GLOBALS ['page_message'] . "</div>";
			    } elseif ($property == "error") {
				    $buffer .= "<div class=\"page_error\">" . $GLOBALS ['page_error'] . "</div>";
			    } elseif ($property == "not_authorized") {
				    $buffer .= "<div class=\"page_error\">Sorry, you are not authorized to see this view</div>";
			    } else {
				    $buffer = $this->loadViewFiles($buffer);
			    }
		    } elseif ($object == "navigation") {
			    if ($property == "menu") {
				    if ($parameter ['code']) {
					    $menu = new \Navigation\Menu ();
					    if ($menu->get ( $parameter ['code'] )) {
						    $buffer .= $menu->asHTML ( $parameter );
					    }
				    } else {
					    app_log ( "navigation menu references without code" );
				    }
			    } else {
				    $buffer = $this->loadViewFiles($buffer);
			    }
		    } elseif ($object == "content") {
			    if ($property == "index") {
				    app_log ( "content::index", 'trace', __FILE__, __LINE__ );
				    if (isset ( $parameters ['id'] ) && preg_match ( "/^\d+$/", $parameter ["id"] )) $target = $parameter ["id"];
				    else $target = $GLOBALS ['_REQUEST_']->query_vars_array [0];

				    $message = new \Content\Message ();
				    $message->get ( $target );
				    if ($message->error) $buffer = "Error: " . $message->error;
				    elseif (! $message->id) {
					    app_log ( "Message not found matching '$target', adding", 'info', __FILE__, __LINE__ );
					    if (role ( 'content operator' )) {
						    $message->add ( array ("target" => $target ) );
					    } else {
						    $buffer = "Sorry, the page you requested was not found";
						    app_log ( "Page not found: $target", 'error', __FILE__, __LINE__ );
					    }
				    }
				    if ($message->cached) {
					    header ( "X-Object-Cached: true" );
				    }
				    if ($message->id) {
					    // Make Sure User Has Privileges
					    if (is_object ( $GLOBALS ['_SESSION_']->customer ) && $GLOBALS ['_SESSION_']->customer->id && $GLOBALS ['_SESSION_']->customer->can ( 'edit content messages' )) {
						    $origin_id = uniqid ();
						    $buffer .= '<script language="Javascript">function editContent(object,origin,id) { var textEditor=window.open("/_admin/text_editor?object="+object+"&origin="+origin+"&id="+id,"","width=800,height=600,left=20,top=20,status=0,toolbar=0"); }; function highlightContent(contentElem) { document.getElementById(\'contentElem\').style.border = \'1px solid red\'; }; function blurContent(contentElem) { document.getElementById(\'contentElem\').style.border = \'0px\'; } </script>';
						    $buffer .= "<div>";
						    $buffer .= '<div id="r7_widget[' . $origin_id . ']">' . $message->content . '</div>';
						    #$buffer .= '<a class="porkchop_edit_button" href="javascript:void(0)" onclick="editContent(\'content\',\'' . $origin_id . '\',\'' . $message->id . '\')" onmouseover="highlightContent(\'content\');" onmouseout="blurContent(\'content\');">Edit</a>';
						    $buffer .= '<a href="/_content/edit?id='.$message->id.'">Edit</a>';
                            $buffer .= "</div>";
					    } else {
						    $buffer .= $message->content;
					    }
				    }
			    } else {
				    $buffer = $this->loadViewFiles($buffer);
			    }
		    } elseif ($object == "product") {
			    // Load Product Class if Not Already Loaded
			    if ($property == "thumbnail") {
				    $id = $this->query_vars;
				    $product = new \Product\Item ( $id );
				    if (! $id) {
					    $category = $product->defaultCategory ();
					    if ($product->error) {
						    print $product->error;
						    exit ();
					    }
				    } else {
					    $category = $product->details ( $id );
				    }
					$productList = new \Product\ItemList();
				    $products = $productList->find ( array ("category" => $category->code ) );

				    // Loop Through Products
				    foreach ( $products as $product ) {
					    $buffer .= "<r7_product.detail format=thumbnail id=".$product->id.">";
				    }
			    } elseif ($property == "detail") {
				    if (preg_match ( "/^\d+$/", $parameter ["id"] )) $id = $parameter ["id"];
				    elseif ($this->query_vars) $id = $this->query_vars;

				    $product = new \Product\Item( $id );
				    if ($parameter ["format"] == "thumbnail") {
					    if ($product->type->group) {
						    $buffer .= "<div id=\"product[" . $parameter ["id"] . "]\" class=\"product_thumbnail\">\n";
						    $buffer .= "\t<a href=\"/_product/thumbnail/" . $product->id . "\" class=\"product_thumbnail_name\">" . $product->name . "</a>\n";
						    $buffer .= "\t<div class=\"product_thumbnail_description\">" . $product->description . "</div>\n";
						    $buffer .= "\t<div class=\"product_thumbnail_retail\">" . $product->retail . "</div>\n";
						    if ($product->images ["0"]->files->thumbnail->path) $buffer .= "\t\t<img src=\"" . $product->images ["0"]->files->thumbnail->path . "\" class=\"product_thumbnail_image\"/>\n";
						    $buffer .= "</div>\n";
					    } else {
						    $buffer .= "<div id=\"product[" . $parameter ["id"] . "]\" class=\"product_thumbnail\">\n";
						    $buffer .= "\t<a href=\"/_product/detail/" . $product->id . "\" class=\"product_thumbnail_name\">" . $product->name . "</a>\n";
						    $buffer .= "\t<div class=\"product_thumbnail_description\">" . $product->description . "</div>\n";
						    $buffer .= "\t<div class=\"product_thumbnail_retail\">" . $product->retail . "</div>\n";
						    if ($product->images ["0"]->files->thumbnail->path) $buffer .= "\t<div class=\"product_thumbnail_image\"><img src=\"" . $product->images ["0"]->files->thumbnail->path . "\" class=\"product_thumbnail_image\"/></div>\n";
						    $buffer .= "</div>\n";
					    }
				    } else {
					    $buffer .= "<div id=\"product[" . $parameter ["id"] . "]\" class=\"product_thumbnail\">\n";
					    $buffer .= "<a href=\"/_product/detail/" . $product->id . "\" class=\"product_thumbnail_name\">" . $product->name . "</a>\n";
					    $buffer .= "<div class=\"product_detail_description\">" . $product->description . "</div>\n";
					    $buffer .= "<div class=\"product_detail_retail\">" . $product->retail . "</div>\n";
					    if ($product->images ["0"]->files->large->path) $buffer .= "<img src=\"" . $product->images ["0"]->files->large->path . "\" class=\"product_thumbnail_image\"/>\n";
					    $buffer .= "</div>\n";
				    }
			    } elseif ($property == "navigation") {
				    if (preg_match ( "/^\d+$/", $parameter ["id"] )) $id = $parameter ["id"];
				    elseif ($this->query_vars) $id = $this->query_vars;

				    $_product = new \Product\Item();
				    if (! $id) {
					    $category = $_product->defaultCategory ();
					    if ($_product->error) {
						    print $_product->error;
						    exit ();
					    }
				    } else {
					    $category = $_product->details ( $id );
				    }
                    $productList = new \Product\ItemList();
				    $products = $productList->find( array ("category" => $category->code ) );

				    // Loop Through Products
				    foreach ( $products as $product_id ) {
					    $product = $_product->details ( $product_id );
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
		    } elseif ($object == "monitor") {
				$buffer = $this->loadViewFiles($buffer);
		    } elseif ($object == "session") {
			    if ($property == "customer_id") $buffer = $GLOBALS ['_SESSION_']->customer->id;
			    elseif ($property == "loggedin") {
				    if (isset ( $GLOBALS ['_SESSION_']->customer->id )) $buffer = "true";
				    else $buffer = "false";
			    } else {
				    $buffer = $this->loadViewFiles($buffer);
			    }
		    } elseif ($object == "register") {
			    if (isset ( $parameter ['id'] ) and preg_match ( "/^\d+$/", $parameter ["id"] )) $id = $parameter ["id"];
			    elseif (isset ( $this->query_vars )) $id = $this->query_vars;

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
		    } elseif ($object == "company") {
			    $companies = new \Company\CompanyList ();
			    list ( $company ) = $companies->find ();

			    if ($property == "name") {
				    $buffer .= $company->name;
			    } else {
                    $buffer = $this->loadViewFiles($buffer);
			    }
		    } elseif ($object == "news") {
			    if ($property == "events") {
				    $eventlist = new \News\EventList ();
				    if ($eventlist->error) {
					    $this->error = "Error fetching events: " . $eventlist->error;
				    } else {
					    $events = $eventlist->find ( array ('feed_id' => $parameter ['id'] ) );
					    if ($eventlist->error) {
						    $this->error = "Error fetching events: " . $eventlist->error;
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
		    } elseif ($object == "adminbar") {
			    if (role ( 'administrator' )) $buffer = "<div class=\"adminbar\" id=\"adminbar\" style=\"height:20px; width: 100%; position: absolute; top: 0px; left: 0px;\">Admin stuff goes here</div>\n";
		    } else {
                $buffer = $this->loadViewFiles($buffer);
		    }
		    return $buffer;
	    }
        public function loadViewFiles($buffer = "") {
		    ob_start ();
            if (isset($this->style)) {
                if (file_exists(MODULES.'/'.$this->module.'/'.$this->style.'/'.$this->view.'_mc.php'))
                    $be_file = MODULES.'/'.$this->module.'/'.$this->style.'/'.$this->view.'_mc.php';
                elseif (file_exists(MODULES.'/'.$this->module.'/default/'.$this->view.'_mc.php'))
                    $be_file = MODULES.'/'.$this->module.'/default/'.$this->view.'_mc.php';
                if (file_exists(MODULES . '/' . $this->module . '/' . $this->style . '/' . $this->view . '.php'))
                    $fe_file = MODULES . '/' . $this->module . '/' . $this->style . '/' . $this->view . '.php';
                elseif (file_exists(MODULES . '/' . $this->module . '/default/' . $this->view . '.php'))
                    $fe_file = MODULES . '/' . $this->module . '/default/' . $this->view . '.php';
            }
		    app_log ( "Loading view " . $this->view . " of module " . $this->module, 'debug', __FILE__, __LINE__ );
		    if (file_exists ( $be_file )) {
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
		    else app_log ( "Backend file '$be_file' for module " . $this->module . " not found" );
            if (file_exists ( $fe_file )) include ($fe_file);
		    $buffer .= ob_get_clean ();
            return $buffer;
        }
	    public function requires($role = '_customer') {
		    if ($role == '_customer') {
			    if ($GLOBALS ['_SESSION_']->customer->id) {
				    return true;
			    } else {
				    header ( "location: /_register/login?target=_" . $this->module . ":" . $this->view );
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
	    public function allMetadata() {
		    $metadataList = new \Site\Page\MetadataList();
			$metaArray = $metadataList->find(array('page_id' => $this->id));
		    if ($metadataList->error()) {
				$this->error = $metadataList->error();
			    return null;
		    }
		    return $metaArray;
	    }
		public function getMetadata($key) {
			$metadata = new \Site\Page\Metadata();
			if ($metadata->get($this->id,$key)) {
				return $metadata->value;
			}
			else {
				return null;
			}
		}

	    public function setMetadata($key, $value) {
		    if (! isset ( $this->id )) {
			    $this->addError ( "No page id" );
			    return false;
		    }
		    if (! isset ( $key )) {
			    $this->addError ( "Invalid key name in Site::Page::setMetadata()" );
			    return false;
		    }

		    $metadata = new \Site\Page\Metadata();
			$metadata->get($this->id,$key);
			if (! isset($value)) {
				$metadata->drop();
			}

		    if ($metadata->set($value)) return true;
			else $this->addError($metadata->error());
			return false;
	    }
	    public function unsetMetadata($key) {
			$metadata = new \Site\Page\Metadata();
            $metadata->get($this->id,$key);
		    return $metadata->drop();
	    }
	    public function addError($error) {
		    $trace = debug_backtrace ();
		    $caller = $trace [0];
		    $file = $caller ['file'];
		    $line = $caller ['line'];
		    app_log ( $error, 'error', $file, $line );
		    array_push ( $this->_errors, $error );
	    }
	    public function errorString($delimiter = "<br>\n") {
		    if (isset ( $this->error )) {
			    array_push ( $this->_errors, $this->error );
		    }
		    $error_string = '';
		    foreach ( $this->_errors as $error ) {
			    if (strlen ( $error_string )) {
				    $error_string .= $delimiter;
			    }
			    if (preg_match ( '/SQL\sError/', $error )) {
				    // SQL errors in the error log, then output to page is standard "site error message"
				    app_log ( $error, 'error' );
				    $error_string .= "Internal site error";
			    } else {
				    $error_string .= $error;
			    }
		    }
		    return $error_string;
	    }
	    public function errors() {
		    return $this->_errors;
	    }
	    public function errorCount() {
		    if (empty ( $this->errors )) $this->errors = array ();
		    if (! empty ( $this->error )) array_push ( $this->errors, $this->error );
		    return count ( $this->_errors );
	    }
    }
