<?php
	namespace Navigation;

    class Menu {
        public $id;
        public $name;
        public $error;

        public function __construct($id = 0) {
			$schema = new Schema();

			if ($id) {
				$this->id = $id;
				$this->details;
			}
        }

        public function details() {
            $get_default_query = "
                SELECT  id,name
                FROM    navigation_menus
                WHERE   id = ?
                AND     company_id = ?
            ";
            $rs = $GLOBALS['_database']->Execute(
				$get_default_query,
				$this->id,
				$GLOBALS['_SESSION_']->company
			);
            if (! $rs) {
                $this->error = $GLOBALS['_database']->ErrorMsg();
                return null;
            }
            $menu = $rs->FetchRow();
			$this->id = $menu["id"];
            $this->name = $menu["name"];
            $menu_obj->name = $menu["name"];
            $menu_obj->item = $this->items($menu["id"]);
        }

        public function items($id=0,$parent_id=0) {
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

            if (is_array($GLOBALS['_SESSION_']->customer->roles)) {
                foreach ($GLOBALS['_SESSION_']->customer->roles as $role) {
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
            if (! $rs) {
                $this->error = "SQL Error in Menu::items: ".$GLOBALS['_database']->ErrorMsg();
                return null;
            }
            $items = array();
            $item_count = 0;
            while ($result = $rs->FetchRow()) {
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
    }
?>
