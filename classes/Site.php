<?php
	class Site Extends BaseClass {
		private $_log_level = 'info';

		public function loadModules($modules = array()) {
			# Process Modules
			foreach ($modules as $module_name => $module_data) {
				if (!isset($module_data['roles'])) $module_data['roles'] = array();
				if (!isset($module_data['templates'])) $module_data['templates'] = array();
				$this->install_log("Loading module <b>$module_name</b>");

				/********************************************/
				/* Update Schema							*/
				/********************************************/
				$class_name = "\\$module_name\\Schema";
				$schema_path = CLASS_PATH."/$module_name/Schema.php";
				if (file_exists($schema_path)) {
					try {
						$class = new $class_name();
						$class_version = $class->version();
						if ($class->error()) $this->install_fail($class->error());
						$class->upgrade();
						if ($class->error()) $this->install_fail("Upgrade from version ".$class->version().": ".$class->error());
						if ($class->version() != $class_version) $this->install_log("Upgraded $module_name from $class_version to ".$class->version(),'notice');
					} catch (Exception $e) {
						$this->install_fail("Cannot upgrade schema '".$class_name."': ".$e->getMessage());
					}
					$this->install_log("$module_name::Schema: version ".$class_version);
					if (isset($module_data['schema']) && $module_data['schema'] != $class_version) {
						$this->install_fail($module_name." Schema version ".$class_version." doesn't match required version ".$module_data['schema']);
					}
				}

				/********************************************/
				/* Add Privileges							*/
				/********************************************/
				if (!empty($module_data['privileges'])) {
					foreach ($module_data['privileges'] as $privilege_name) {
						$this->install_log("Adding privilege ".$privilege_name);
						$privilege = new \Register\Privilege();
						if ($privilege->get($privilege_name)) {
							$privilege->update(array('module' => $module_name));
						}
						else {
							$privilege->add(array('name' => $privilege_name,'module' => $module_name));
						}
					}
				}

				/********************************************/
				/* Add Roles								*/
				/********************************************/
				foreach ($module_data['roles'] as $role_name => $role_data) {
					$role = new \Register\Role();
					if (! $role->get($role_name)) {
						$this->install_log("Adding role '$role_name'");
						if (! isset($role_data['description'])) $role_data['description'] = $role_name;
						$role->add(array('name' => $role_name,'description' => $role_data['description']));
						if ($role->error()) {
							$this->install_fail("Error adding role '$role_name': ".$role->error());
						}
						elseif (isset($role_data['privileges'])) {
							foreach ($role_data['privileges'] as $privilege_name) {
								$role->addPrivilege($privilege_name);
							}
						}
					}
					else {
						$this->install_log("Found role $role_name",'debug');
					}
				}

				/********************************************/
				/* Add Templates							*/
				/********************************************/
				foreach ($module_data['templates'] as $view => $template) {
					$page = new \Site\Page(strtolower($module_name),$view);
					if ($page->error) {
						$this->install_fail("Error loading view '$view' for module '$module_name': ".$page->error);
					}
					if (! $page->id) {
						try {
							$page->add(strtolower($module_name),$view,null);
						} catch (Exception $e) {
							$this->install_fail("Cannot add view: ".$e->getMessage());
						}
						if (! $page->id) {
							$this->install_log("Cannot find view '$view' for module '$module_name': ".$page->error,"warn");
							continue;
						};
					}
					if (!isset($page->metadata) || $page->metadata->template != $template) {
						//$this->install_log($page->metadata->template." vs $template");
						$this->install_log("Add template '$template' to $module_name::$view");
						$page->setMetadata("template",$template);
						if ($page->error) {
							$this->install_fail("Could not add metadata to page: ".$page->error);
						}
					}
					else {
						$this->install_log("Template already set correctly for $module_name::$view",'trace');
					}
				}
			}
		}

		public function populateMenus($menus = array()) {
			foreach ($menus as $code => $menu) {
				$nav_menu = new \Site\Navigation\Menu();
				if ($nav_menu->get($code)) {
					$this->install_log("Menu $code found");
				}
				elseif (! $nav_menu->error() && $nav_menu->add(array("code" => $code,"title" => $menu["title"]))) {
					$this->install_log("Menu $code added");
				}
				else {
					$this->install_fail("Error adding menu $code: ".$nav_menu->error());
				}
				foreach ($menu["items"] as $item) {
					$nav_item = new \Site\Navigation\Item();
					if ($nav_item->getItem($nav_menu->id,$item["title"])) {
						$nav_item->update(
							array(
								"view_order"	=> $item["view_order"],
								"alt"			=> $item["alt"],
								"description"	=> $item["description"],
								"target"		=> $item["target"],
							)
						);
						$this->install_log("Menu Item ".$item["title"]." updated");
					}
					elseif (! $nav_item->error() && $nav_item->add(
							array(
								"menu_id"		=> $nav_menu->id,
								"title"			=> $item["title"],
								"target"		=> $item["target"],
								"view_order"	=> $item["view_order"],
								"alt"			=> $item["alt"],
								"description"	=> $item["description"]
							)
						)) {
							$this->install_log("Adding Menu Item ".$item["title"]);
					}
					else {
						$this->install_fail("Error adding menu item ".$item["title"].": ".$nav_item->error());
					}
					foreach ($item['items'] as $subitem) {
						$subnav_item = new \Site\Navigation\Item();
						if ($subnav_item->getItem($nav_menu->id,$subitem["title"],$nav_item)) {
							$subnav_item->update(
								array(
									"view_order"	=> $subitem["view_order"],
									"target"		=> $subitem["target"],
									"alt"			=> $subitem["alt"],
									"description"	=> $subitem["description"]
								)
							);
							$this->install_log("Sub Menu Item ".$subitem["title"]." updated");
						}
						elseif (! $subnav_item->error() && $subnav_item->add(
								array(
									"menu_id"		=> $nav_menu->id,
									"parent_id"		=> $nav_item->id,
									"title"			=> $subitem["title"],
									"target"		=> $subitem["target"],
									"view_order"	=> $subitem["view_order"],
									"alt"			=> $subitem["alt"],
									"description"	=> $subitem["description"]
								)
							)) {
								$this->install_log("Adding SubMenu Item ".$subitem["title"]);
						}
						else {
							$this->install_fail("Error adding menu item ".$subitem["title"].": ".$subnav_item->error());
						}
					}
				}
			}
		}

		public function install_log($message = '',$level = 'info') {
			if (! $this->log_display($level)) return;
			print date('Y/m/d H:i:s');
			print " [$level]";
			print ": $message<br>\n";
			flush();
		}
	
		public function install_fail($message) {
			$this->install_log("Upgrade failed: $message",'error');
			exit;
		}

		/**
		 * Get/Set the log level - New CamelCase version
		 * @param mixed $level 
		 * @return string 
		 */
		public function logLevel($level = null) {
			if (isset($level)) $this->_log_level = $level;
			return $this->_log_level;
		}

		/**
		 * Get/Set the log level
		 * @param mixed $level 
		 * @return string 
		 */
		public function log_level($level = null) {
			if (isset($level)) $this->_log_level = $level;
			return $this->_log_level;
		}
	
		public function log_display($level = 'info') {
			if (isset($_REQUEST['log_level'])) $log_level = $_REQUEST['log_level'];
			else $log_level = $this->_log_level;
	
			if ($log_level == 'trace') return true;
			if ($log_level == 'debug' && $level != 'trace') return true;
			if ($log_level == 'info' && $level != 'trace' && $level != 'debug') return true;
			if ($log_level == 'warning' && $level != 'trace' && $level != 'debug' && $level != 'info') return true;
			if ($log_level == 'notice' && $level != 'trace' && $level != 'debug' && $level != 'info' && $level != 'warning') return true;
			if ($log_level == 'error') return true;
			return false;
		}

		public function url() {
			if ($GLOBALS['_config']->site->https) return 'https://'.$GLOBALS['_config']->site->hostname;
			else return 'http://'.$GLOBALS['_config']->site->hostname;
		}

		public function page($module = null, $view = null, $index = null) {
			return new \Site\Page($module,$view,$index);
		}

        public function configuration($key) {
            $config = new \Site\Configuration();
			$config->get($key);
			if ($config->error()) $this->error($config->error());
			else return $config->value;
			return null;
        }

		public function module_list() {
			return new \Site\ModuleList();
		}
	}
