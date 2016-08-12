<?php
    class Page
    {
        public $module;
        public $view;
		public $index;
        public $style;
        public $auth_required;
        public $ssl_required;
        public $error;
		public $uri;

        public function __construct ($params = array())
        {
			# Initialize Schema
			$init = new PageInit();
			if (isset($init->error))
			{
				$this->error = "Error initializing Page schema: ".$init->error;
				return null;
			}
		}

		public function get($module = '',$view = '',$index = '')
		{
			if (! $module) $module = $this->module;
			if (! $view) $view = $this->view;
			if (! $index) $index = $this->index;
			if (! isset($module,$view))
			{
				$this->error = "Missing required parameter in Page::get";
				return null;
			}
			if (! isset($index)) $index = '';

			$get_object_query = "
				SELECT	id
				FROM	page_pages
				WHERE	module = ?
				AND		view = ?
				AND		`index` = ?
			";
			$rs = $GLOBALS['_database']->Execute(
				$get_object_query,
				array($module,$view,$index)
			);
			if (! $rs)
			{
				$this->error = "SQL Error in Page::get: ".$GLOBALS['_database']->ErrorMsg();
				return null;
			}
			list($id) = $rs->FetchRow();
			if (! $id)
			{
				$page = $this->add($module,$view,$index);
				if ($this->error) return null;
				$id = $page->id;
			}
			return $this->details($id);
		}

		public function add($module,$view,$index)
		{
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
				array($module,$view,$index)
			);
			if ($GLOBALS['_database']->ErrorMsg())
			{
				$this->error = "SQL Error in Page::add: ".$GLOBALS['_database']->ErrorMsg();
				return null;
			}
			$id = $GLOBALS['_database']->Insert_ID();
			$this->details($id);
		}

		public function details($id)
		{
            # Make Sure Authentication Requirements are Met
            if (($this->auth_required) and (! $GLOBALS["_SESSION_"]->customer->id))
            {
				if (($this->module != "register")
					or	(! in_array($this->view,array('login','forgot_password','register','email_verify','resend_verify','invoice_login','thank_you')))
				)
				{
					# Clean Query Vars for this
					$auth_query_vars = preg_replace("/\/$/","",$this->query_vars);

					if ($this->module == 'content' && $this->view == 'index' && ! $auth_query_vars)
						$auth_target = '';
					else
					{
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
			$_metadata = new PageMetadata();
			$this->metadata = $_metadata->all($id);
    	}

		public function load_template()
		{
			if (isset($this->metadata->template))
			{
				app_log("Loading template '".$this->metadata->template."' from page metadata",'debug',__FILE__,__LINE__);
				$html = file_get_contents(HTML."/".$this->metadata->template);
			}
			elseif (file_exists(HTML."/".$this->module.".".$this->view.".html"))
			{
				app_log("Loading template '"."/".$this->module.".".$this->view.".html'",'debug',__FILE__,__LINE__);
				$html = file_get_contents(HTML."/".$this->module.".".$this->view.".html");
			}
			elseif (file_exists(HTML."/".$this->module.".html"))
			{
				app_log("Loading template '"."/".$this->module.".html'",'debug',__FILE__,__LINE__);
				$html = file_get_contents(HTML."/".$this->module.".html");
			}
			elseif (file_exists(HTML."/index.html"))
			{
				app_log("Loading template '/index.html'",'debug',__FILE__,__LINE__);
				$html = file_get_contents(HTML."/index.html");
			}
			elseif (file_exists(HTML."/install.html"))
				$html = file_get_contents(HTML."/install.html");
			else $html = '';
			return $this->parse($html);
		}

		public function parse($message)
		{
			$module_pattern = "/<r7(\s[\w\-]+\=\"[^\"]*\")*\/>/is";
			while (preg_match($module_pattern,$message,$matched))
			{
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

		private function parse_element($string)
		{
			# Initialize Array to hold Parameters
			$parameters = array();

			# Grab Parameters from Element
			preg_match('/^<r7\s(.*)\/>/',$string,$matches);
			$string = $matches[1];

			# Tokenize Parameters
			while(strlen($string) > 0)
			{
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
			#app_log(print_r($parameters,true),'debug',__FILE__,__LINE__);
			return $parameters;
		}

		public function replace($string)
		{
			# Initialize Replacement Buffer
			$buffer = '';

			# Parse Token
			$parameter = $this->parse_element($string);

			if (array_key_exists('module',$parameter)) $module = $parameter['module'];
			if (array_key_exists('object',$parameter)) $object = $parameter['object'];
			if (array_key_exists('property',$parameter)) $property = $parameter['property'];

			if ($object == "constant")
			{
				if ($property == "path")
				{
					$buffer .= PATH;
				}
				elseif ($property == "date")
				{
					$buffer .= date('m/d/Y h:i:s');
				}
				elseif ($property == "host")
				{
					$buffer .= $_SERVER['HTTP_HOST'];
				}
			}
			elseif ($object == "page")
			{
				if ($property == "view")
				{
					$buffer = "<r7 object=\"".$this->module."\" property=\"".$this->view."\"/>";
				}
				elseif ($property == "metadata")
				{
					if (isset($this->metadata->$parameter["field"])) $buffer = $this->metadata->$parameter["field"];
				}
				elseif ($property == "navigation")
				{
					require_once( MODULES.'/content/_classes/navigation.php');

					$_navigation = new Menu();
					if ($_navigation->error)
					{
						$this->error = "Error initializing navigation module: ".$_navigation->error;
						return '';
					}

					$menus = $_navigation->find(array("name" => $parameter["name"]));
					if ($_navigation->error)
					{
						app_log("Error displaying menus: ".$_navigation->error,'error',__FILE__,__LINE__);
						$this->error = $_navigation->error;
						return '';
					}

					$menu = $menus[0];

					if (count($menu->item))
					{
						foreach($menu->item as $item)
						{
							$button_class	= "button_".preg_replace("/\W/","_",$menu->name);
							$button_id		= "button[".$item->id."]";
							if (count($item->children))
							{
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
								foreach($item->children as $child)
								{
									$buffer .= "\t\t"
									."<a"
									." onMouseOver=\"expandMenu('$child_container_id')\""
									." onMouseOut=\"collapseMenu('$child_container_id')\""
									.' href="'.$child->target.'"'
									.' class="'.$child_button_class.'">'.$child->title."</a>\n";
								}
								$buffer .= "\t</div>";
							}
							else
							{
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
				elseif ($property == "message")
				{
					$buffer .= "<div class=\"page_message\">".$GLOBALS['page_message']."</div>";
				}
				elseif ($property == "error")
				{
					$buffer .= "<div class=\"page_error\">".$GLOBALS['page_error']."</div>";
				}
			}
			elseif ($object == "content")
			{
				require_once MODULES.'/content/_classes/content.php';
				if ($property == "index")
				{
					if (preg_match("/^\d+$/",$parameter["id"])) $target = $parameter["id"];
					else $target = $GLOBALS['_REQUEST_']->query_vars_array[0];

					$_content = new Content();
					list($content) = $_content->find(array("target" => $target));
					if ($_content->error)
						$buffer = "Error: ".$_content->error;
					elseif (! $content->id)
					{
						app_log("Message not found matching '$target', adding",'info',__FILE__,__LINE__);
						if (role('content operator'))
						{
							$content = $_content->add(array("target" => $target));
						}
						else
						{
							$buffer = "Sorry, the page you requested was not found";
							app_log("Page not found: $target",'error',__FILE__,__LINE__);
						}
					}
					if ($content->cached)
					{
						header("X-Object-Cached: true");
					}
					if ($content->id)
					{
						# Make Sure User Has Privileges
						if (role('content operator'))
						{
							$origin_id = uniqid();
							$buffer .= '<script language="Javascript">function editContent(object,origin,id) { var textEditor=window.open("/_admin/text_editor?object="+object+"&origin="+origin+"&id="+id,"","width=800,height=600,left=20,top=20,status=0,toolbar=0"); } </script>';
							$buffer .= "<div>";
							$buffer .= '<div id="r7_widget['.$origin_id.']" style="border: 1px solid red">'.$content->content.'</div>';
							$buffer .= '<a style="border: 1px solid black" href="javascript:void(0)" onclick="editContent(\'content\',\''.$origin_id.'\',\''.$content->id.'\')">Edit</a>';
							$buffer .= "</div>";
						}
						else
						{
							$buffer .= $content->content;
						}
					}
				}
				else
				{
					app_log("Loading ".MODULES.'/'.$this->module.'/'.$this->style.'/'.$this->view,'debug',__FILE__,__LINE__);
					ob_start();
					include(MODULES.'/'.$this->module.'/'.$this->style.'/'.$this->view.'_mc.php');
					include(MODULES.'/'.$this->module.'/'.$this->style.'/'.$this->view.'.php');
					$buffer .= ob_get_clean();
				}
			}
			elseif ($object == "product")
			{
				# Load Product Class if Not Already Loaded
				require_once MODULES.'/product/_classes/default.php';
				if ($property == "thumbnail")
				{
					$id = $this->query_vars;
					$_product = new Product();
					if (! $id)
					{
						$category = $_product->defaultCategory();
						if ($_product->error)
						{
							print $_product->error;
							exit;
						}
					}
					else
					{
						$category = $_product->details($id);
					}
					$products = $_product->find(array("category" => $category->code));

					# Loop Through Products
					foreach ($products as $product_id)
					{
						#$product = $_product->details($product_id);
						$buffer .= "<r7_product.detail format=thumbnail id=$product_id>";
						#error_log("Buffer: $buffer");
					}
				}
				elseif ($property == "detail")
				{
		 			if (preg_match("/^\d+$/",$parameter["id"])) $id = $parameter["id"];
					elseif ($this->query_vars) $id = $this->query_vars;

					$_product = new Product();
					$product = $_product->details($id);
					if ($parameter["format"] == "thumbnail")
					{
						if ($product->type->group)
						{
							$buffer .= "<div id=\"product[".$parameter["id"]."]\" class=\"product_thumbnail\">\n";
							$buffer .= "\t<a href=\"/_product/thumbnail/".$product->id."\" class=\"product_thumbnail_name\">".$product->name."</a>\n";
							$buffer .= "\t<div class=\"product_thumbnail_description\">".$product->description."</div>\n";
							$buffer .= "\t<div class=\"product_thumbnail_retail\">".$product->retail."</div>\n";
							if ($product->images["0"]->files->thumbnail->path)
								$buffer .= "\t\t<img src=\"".$product->images["0"]->files->thumbnail->path."\" class=\"product_thumbnail_image\"/>\n";
							$buffer .= "</div>\n";
						}
						else
						{
							$buffer .= "<div id=\"product[".$parameter["id"]."]\" class=\"product_thumbnail\">\n";
							$buffer .= "\t<a href=\"/_product/detail/".$product->id."\" class=\"product_thumbnail_name\">".$product->name."</a>\n";
							$buffer .= "\t<div class=\"product_thumbnail_description\">".$product->description."</div>\n";
							$buffer .= "\t<div class=\"product_thumbnail_retail\">".$product->retail."</div>\n";
							if ($product->images["0"]->files->thumbnail->path)
								$buffer .= "\t<div class=\"product_thumbnail_image\"><img src=\"".$product->images["0"]->files->thumbnail->path."\" class=\"product_thumbnail_image\"/></div>\n";
							$buffer .= "</div>\n";
						}
					}
					else
					{
						$buffer .= "<div id=\"product[".$parameter["id"]."]\" class=\"product_thumbnail\">\n";
						$buffer .= "<a href=\"/_product/detail/".$product->id."\" class=\"product_thumbnail_name\">".$product->name."</a>\n";
						$buffer .= "<div class=\"product_detail_description\">".$product->description."</div>\n";
						$buffer .= "<div class=\"product_detail_retail\">".$product->retail."</div>\n";
						if ($product->images["0"]->files->large->path)
							$buffer .= "<img src=\"".$product->images["0"]->files->large->path."\" class=\"product_thumbnail_image\"/>\n";
						$buffer .= "</div>\n";
					}
				}
				elseif ($property == "navigation")
				{
					if (preg_match("/^\d+$/",$parameter["id"])) $id = $parameter["id"];
					elseif ($this->query_vars) $id = $this->query_vars;

					$_product = new Product();
					if (! $id)
					{
						$category = $_product->defaultCategory();
						if ($_product->error)
						{
							print $_product->error;
					 		exit;
						}
					}
					else
					{
						$category = $_product->details($id);
					}
					$products = $_product->find(array("category" => $category->code));
		    
					# Loop Through Products
					foreach ($products as $product_id)
					{
						$product = $_product->details($product_id);
						if ($product->type->group)
						{
							$buffer .= "<div id=\"product_navigation[".$parameter["id"]."]\" class=\"product_navigation\">\n";
							$buffer .= "<a href=\"/_product/thumbnail/".$product->id."\" class=\"product_navigation_name\">".$product->name."</a>\n";
							if ($product->images["0"]->files->icon->path)
								$buffer .= "<img src=\"".$product->images["0"]->files->icon->path."\" class=\"product_navigation_image\"/>\n";
							$buffer .= "</div>\n";
						}
						else
						{
							$buffer .= "<div id=\"product_navigation[".$parameter["id"]."]\" class=\"product_navigation\">\n";
							$buffer .= "<a href=\"/_product/detail/".$product->id."\" class=\"product_navigation_name\">".$product->name."</a>\n";
							if ($product->images["0"]->files->icon->path)
								$buffer .= "<img src=\"".$product->images["0"]->files->icon->path."\" class=\"product_navigation_image\"/>\n";
							$buffer .= "</div>\n";
						}
					}
				}
				else
				{
					app_log("Loading ".MODULES.'/'.$this->module.'/'.$this->style.'/'.$this->view,'debug',__FILE__,__LINE__);
					ob_start();
					include(MODULES.'/'.$this->module.'/'.$this->style.'/'.$this->view.'_mc.php');
					include(MODULES.'/'.$this->module.'/'.$this->style.'/'.$this->view.'.php');
					$buffer .= ob_get_clean();
				}
			}
			elseif ($object == "session")
			{
				if ($property == "customer_id") $buffer = $GLOBALS['_SESSION_']->customer->id;
				elseif ($property == "loggedin")
				{
					if (isset($GLOBALS['_SESSION_']->customer->id)) $buffer = "true";
					else $buffer = "false";
				}
				else
				{
					ob_start();
					include(MODULES.'/'.$this->module.'/'.$this->style.'/'.$this->view.'_mc.php');
					include(MODULES.'/'.$this->module.'/'.$this->style.'/'.$this->view.'.php');
					$buffer .= ob_get_clean();
				}
			}
			elseif ($object == "register")
			{
				if (isset($parameter['id']) and preg_match("/^\d+$/",$parameter["id"])) $id = $parameter["id"];
				elseif (isset($this->query_vars)) $id = $this->query_vars;

				if ($property == "user")
				{
					if ($parameter['field'] == "name")
					{
						$_customer = new RegisterCustomer();
						$customer = $_customer->details($GLOBALS['_SESSION_']->customer->id);
						$buffer .= $customer->first_name." ".$customer->last_name;
					}
				}
				elseif ($property == "welcomestring")
				{
					if ($GLOBALS['_SESSION_']->customer)
					{
						$buffer .= "<span class=\"register_welcomestring\">Welcome ".$GLOBALS['_SESSION_']->customer->first_name." ".$GLOBALS['_SESSION_']->customer->last_name."</span>";
					}
					else
					{
						$buffer .= "<a class=\"register_welcomestring\" href=\"".PATH."/_register/login\">Log In</a>";
					}
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
			elseif ($object == "company")
			{
				$company = new Company();
				list($_company) = $company->find();

				if ($property == "name")
				{
					$buffer .= $_company->name;
				}
			}
			elseif ($object == "news")
			{
				require_once(MODULES.'/news/_classes/news.php');

				if ($property == "events")
				{
					$_event = new NewsEvent();
					if ($_event->error)
					{
						$GLOBALS['_page']->error = "Error fetching events: ".$_event->error;
					}
					else
					{
						$events = $_event->find(array('feed_id' => $parameter['id']));
						if ($_event->error)
						{
							$GLOBALS['_page']->error = "Error fetching events: ".$_event->error;
						}
						else if (count($events))
						{
							foreach ($events as $event)
							{
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
				else
				{
					#error_log("Loading ".MODULES.'/'.$this->module.'/'.$this->style.'/'.$this->view.'_mc.php');
					ob_start();
					include(MODULES.'/'.$this->module.'/'.$this->style.'/'.$this->view.'_mc.php');
					include(MODULES.'/'.$this->module.'/'.$this->style.'/'.$this->view.'.php');
					$buffer .= ob_get_clean();
				}
			}
			elseif ($object == "adminbar")
			{
				if (role('administrator'))
				$buffer = "<div class=\"adminbar\" id=\"adminbar\" style=\"height:20px; width: 100%; position: absolute; top: 0px; left: 0px;\">Admin stuff goes here</div>\n";
			}
			else
			{
				ob_start();
				app_log("Loading view ".$this->view." of module ".$this->module,'debug',__FILE__,__LINE__);
				include(MODULES.'/'.$this->module.'/'.$this->style.'/'.$this->view.'_mc.php');
				include(MODULES.'/'.$this->module.'/'.$this->style.'/'.$this->view.'.php');
				$buffer .= ob_get_clean();
			}
			return $buffer;
		}
	}
	
	class PageMetadata
	{
		###################################################
		### Get Page Metadata							###
		###################################################
		public function all($page_id)
		{
			$get_object_query = "
				SELECT	`key`,value
				FROM	page_metadata
				WHERE	page_id = ?
			";

			$rs = $GLOBALS['_database']->Execute(
				$get_object_query,
				array($page_id)
			);
			if (! $rs)
			{
				$this->error = "SQL Error getting view metadata in PageMetadata::get: ".$GLOBALS['_database']->ErrorMsg();
				return null;
			}
			$metadata = array();
			while(list($key,$value) = $rs->FetchRow())
			{
				$metadata[$key] = $value;
			}
			$metadata = (object) $metadata;
			return $metadata;
		}
		public function get($page_id,$key)
		{
			$get_object_query = "
				SELECT	value
				FROM	page_metadata
				WHERE	page_id = ?
				AND		`key` = ?
			";

			$rs = $GLOBALS['_database']->Execute(
				$get_object_query,
				array($id,$key)
			);
			if (! $rs)
			{
				$this->error = "SQL Error getting view metadata in PageMetadata::get: ".$GLOBALS['_database']->ErrorMsg();
				return 0;
			}
			list($value) = $rs->FetchRow();
			return $value;
		}
		public function set($page_id,$key,$value)
		{
			if (! preg_match('/^\d+$/',$page_id))
			{
				$this->error = "Invalid page id in PageMetadata::set";
				return null;
			}
			if (! isset($key))
			{
				$this->error = "Invalid key name in PageMetadata::set";
				return null;
			}

			$set_data_query = "
				REPLACE
				INTO	page_metadata
				(		page_id,`key`,value)
				VALUES
				(		?,?,?)
			";
			$GLOBALS['_database']->Execute(
				$set_data_query,
				array($page_id,$key,$value)
			);
			if ($GLOBALS['_database']->ErrorMsg())
			{
				$this->error = "SQL Error setting metadata in PageMetadata::add: ".$GLOBALS['_database']->ErrorMsg();
				return null;
			}
			return $this->update($GLOBALS['_database']->Insert_ID(),$parameters);
		}
		###################################################
		### Find Page Metadata							###
		###################################################
		public function find($parameters)
		{
			$find_data_query = "
				SELECT	id
				FROM	page_metadata
				WHERE	id = id
			";

			if ($paramters['page_id'])
				$find_data_query .= "
					AND		page_id = ".$GLOBALS['_database']->qstr($parameters['page_id'],get_magic_quotes_gpc());
			if ($parameters['key'])
				$find_data_query .= "
					AND		`key` = ".$GLOBALS['_database']->qstr($parameters['key'],get_magic_quotes_gpc());
			if ($parameters['value'])
				$find_data_query .= "
					AND		value = ".$GLOBALS['_database']->qstr($parameters['value'],get_magic_quotes_gpc());

			$rs = $GLOBALS['_database']->Execute($find_data_query);
			if (! $rs)
			{
				$this->error = "SQL Error getting page metadata in PageMetadata::find: ".$GLOBALS['_database']->ErrorMsg();
				return 0;
			}
			$object_array = array();
			while (list($id) = $rs->FetchRow())
			{
				$object = $this->details($id);
				array_push($object_array,$object);
			}
			return $object_array;
		}
		###################################################
		### Get Details for Metadata					###
		###################################################
		public function details($id = 0)
		{
			$get_object_query = "
				SELECT	`key`,
						value
				FROM	page_metadata
				WHERE	id = ?
			";
			$rs = $GLOBALS['_database']->Execute(
				$get_object_query,
				array($id)
			);
			if (! $rs)
			{
				$this->error = "SQL Error getting view metadata in PageMetadata::details: ".$GLOBALS['_database']->ErrorMsg();
				return 0;
			}
			$object_array = array();
			while (list($key,$value) = $rs->FetchRow())
			{
				$object_array[$key] = $value;
			}
			$object = (object) $object_array;
			return $object;
		}
	}
	class PageInit
	{
		###################################################
		### Database Schema Setup						###
		###################################################
		public function __construct()
		{
			# See if Schema is Available
			$schema_list = $GLOBALS['_database']->MetaTables();

			if (! in_array("page__info",$schema_list))
			{
				# Create company__info table
				$create_table_query = "
					CREATE TABLE page__info (
						label	varchar(100) not null primary key,
						value	varchar(255)
					)
				";
				$GLOBALS['_database']->Execute($create_table_query);
				if ($GLOBALS['_database']->ErrorMsg())
				{
					$this->error = "SQL Error creating info table in Page::schema_manager: ".$GLOBALS['_database']->ErrorMsg();
					return 0;
				}
			}

			# Check Current Schema Version
			$get_version_query = "
				SELECT	value
				FROM	page__info
				WHERE	label = 'schema_version'
			";

			$rs = $GLOBALS['_database']->Execute($get_version_query);
			if (! $rs)
			{
				$this->error = "SQL Error in Page::schema_manager: ".$GLOBALS['_database']->ErrorMsg();
				return 0;
			}

			list($current_schema_version) = $rs->FetchRow();

			if ($current_schema_version < 1)
			{
				$update_schema_query = "
					INSERT
					INTO	page__info
					VALUES	('schema_version',1)
					ON DUPLICATE KEY UPDATE
							value = 1
				";
				$GLOBALS['_database']->Execute($update_schema_query);
				if ($GLOBALS['_database']->ErrorMsg())
				{
					$this->error = "SQL Error creating _info table in Page::schema_manager: ".$GLOBALS['_database']->ErrorMsg();
					return 0;
				}
				$current_schema_version = 1;
				$update_schema_version = "
					UPDATE	page__info
					SET		value = $current_schema_version
					WHERE	label = 'schema_version'
				";
				$GLOBALS['_database']->Execute($update_schema_version);
				if ($GLOBALS['_database']->ErrorMsg())
				{
					$this->error = "SQL Error in register::Person::schema_manager: ".$GLOBALS['_database']->ErrorMsg();
					return 0;
				}
			}
			if ($current_schema_version < 2)
			{
				$create_table_query = "
					CREATE TABLE IF NOT EXISTS `page_metadata` (
					  `id`		int(5) NOT NULL AUTO_INCREMENT,
					  `module`	varchar(100) NOT NULL,
					  `view`	varchar(100) NOT NULL,
					  `index`	varchar(100) NOT NULL DEFAULT '',
					  `format`	enum('application/json','application/xml') DEFAULT 'application/json',
					  `content` text,
					  PRIMARY KEY `pk_page_views` (`id`),
					  UNIQUE KEY `uk_page_views` (`module`,`view`,`index`)
					)
				";
				$GLOBALS['_database']->Execute($create_table_query);
				if ($GLOBALS['_database']->ErrorMsg())
				{
					$this->error = "SQL Error creating page views table in Page::schema_manager: ".$GLOBALS['_database']->ErrorMsg();
					return 0;
				}
				$create_table_query = "
					CREATE TABLE IF NOT EXISTS `page_widget_types` (
					  `id` int(5) NOT NULL AUTO_INCREMENT,
					  `name` varchar(100) NOT NULL,
					  PRIMARY KEY `pk_widget_type` (`id`),
					  UNIQUE KEY `uk_name` (`name`)
					)
				";
				$GLOBALS['_database']->Execute($create_table_query);
				if ($GLOBALS['_database']->ErrorMsg())
				{
					$this->error = "SQL Error creating page widgets table in Page::schema_manager: ".$GLOBALS['_database']->ErrorMsg();
					return 0;
				}
				$create_table_query = "
					CREATE TABLE IF NOT EXISTS `page_widgets` (
					  `id` int(10) NOT NULL AUTO_INCREMENT,
					  `page_view_id` int(5) NOT NULL,
					  `type_id` int(10) NOT NULL DEFAULT '0',
					  PRIMARY KEY (`id`),
					  FOREIGN KEY `fk_page_view` (`page_view_id`) REFERENCES `page_metadata` (`id`),
					  FOREIGN KEY `fk_widget_type` (`type_id`) REFERENCES `page_widget_types` (`id`)
					)
				";
				$GLOBALS['_database']->Execute($create_table_query);
				if ($GLOBALS['_database']->ErrorMsg())
				{
					$this->error = "SQL Error creating page widgets table in Page::schema_manager: ".$GLOBALS['_database']->ErrorMsg();
					return 0;
				}
				$current_schema_version = 2;
				$update_schema_version = "
					UPDATE	page__info
					SET		value = $current_schema_version
					WHERE	label = 'schema_version'
				";
				$GLOBALS['_database']->Execute($update_schema_version);
				if ($GLOBALS['_database']->ErrorMsg())
				{
					$this->error = "SQL Error in Page::schema_manager: ".$GLOBALS['_database']->ErrorMsg();
					return 0;
				}
			}
		}
	}
?>
