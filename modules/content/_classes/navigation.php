<?php
    ###########################################
    ### navigation.php                      ###
    ### Display and Manage Menus            ###
    ### A. Caravello 6/18/2010              ###
    ###########################################
    
    class Menu
    {
        public $id;
        public $name;
        public $error;

        public function __construct($id = 0)
        {
            $this->schema_manager();
        }

        public function details($id=0)
        {
            if (! $id) $id = $this->id;

            $get_default_query = "
                SELECT  id,name
                FROM    navigation_menus
                WHERE   id = $id
                AND     company_id = ".$GLOBALS['_SESSION_']->company."
            ";
            $rs = $GLOBALS['_database']->Execute($get_default_query);
            if (! $rs)
            {
                $this->error = $GLOBALS['_database']->ErrorMsg();
                return 0;
            }
            $menu = $rs->FetchRow();
            $this->name = $menu["name"];
            $menu_obj->name = $menu["name"];
            #print "&nbsp;Getting Items for ".$menu["id"]."<br>\n";
            $menu_obj->item = $this->items($menu["id"]);
            return $menu_obj;
        }
        
        public function find($parameters)
        {
            $get_menus_query = "
                SELECT  id
                FROM    navigation_menus
                WHERE   id = id
            ";
            
            if ($parameters["name"]) $get_menus_query .= "
                AND     name = ".$GLOBALS['_database']->qstr($parameters["name"],get_magic_quotes_gpc());

            $rs = $GLOBALS['_database']->Execute($get_menus_query);
            if (! $rs)
            {
                $this->error = $GLOBALS['_database']->ErrorMsg();
                return 0;
            }
            $results = array();
            while(list($menu_id) = $rs->FetchRow())
            {
                array_push($results,$this->details($menu_id));
                #print "Getting Details for $menu_id<br>\n";
            }
            return $results;
        }

        public function items($id=0,$parent_id=0)
        {
            if (! $id) $id = $this->id;
            if (! preg_match("/^\d+$/",$id)) $id = 0;
            if (! preg_match("/^\d+$/",$parent_id)) $parent_id = 0;

            $get_items_query = "
                SELECT  id,
                        title,
                        target,
                        alt,
                        privileged,
                        `external`,
                        `ssl`
                FROM    navigation_menu_items
                WHERE	menu_id = $id
                AND     parent_id = $parent_id
                AND     (   admin_role_required = 0
            ";

            if (is_array($GLOBALS['_SESSION_']->customer->roles))
            {
                foreach ($GLOBALS['_SESSION_']->customer->roles as $role)
                {
                    $_customer = new Customer();
                    $role_id = $_customer->role_id($role);
                    $get_items_query .= "
                    OR     admin_role_required = '$role_id'";
                }
            }

            $get_items_query .= ")
                ORDER BY view_order,title
            ";
            #print "Get Items: $get_items_query<br>\n";
            $rs = $GLOBALS['_database']->Execute($get_items_query);
            if (! $rs)
            {
                $this->error = "SQL Error in Menu::items: ".$GLOBALS['_database']->ErrorMsg();
                return null;
            }
            $items = array();
            $item_count = 0;
            while ($result = $rs->FetchRow())
            {
				$items[$item_count] = new stdClass();

				if (array_key_exists("id",$result)) $items[$item_count]->id = $result['id'];
				if (array_key_exists("title",$result)) $items[$item_count]->title = $result['title'];
				if (array_key_exists("target",$result)) $items[$item_count]->target = $result['target'];
				if (array_key_exists("alt",$result)) $items[$item_count]->alt = $result['alt'];
				if (array_key_exists('privileged',$result)) $items[$item_count]->privileged = true;
				else $items[$item_count]->privileged = false;
				if (array_key_exists('external',$result)) $items[$item_count]->external = true;
				else $items[$item_count]->external = false;
				if (array_key_exists('ssl',$result)) $items[$item_count]->ssl = true;

                # See if there are children
                $items[$item_count]->children = $this->items($id,$result['id']);
                $item_count ++;
                
            }
            return $items;
        }
		public function schema_manager()
		{
			# See if Schema is Available
			$schema_list = $GLOBALS['_database']->MetaTables();

			if (! in_array("navigation__info",$schema_list))
			{
				# Create company__info table
				$create_table_query = "
					CREATE TABLE navigation__info (
						label	varchar(100) not null primary key,
						value	varchar(255)
					)
				";
				$GLOBALS['_database']->Execute($create_table_query);
				if ($GLOBALS['_database']->ErrorMsg())
				{
					$this->error = "SQL Error creating info table in navigation::Menu::schema_manager: ".$GLOBALS['_database']->ErrorMsg();
					return 0;
				}
			}

			# Check Current Schema Version
			$get_version_query = "
				SELECT	value
				FROM	navigation__info
				WHERE	label = 'schema_version'
			";

			$rs = $GLOBALS['_database']->Execute($get_version_query);
			if (! $rs)
			{
				$this->error = "SQL Error in navigation::Menu::schema_manager: ".$GLOBALS['_database']->ErrorMsg();
				return 0;
			}

			list($current_schema_version) = $rs->FetchRow();

			if ($current_schema_version < 1)
			{
				$update_schema_query = "
					INSERT
					INTO	navigation__info
					VALUES	('schema_version',1)
					ON DUPLICATE KEY UPDATE
							value = 1
				";
				$GLOBALS['_database']->Execute($update_schema_query);
				if ($GLOBALS['_database']->ErrorMsg())
				{
					$this->error = "SQL Error creating _info table in navigation::Menu::schema_manager: ".$GLOBALS['_database']->ErrorMsg();
					return 0;
				}
				$current_schema_version = 1;
			}
			if ($current_schema_version < 2)
			{
				$create_table_query = "
                    CREATE TABLE IF NOT EXISTS `navigation_menus` (
                      `id` int(5) NOT NULL AUTO_INCREMENT,
                      `company_id` int(5) NOT NULL DEFAULT '0',
                      `name` varchar(100) NOT NULL DEFAULT '',
                      PRIMARY KEY (`id`),
                      UNIQUE KEY `nav_name` (`company_id`,`name`),
                      FOREIGN KEY `fk_company_id` (`company_id`) REFERENCES `company_companies` (`id`)
                    )
				";
				$GLOBALS['_database']->Execute($create_table_query);
				if ($GLOBALS['_database']->ErrorMsg())
				{
					$this->error = "SQL Error creating navigation menus table in navigation::Menu::schema_manager: ".$GLOBALS['_database']->ErrorMsg();
					return 0;
				}
				$create_table_query = "
                    CREATE TABLE IF NOT EXISTS `navigation_menu_items` (
                      `id` int(8) NOT NULL AUTO_INCREMENT,
                      `menu_id` int(11) NOT NULL DEFAULT '0',
                      `title` varchar(100) NOT NULL DEFAULT '',
                      `target` varchar(200) NOT NULL DEFAULT '',
                      `view_order` int(3) DEFAULT NULL,
                      `alt` text,
                      `privileged` int(1) NOT NULL DEFAULT '0',
                      `parent_id` int(5) NOT NULL DEFAULT '0',
                      `external` int(1) NOT NULL DEFAULT '0',
                      `ssl` int(11) NOT NULL DEFAULT '0',
                      `spacer` int(1) NOT NULL DEFAULT '0',
                      `cart_link` int(1) NOT NULL DEFAULT '0',
                      `admin_role_required` int(11) NOT NULL DEFAULT '0',
                      PRIMARY KEY (`id`),
                      KEY `parent_id` (`parent_id`),
                      KEY `view_order` (`view_order`),
                      FOREIGN KEY `fk_menu_id` (`menu_id`) REFERENCES `navigation_menus` (`id`)
                    )
				";
				$GLOBALS['_database']->Execute($create_table_query);
				if ($GLOBALS['_database']->ErrorMsg())
				{
					$this->error = "SQL Error creating navigation menu items table in navigation::Menu::schema_manager: ".$GLOBALS['_database']->ErrorMsg();
					return 0;
				}
				$current_schema_version = 2;
			}

			$update_schema_version = "
				UPDATE	navigation__info
				SET		value = $current_schema_version
				WHERE	label = 'schema_version'
			";
			$GLOBALS['_database']->Execute($update_schema_version);
			if ($GLOBALS['_database']->ErrorMsg())
			{
				$this->error = "SQL Error in navigation::Menu::schema_manager: ".$GLOBALS['_database']->ErrorMsg();
				return 0;
			}
		}
    }
?>
