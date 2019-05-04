<?php
	namespace Site;

    class Page {
    
		public $id;
		public $module = 'content';
		public $view = 'index';
		public $index = '';
		public $style = 'default';
		public $auth_required = 0;
		public $ssl_required;
		public $error;
		public $uri;
		public $title;
		public $metadata;
		public $template;
		public $success;
		private $_errors = array();

        public function __construct () {
			$args = func_get_args();
			if (func_num_args() == 1 && gettype($args[0]) == "string") {
				$this->id = $args[0];
				$this->details();
			}
			elseif (func_num_args() == 1 && gettype($args[0]) == "array") {
				if ($args[0]['method']) {
					$this->method = $args[0]['method'];
					$this->view = $args[0]['view'];
					if ($args[0]['index']) {
						$this->index = $args[0]['index'];
					}
				}
			}
			elseif (func_num_args() == 2 && gettype($args[0]) == "string" && gettype($args[1]) == "string") {
				$this->get($args[0],$args[1]);
			}

		}

		public function fromRequest() {
			return $this->get(
				$GLOBALS['_REQUEST_']->module,
				$GLOBALS['_REQUEST_']->view,
				$GLOBALS['_REQUEST_']->index
			);
		}

		public function applyStyle() {
			if (isset($GLOBALS['_config']->style[$this->module]))
				$this->style = $GLOBALS['_config']->style[$this->module];
		}

		public function requireRole($role) {
			if ($this->module == 'register' && $this->view == 'login') {
				# Do Nothing, Where Here
			}
			elseif (! $GLOBALS['_SESSION_']->customer->id) {
				header('location: /_register/login?return=true&module='.$this->module.'&view='.$this->view);
				exit;
			}
			elseif (! $GLOBALS['_SESSION_']->customer->has_role($role)) {
				header('location: /_register/permission_denied');
				exit;
			}
		}

		public function get($module,$view,$index = null) {
			$parameters = array($module,$view);
			if (strlen($index) < 1) $index = null;

			# Prepare Query
			$get_object_query = "
				SELECT	id
				FROM	page_pages
				WHERE	module = ?
				AND		view = ?
			";
			if (isset($index)) {
				$get_object_query .= "
				AND		`index` = ?
				";
				array_push($parameters,$index);
			}
			else {
				$get_object_query .= "
				AND		(`index` is null or `index` = '')
				";
			}
			query_log($get_object_query);
			app_log(print_r($parameters,true));
			$rs = $GLOBALS['_database']->Execute(
				$get_object_query,
				$parameters
			);
			if (! $rs) {
				$this->addError("SQL Error in Page::get: ".$GLOBALS['_database']->ErrorMsg());
				return null;
			}
			list($id) = $rs->FetchRow();

			if (is_numeric($id)) {
				$this->id = $id;
				return $this->details();
			}
			else {
				return false;
			}
			return true;
		}

		public function add($module = '',$view = '',$index = '') {
			# Apply optional parameters
			if ($module) {
				$this->module = $module;
				if ($view) {
					$this->view = $view;
					if ($index) {
						$this->index = $index;
					}
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
			$GLOBALS['_database']->Execute(
				$add_object_query,
				array(
					$this->module,
					$this->view,
					$this->index
				)
			);
			if ($GLOBALS['_database']->ErrorMsg()) {
				$this->error = "SQL Error in Site::Page::add(): ".$GLOBALS['_database']->ErrorMsg();
				return null;
			}
			$id = $GLOBALS['_database']->Insert_ID();
			app_log("Added page id ".$id);
			$this->details($id);
		}

		public function details() {
			$get_details_query = "
				SELECT	*
				FROM	page_pages
				WHERE	id = ?
			";
			$rs = $GLOBALS['_database']->Execute(
				$get_details_query,
				array($this->id)
			);
			if (! $rs) {
				$this->error = "SQL Error in Site::Page::details(): ".$GLOBALS['_database']->ErrorMsg();
				return null;
			}
			$object = $rs->FetchNextObject(false);
			if (gettype($object) == 'object') {
				$this->module = $object->module;
				$this->view = $object->view;
				$this->index = $object->index;
			}
			else {
				# Just Let The Defaults Go
			}
			if (isset($GLOBALS['_config']->style[$this->module])) {
				$this->style = $GLOBALS['_config']->style[$this->module];
			}

            # Make Sure Authentication Requirements are Met
            if (($this->auth_required) and (! $GLOBALS["_SESSION_"]->customer->id)) {
				if (($this->module != "register")
					or	(! in_array($this->view,array('login','forgot_password','register','email_verify','resend_verify','invoice_login','thank_you')))
				)
				{
					# Clean Query Vars for this
					$auth_query_vars = preg_replace("/\/$/","",$this->query_vars);

					if ($this->module == 'content' && $this->view == 'index' && ! $auth_query_vars)
						$auth_target = '';
					else {
						$auth_target = ":_".$this->module.":".$this->view;
						if ($auth_query_vars) $auth_target .= ":".$auth_query_vars;
						$auth_target = urlencode($auth_target);
					}

					# Build New URL
					header("location: ".PATH."/_register/login/".$auth_target);

					$this->error = "Authorization Required - Requirements not Met";
					return null;
				}
			}

			# Load View Metadata
			$_metadata = new \Site\Page\Metadata();
			$this->metadata = $_metadata->all($this->id);

			# Pull Key Metadata
			$this->template = $_metadata->get('template');
			
			return true;
    	}

		public function load_template() {
			if (isset($this->metadata->template)) {
				app_log("Loading template '".$this->metadata->template."' from page metadata",'debug',__FILE__,__LINE__);
				$html = file_get_contents(HTML."/".$this->metadata->template);
			}
			elseif (file_exists(HTML."/".$this->module.".".$this->view.".html")) {
				app_log("Loading template '"."/".$this->module.".".$this->view.".html'",'debug',__FILE__,__LINE__);
				$html = file_get_contents(HTML."/".$this->module.".".$this->view.".html");
			}
			elseif ($this->view == 'api' && file_exists(HTML."/_api.html")) {
				app_log("Loading template '_api.html'",'debug',__FILE__,__LINE__);
				$html = file_get_contents(HTML."/_api.html");
			}
			elseif (file_exists(HTML."/".$this->module.".html")) {
				app_log("Loading template '"."/".$this->module.".html'",'debug',__FILE__,__LINE__);
				$html = file_get_contents(HTML."/".$this->module.".html");
			}
			elseif (isset($GLOBALS['_config']->site->default_template)) {
				app_log("Loading template '".$GLOBALS['_config']->site->default_template."'",'debug',__FILE__,__LINE__);
				if (! file_exists(HTML."/".$GLOBALS['_config']->site->default_template)) {
					app_log("Default template file not found!",'error',__FILE__,__LINE__);
				}
				$html = file_get_contents(HTML."/".$GLOBALS['_config']->site->default_template);
			}
			elseif (file_exists(HTML."/index.html")) {
				app_log("Loading template '/index.html'",'debug',__FILE__,__LINE__);
				$html = file_get_contents(HTML."/index.html");
			}
			elseif (file_exists(HTML."/install.html"))
				$html = file_get_contents(HTML."/install.html");
			else $html = '<r7 object="page" property="view"/>';
			return $this->parse($html);
		}

		public function parse($message) {
			$module_pattern = "/<r7(\s[\w\-]+\=\"[^\"]*\")*\/>/is";
			while (preg_match($module_pattern,$message,$matched)) {
				$search = $matched[0];
				$parse_message = "Replaced $search";
				$replace_start = microtime(true);
				$replace = $this->replace($matched[0]);
				#app_log($parse_message." with $replace in ".sprintf("%0.4f",(microtime(true) - $replace_start))." seconds",'debug',__FILE__,__LINE__);
				$message = str_replace($search,$replace,$message);
			}

			# Return Messsage
			return $message;
		}

		private function parse_element($string) {
			# Initialize Array to hold Parameters
			$parameters = array();

			# Grab Parameters from Element
			preg_match('/^<r7\s(.*)\/>/',$string,$matches);
			$string = $matches[1];

			# Tokenize Parameters
			while(strlen($string) > 0) {
				# Trim Leading Space
				$string = ltrim($string);

				# Grab Parameter Name
				list($name,$string) = preg_split('/\=/',$string,2);

				# Grab Parameter Value (with optional surrounding double quotes)
				if (substr($string,0,1) == '"')
					list($value,$string) = preg_split('/\"/',substr($string,1),2);
				else
					list($value,$string) = preg_split('/\s/',$string,2);

				# Store Parameter in Array
				$parameters[$name] = $value;
			}
			return $parameters;
		}

		public function replace($string) {
			# Initialize Replacement Buffer
			$buffer = '';

			# Parse Token
			$parameter = $this->parse_element($string);

			if (array_key_exists('module',$parameter)) $module = $parameter['module'];
			if (array_key_exists('object',$parameter)) $object = $parameter['object'];
			if (array_key_exists('property',$parameter)) $property = $parameter['property'];

			app_log("Object: $object Property: $property",'debug',__FILE__,__LINE__);
			if ($object == "constant") {
				if ($property == "path") {
					$buffer .= PATH;
				}
				elseif ($property == "date") {
					$buffer .= date('m/d/Y h:i:s');
				}
				elseif ($property == "host") {
					$buffer .= $_SERVER['HTTP_HOST'];
				}
			}
			elseif ($object == "page") {
				if ($property == "view") {
					$buffer = "<r7 object=\"".$this->module."\" property=\"".$this->view."\"/>";
				}
				elseif ($property == "title") {
					if (isset($this->metadata->title)) $buffer = $this->metadata->title;
				}
				elseif ($property == "metadata") {
					if (isset($this->metadata->$parameter["field"])) $buffer = $this->metadata->$parameter["field"];
				}
				elseif ($property == "navigation") {
					$menuList = new \Site\MenuList();
					if ($menuList->error) {
						$this->error = "Error initializing navigation module: ".$menuList->error;
						return '';
					}

					$menus = $menuList->find(array("name" => $parameter["name"]));
					if ($menuList->error) {
						app_log("Error displaying menus: ".$menuList->error,'error',__FILE__,__LINE__);
						$this->error = $menuList->error;
						return '';
					}

					$menu = $menus[0];

					if (count($menu->item)) {
						foreach($menu->item as $item) {
							if (isset($parameter['class']))
								$button_class = $parameters['class'];
							else {
								$button_class	= "button_".preg_replace("/\W/","_",$menu->name);
							}
							$button_id		= "button[".$item->id."]";
							if (count($item->children)) {
								$child_container_class	= "child_container_".preg_replace("/\W/","_",$menu->name);
								$child_container_id		= "child_container[".$item->id."]";
								$child_button_class		= "child_button_".preg_replace("/\W/","_",$menu->name);

								$buffer .=
								    "<div"
									." onMouseOver=\"expandMenu('$child_container_id')\""
									." onMouseOut=\"collapseMenu('$child_container_id')\""
									." id=\"$button_id\""
									." class=\"$button_class\""
								    .">"
									.$item->title
								    ."</div>\n"
								;

								$buffer .= "\t<div class=\"$child_container_class\" id=\"$child_container_id\">\n";
								foreach($item->children as $child) {
									$buffer .= "\t\t"
									."<a"
									." onMouseOver=\"expandMenu('$child_container_id')\""
									." onMouseOut=\"collapseMenu('$child_container_id')\""
									.' href="'.$child->target.'"'
									.' class="'.$child_button_class.'">'.$child->title."</a>\n";
								}
								$buffer .= "\t</div>";
							}
							else {
								$buffer .=
								    "<a"
									." href=\"".$item->target."\""
									." class=\"$button_class\""
								    .">"
									.$item->title
								    ."</a>\n"
								;
							}
						}
					}
				}
				elseif ($property == "message") {
					$buffer .= "<div class=\"page_message\">".$GLOBALS['page_message']."</div>";
				}
				elseif ($property == "error") {
					$buffer .= "<div class=\"page_error\">".$GLOBALS['page_error']."</div>";
				}
				elseif ($property == "not_authorized") {
					$buffer .= "<div class=\"page_error\">Sorry, you are not authorized to see this view</div>";
				}
				else {
					app_log("Loading ".MODULES.'/'.$this->module.'/'.$this->style.'/'.$this->view,'debug',__FILE__,__LINE__);
					ob_start();
					$be_file = MODULES.'/'.$this->module.'/'.$this->style.'/'.$this->view.'_mc.php';
					$fe_file = MODULES.'/'.$this->module.'/'.$this->style.'/'.$this->view.'.php';
					if (file_exists($be_file)) include($be_file);
					if (file_exists($fe_file)) include($fe_file);
					$buffer .= ob_get_clean();
				}
			}
			elseif ($object == "content") {
				if ($property == "index") {
					app_log("content::index",'trace',__FILE__,__LINE__);
					if (isset($parameters['id']) && preg_match("/^\d+$/",$parameter["id"])) $target = $parameter["id"];
					else $target = $GLOBALS['_REQUEST_']->query_vars_array[0];

					$message = new \Content\Message();
					$message->get($target);
					if ($message->error)
						$buffer = "Error: ".$message->error;
					elseif (! $message->id) {
						app_log("Message not found matching '$target', adding",'info',__FILE__,__LINE__);
						if (role('content operator')) {
							$message->add(array("target" => $target));
						}
						else {
							$buffer = "Sorry, the page you requested was not found";
							app_log("Page not found: $target",'error',__FILE__,__LINE__);
						}
					}
					if ($message->cached) {
						header("X-Object-Cached: true");
					}
					if ($message->id) {
						# Make Sure User Has Privileges
						if (is_object($GLOBALS['_SESSION_']->customer) && $GLOBALS['_SESSION_']->customer->id && $GLOBALS['_SESSION_']->customer->has_role('content operator')) {
							$origin_id = uniqid();
							$buffer .= '<script language="Javascript">function editContent(object,origin,id) { var textEditor=window.open("/_admin/text_editor?object="+object+"&origin="+origin+"&id="+id,"","width=800,height=600,left=20,top=20,status=0,toolbar=0"); }; function highlightContent(contentElem) { document.getElementById(contentElem).style.border = \'1px solid red\'; }; function blurContent(contentElem) { document.getElementById(contentElem).style.border = \'0px\'; } </script>';
							$buffer .= "<div>";
							$buffer .= '<div id="r7_widget['.$origin_id.']">'.$message->content.'</div>';
							$buffer .= '<a class="porkchop_edit_button" href="javascript:void(0)" onclick="editContent(\'content\',\''.$origin_id.'\',\''.$message->id.'\')" onmouseover="highlightContent(\'content\');" onmouseout="blurContent(\'content\');">Edit</a>';
							$buffer .= "</div>";
						}
						else {
							$buffer .= $message->content;
						}
					}
				}
				else {
					app_log("Loading ".MODULES.'/'.$this->module.'/'.$this->style.'/'.$this->view,'debug',__FILE__,__LINE__);
					ob_start();
					include(MODULES.'/'.$this->module.'/'.$this->style.'/'.$this->view.'_mc.php');
					include(MODULES.'/'.$this->module.'/'.$this->style.'/'.$this->view.'.php');
					$buffer .= ob_get_clean();
				}
			}
			elseif ($object == "product") {
				# Load Product Class if Not Already Loaded
				if ($property == "thumbnail") {
					$id = $this->query_vars;
					$product = new \Product\Item($id);
					if (! $id) {
						$category = $product->defaultCategory();
						if ($product->error) {
							print $product->error;
							exit;
						}
					}
					else {
						$category = $_product->details($id);
					}
					$products = $_product->find(array("category" => $category->code));

					# Loop Through Products
					foreach ($products as $product_id) {
						#$product = $_product->details($product_id);
						$buffer .= "<r7_product.detail format=thumbnail id=$product_id>";
					}
				}
				elseif ($property == "detail") {
		 			if (preg_match("/^\d+$/",$parameter["id"])) $id = $parameter["id"];
					elseif ($this->query_vars) $id = $this->query_vars;

					$product = new Product($id);
					if ($parameter["format"] == "thumbnail") {
						if ($product->type->group) {
							$buffer .= "<div id=\"product[".$parameter["id"]."]\" class=\"product_thumbnail\">\n";
							$buffer .= "\t<a href=\"/_product/thumbnail/".$product->id."\" class=\"product_thumbnail_name\">".$product->name."</a>\n";
							$buffer .= "\t<div class=\"product_thumbnail_description\">".$product->description."</div>\n";
							$buffer .= "\t<div class=\"product_thumbnail_retail\">".$product->retail."</div>\n";
							if ($product->images["0"]->files->thumbnail->path)
								$buffer .= "\t\t<img src=\"".$product->images["0"]->files->thumbnail->path."\" class=\"product_thumbnail_image\"/>\n";
							$buffer .= "</div>\n";
						}
						else {
							$buffer .= "<div id=\"product[".$parameter["id"]."]\" class=\"product_thumbnail\">\n";
							$buffer .= "\t<a href=\"/_product/detail/".$product->id."\" class=\"product_thumbnail_name\">".$product->name."</a>\n";
							$buffer .= "\t<div class=\"product_thumbnail_description\">".$product->description."</div>\n";
							$buffer .= "\t<div class=\"product_thumbnail_retail\">".$product->retail."</div>\n";
							if ($product->images["0"]->files->thumbnail->path)
								$buffer .= "\t<div class=\"product_thumbnail_image\"><img src=\"".$product->images["0"]->files->thumbnail->path."\" class=\"product_thumbnail_image\"/></div>\n";
							$buffer .= "</div>\n";
						}
					}
					else {
						$buffer .= "<div id=\"product[".$parameter["id"]."]\" class=\"product_thumbnail\">\n";
						$buffer .= "<a href=\"/_product/detail/".$product->id."\" class=\"product_thumbnail_name\">".$product->name."</a>\n";
						$buffer .= "<div class=\"product_detail_description\">".$product->description."</div>\n";
						$buffer .= "<div class=\"product_detail_retail\">".$product->retail."</div>\n";
						if ($product->images["0"]->files->large->path)
							$buffer .= "<img src=\"".$product->images["0"]->files->large->path."\" class=\"product_thumbnail_image\"/>\n";
						$buffer .= "</div>\n";
					}
				}
				elseif ($property == "navigation") {
					if (preg_match("/^\d+$/",$parameter["id"])) $id = $parameter["id"];
					elseif ($this->query_vars) $id = $this->query_vars;

					$_product = new Product();
					if (! $id) {
						$category = $_product->defaultCategory();
						if ($_product->error) {
							print $_product->error;
					 		exit;
						}
					}
					else {
						$category = $_product->details($id);
					}
					$products = $_product->find(array("category" => $category->code));
		    
					# Loop Through Products
					foreach ($products as $product_id) {
						$product = $_product->details($product_id);
						if ($product->type->group) {
							$buffer .= "<div id=\"product_navigation[".$parameter["id"]."]\" class=\"product_navigation\">\n";
							$buffer .= "<a href=\"/_product/thumbnail/".$product->id."\" class=\"product_navigation_name\">".$product->name."</a>\n";
							if ($product->images["0"]->files->icon->path)
								$buffer .= "<img src=\"".$product->images["0"]->files->icon->path."\" class=\"product_navigation_image\"/>\n";
							$buffer .= "</div>\n";
						}
						else {
							$buffer .= "<div id=\"product_navigation[".$parameter["id"]."]\" class=\"product_navigation\">\n";
							$buffer .= "<a href=\"/_product/detail/".$product->id."\" class=\"product_navigation_name\">".$product->name."</a>\n";
							if ($product->images["0"]->files->icon->path)
								$buffer .= "<img src=\"".$product->images["0"]->files->icon->path."\" class=\"product_navigation_image\"/>\n";
							$buffer .= "</div>\n";
						}
					}
				}
				else {
					app_log("Loading ".MODULES.'/'.$this->module.'/'.$this->style.'/'.$this->view,'debug',__FILE__,__LINE__);
					ob_start();
					include(MODULES.'/'.$this->module.'/'.$this->style.'/'.$this->view.'_mc.php');
					include(MODULES.'/'.$this->module.'/'.$this->style.'/'.$this->view.'.php');
					$buffer .= ob_get_clean();
				}
			}
			elseif ($object == "monitor") {
				# Load Product Class if Not Already Loaded
			#	if ($property == "dashboard") {
			#		$buffer .= $GLOBALS['_config']->monitor->default_dashboard;
			#	}
			#	else {
					app_log("Loading ".MODULES.'/'.$this->module.'/'.$this->style.'/'.$this->view,'debug',__FILE__,__LINE__);
					ob_start();
					include(MODULES.'/'.$this->module.'/'.$this->style.'/'.$this->view.'_mc.php');
					include(MODULES.'/'.$this->module.'/'.$this->style.'/'.$this->view.'.php');
					$buffer .= ob_get_clean();
			#	}
			}
			elseif ($object == "session") {
				if ($property == "customer_id") $buffer = $GLOBALS['_SESSION_']->customer->id;
				elseif ($property == "loggedin") {
					if (isset($GLOBALS['_SESSION_']->customer->id)) $buffer = "true";
					else $buffer = "false";
				}
				else {
					ob_start();
					include(MODULES.'/'.$this->module.'/'.$this->style.'/'.$this->view.'_mc.php');
					include(MODULES.'/'.$this->module.'/'.$this->style.'/'.$this->view.'.php');
					$buffer .= ob_get_clean();
				}
			}
			elseif ($object == "register") {
				if (isset($parameter['id']) and preg_match("/^\d+$/",$parameter["id"])) $id = $parameter["id"];
				elseif (isset($this->query_vars)) $id = $this->query_vars;

				if ($property == "user") {
					if ($parameter['field'] == "name") {
						$customer = new \Register\Customer($GLOBALS['_SESSION_']->customer->id);
						$buffer .= $customer->first_name." ".$customer->last_name;
					}
				}
				elseif ($property == "welcomestring") {
					if ($GLOBALS['_SESSION_']->customer) {
						$buffer .= "<span class=\"register_welcomestring\">Welcome ".$GLOBALS['_SESSION_']->customer->first_name." ".$GLOBALS['_SESSION_']->customer->last_name."</span>";
					}
					else {
						$buffer .= "<a class=\"register_welcomestring\" href=\"".PATH."/_register/login\">Log In</a>";
					}
				}
				else {
					app_log("Loading ".MODULES.'/'.$this->module.'/'.$this->style.'/'.$this->view.'_mc.php');
					ob_start();
					include(MODULES.'/'.$this->module.'/'.$this->style.'/'.$this->view.'_mc.php');
					include(MODULES.'/'.$this->module.'/'.$this->style.'/'.$this->view.'.php');
					$buffer .= ob_get_clean();
				}
			}
			elseif ($object == "company") {
				$companies = new \Company\CompanyList();
				list($company) = $companies->find();

				if ($property == "name") {
					$buffer .= $company->name;
				}
				else
				{
					#error_log("Loading ".MODULES.'/'.$this->module.'/'.$this->style.'/'.$this->view.'_mc.php');
					ob_start();
					include(MODULES.'/'.$this->module.'/'.$this->style.'/'.$this->view.'_mc.php');
					include(MODULES.'/'.$this->module.'/'.$this->style.'/'.$this->view.'.php');
					$buffer .= ob_get_clean();
				}
			}
			elseif ($object == "news") {
				if ($property == "events") {
					$eventlist = new \News\EventList();
					if ($eventlist->error) {
						$this->error = "Error fetching events: ".$eventlist->error;
					}
					else {
						$events = $eventlist->find(array('feed_id' => $parameter['id']));
						if ($eventlist->error) {
							$this->error = "Error fetching events: ".$eventlist->error;
						}
						else if (count($events)) {
							foreach ($events as $event) {
								$buffer .= "<a class=\"value ".$greenbar."newsWidgetEventValue\" href=\"".PATH."/_news/event/".$event->id."\">".$event->name."</a>";
								if ($greenbar)
									$greenbar = '';
								else
									$greenbar = 'greenbar ';
							}
							$buffer .= "<a class=\"value newsWidgetEventValue newsWidgetAddLink\" href=\"".PATH."/_news/new_event"."\">Add</a>";
						}
					}
				}
				else {
					#error_log("Loading ".MODULES.'/'.$this->module.'/'.$this->style.'/'.$this->view.'_mc.php');
					ob_start();
					include(MODULES.'/'.$this->module.'/'.$this->style.'/'.$this->view.'_mc.php');
					include(MODULES.'/'.$this->module.'/'.$this->style.'/'.$this->view.'.php');
					$buffer .= ob_get_clean();
				}
			}
			elseif ($object == "adminbar") {
				if (role('administrator'))
				$buffer = "<div class=\"adminbar\" id=\"adminbar\" style=\"height:20px; width: 100%; position: absolute; top: 0px; left: 0px;\">Admin stuff goes here</div>\n";
			}
			else {
				ob_start();
				app_log("Loading view ".$this->view." of module ".$this->module,'debug',__FILE__,__LINE__);
				$be_file = MODULES.'/'.$this->module.'/'.$this->style.'/'.$this->view.'_mc.php';
				$fe_file = MODULES.'/'.$this->module.'/'.$this->style.'/'.$this->view.'.php';
				if (file_exists($be_file)) include($be_file);
				if (file_exists($fe_file)) include($fe_file);
				$buffer .= ob_get_clean();
			}
			return $buffer;
		}
		public function requires($role = '_customer') {
			if ($role == '_customer') {
				if ($GLOBALS['_SESSION_']->customer->id) {
					return true;
				}
				else {
					header("location: /_register/login?target=_".$this->module.":".$this->view);
					ob_flush();
					exit;
				}
			}
			elseif ($GLOBALS['_SESSION_']->customer->has_role($role)) {
				return true;
			}
			else {
				header("location: /_register/not_authorized");
				ob_flush();
				exit;
			}
		}

		public function metadata() {
			$get_data_query = "
				SELECT	`key`,
						`value`
				FROM	page_metadata
				WHERE	page_id = ?
			";

			$rs = $GLOBALS['_database']->Execute(
				$get_data_query,
				array($this->id)
			);

			if (! $rs) {
				$this->error = "SQL Error in Site::Page::metadata(): ".$GLOBALS['_database']->ErrorMsg();
				return null;
			}
			$metadata = array();
			while(list($key,$value) = $rs->FetchRow()) {
				array_push($metadata,array($key,$value));
			}
			return $metadata;
		}

		public function setMetadata($key,$value) {
			if (! isset($this->id)) {
				$this->addError("No page id");
				return false;
			}
            if (! preg_match('/^\d+$/',$this->id)) {
                $this->addError("Invalid page id in Site::Page::setMetadata()");
                return false;
            }
            if (! isset($key)) {
                $this->addError("Invalid key name in Site::Page::setMetadata()");
                return false;
            }

            $set_data_query = "
                REPLACE
                INTO    page_metadata
                (       page_id,`key`,value)
                VALUES
                (       ?,?,?)
            ";
            $GLOBALS['_database']->Execute(
                $set_data_query,
                array($this->id,$key,$value)
            );
            if ($GLOBALS['_database']->ErrorMsg()) {
                $this->addError("SQL Error setting metadata in Site::Page::setMetadata(): ".$GLOBALS['_database']->ErrorMsg());
                return false;
            }
            return true;
		}

		public function unsetMetadata($key) {
            if (! preg_match('/^\d+$/',$this->id)) {
                $this->error = "Invalid page id in Site::Page::unsetMetadata(): ";
                return null;
            }
            if (! isset($key)) {
                $this->error = "Invalid key name in Site::Page::unsetMetadata(): ";
                return null;
            }

            $set_data_query = "
                DELETE
                FROM    page_metadata
				WHERE	page_id = ?
				AND		`key` = ?
            ";
            $GLOBALS['_database']->Execute(
                $set_data_query,
                array($this->id,$key)
            );
            if ($GLOBALS['_database']->ErrorMsg()) {
                $this->error = "SQL Error setting metadata in Site::Page::unsetMetadata(): ".$GLOBALS['_database']->ErrorMsg();
                return null;
            }
            return $value;
		}

		public function addError($error) {
			array_push($this->_errors,$error);		
		}

		public function errorString($delimiter = "<br>\n") {
			if (count($this->_errors)) return join($delimiter,$this->_errors);
			return $this->error;
		}

		public function errors() {
			return $this->_errors;
		}

		public function errorCount() {
			if ($this->error) array_push($this->error);
			return count($this->_errors);
		}
	}
?>
