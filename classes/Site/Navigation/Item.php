<?php
	namespace Site\Navigation;

	class Item Extends \BaseModel {
		public $menu_id;
		public $title;
		public $target;
		public $view_order;
		public $alt;
		public $description;
		public $parent_id;
		public $required_role_id;
		public $external = false;
		public $ssl = false;
		public $item; // Array of child navigation items

		public function __construct($id = null) {
			$this->_tableName = 'navigation_menu_items';
			$this->_tableUKColumn = null;
			$this->_cacheKeyPrefix = 'navigation.item';
			parent::__construct($id);
		}

		public function __call($name, $arguments) {
			if ($name == "get") return $this->getItem($arguments[0],$arguments[1],$arguments[2]);
			else $this->error("Method '$name' not found");
		}

		/**
		 * get navigation item by target
		 *
		 * @param $target, target of navigation item
		 */
		public function getByTarget( $target ) {
			$this->clearError();

			// Initialize Database Service
			$database = new \Database\Service();

			// Prepare Query
			$get_object_query = "
				SELECT  id
				FROM    navigation_menu_items
				WHERE   target = ?
			";

			// Add Parameters
			if ($this->validTarget($target)) {
				$this->AddParam($target);
			}
			else {
				$this->error("Invalid Target");
				return false;
			}

			// Execute Query
			$rs = $database->Execute($get_object_query);
			if (! $rs) {
				$this->SQLError($database->ErrorMsg());
				return null;
			}
			list($id) = $rs->FetchRow();
			if ($id > 0) {
				$this->id = $id;
				return $this->details();
			}
			return null;
		}

		/** @method public function add($parameters = [])
		 * Add a new navigation item
		 * @param array $parameters 
		 * @return bool 
		 */
		public function add($parameters = []) {
			$this->clearError();

			// Validate Parameters
			if ($parameters['menu_id']) {
				$menu = new Menu($parameters['menu_id']);
				if (! $menu->id) {
						$this->error("Menu not found");
						return false;
				}
			}

			// Initialize Database Service
			$database = new \Database\Service();

			// Prepare Query
			$add_object_query = "
				INSERT
				INTO    navigation_menu_items
				(		menu_id,title)
				VALUES
				(		?,?)
			";

			// Add Parameters
			if (isset($parameters['menu_id'])) {
				$database->AddParam($parameters['menu_id']);
			}
			else {
				$this->error("Menu ID not found");
				return false;
			}
			if (isset($parameters['title'])) {
				if ($this->validTitle($parameters['title'])) {
					$database->AddParam($parameters['title']);
				}
				else {
					$this->error("Invalid Title");
					return false;
				}
			}

			// Execute Query
			$database->Execute($add_object_query);

			// Check for Errors
			if ($database->ErrorMsg()) {
				$this->SQLError($database->ErrorMsg());
				return false;
			}

			$id = $database->Insert_ID();

			// add audit log
			$auditLog = new \Site\AuditLog\Event();
			$auditLog->add(array(
				'instance_id' => $id,
				'description' => 'Added new '.$this->_objectName(),
				'class_name' => get_class($this),
				'class_method' => 'add'
			));

			$this->id = $id;
			return $this->update($parameters);
		}

		/** @method public getByParentIdViewOrderMenuId(parent_id, view_order, menu_id)
		 * Get navigation item by parent id, view order and menu id
		 * @param int $parent_id, parent id of navigation item
		 * @param int $view_order, view order of navigation item
		 * @param int $menu_id, menu id of navigation item
		 * @return object $this
		 */
		public function getByParentIdViewOrderMenuId($parent_id,$view_order,$menu_id) {
			// Clear Errors
			$this-> clearError();

			// Initialize Database Service
			$database = new \Database\Service();

			// Prepare Query
			if (!empty($view_order)) {
				$get_object_query = "
					SELECT  id
					FROM	navigation_menu_items
					WHERE	parent_id = ?
					AND		view_order = ?
					AND		menu_id = ?
				";

				$database->AddParams($parent_id,$view_order,$menu_id);
			}
			else {
				$get_object_query = "
					SELECT	id
					FROM	navigation_menu_items
					WHERE	parent_id = ?
					AND		menu_id = ?
				";
				$database->AddParams($parent_id,$menu_id);
			}

			// Execute Query
			$rs = $database->Execute($get_object_query);

			if (! $rs) {
				$this->SQLError($database->ErrorMsg());
				return null;
			}
			list($id) = $rs->FetchRow();
			if ($id > 0) {
				$this->id = $id;
				return $this->details();
			}
			return null;
		}

		/** @method public getItem(menu_id, code, parent)
		 * Get navigation item by menu id, code and parent
		 * @param int $menu_id, menu id of navigation item
		 * @param string $code, code of navigation item
		 * @param object $parent, parent of navigation item (optional)
		 * @return bool True if found
		 */
		public function getItem($menu_id,$code,$parent = null): bool {
			// Clear Errors
			$this->clearError();

			// Initialize Database Service
			$database = new \Database\Service();

			if (empty($menu_id) or !is_numeric($menu_id)) {
				$this->error("Valid Menu ID Required");
				return false;
			}
			else {
				$menu = new Menu($menu_id);
				if (! $menu->id) {
					$this->error("Menu not found");
					return false;
				}
			}
			if (empty($code) or ! $this->validMenuCode($code)) {
				$this->error("Valid Code Required");
				return false;
			}
			if (!empty($parent)) {
				if (is_numeric($parent)) {
					$parent = new Item($parent);
				}
				elseif (is_object($parent)) {
					// do nothing
				}
				elseif (is_string($parent)) {
					$parent_item = new Item();
					if (! $parent_item->get('menu_id',$menu_id,'title',$parent)) {
						$this->error("Parent not found");
						return false;
					}
					$parent = $parent_item;
				}
				else {
					$this->error("Invalid Parent");
					return false;
				}
			}
			// Prepare Query
			$get_object_query = "
				SELECT	id
				FROM	navigation_menu_items
				WHERE	menu_id = ?
				AND		title = ?
			";

			// Add Parameters
			$database->AddParam($menu->id);
			$database->AddParam($code);

			if (isset($parent) and is_object($parent)) {
				$get_object_query .= "
				AND		parent_id = ?";
				$database->AddParam($parent->id);
			}

			// Execute Query
			$rs = $database->Execute($get_object_query);

			// Check for Errors
			if (! $rs) {
				$this->SQLError($database->ErrorMsg());
				return false;
			}

			// Fetch Results
			list($id) = $rs->FetchRow();
			if ($id) {
				$this->id = $id;
				return $this->details();
			}
			else {
				return false;
			}
		}

		/** @method public update(parameters)
		 * Update navigation item
		 * @param array $parameters, parameters to update
		 * @return bool True if updated
		 */
		public function update($parameters = []): bool {
			// Clear Errors
			$this->clearError();

			// Initialize Database Service
			$database = new \Database\Service();

			// Clear Cache
			$this->clearCache();

			// Prepare Query
			$update_object_query = "
				UPDATE  navigation_menu_items
				SET             id = id
			";

			// Add Parameters
			if (isset($parameters['menu_id'])) {
				$menu = new Menu($parameters['menu_id']);
				if (! $menu->id) {
					$this->error("Menu not found");
					return false;
				}
				$update_object_query .= ",
					menu_id = ?";
				$database->AddParam($parameters['menu_id']);
			}

			if (isset($parameters['title'])) {
				if ($this->validTitle($parameters['title'])) {
					$update_object_query .= ",
					title = ?";
					$database->AddParam($parameters['title']);
				}
				else {
					$this->error("Invalid Title");
					return false;
				}
			}

			if (isset($parameters['target'])) {
				if ($this->validTarget($parameters['target'])) {
					$update_object_query .= ",
						target = ?";
					$database->AddParam($parameters['target']);
				}
				else {
					$this->error("Invalid Target");
					return false;
				}
			}
			if (isset($parameters['parent_id'])) {
				$parent = new Item($parameters['parent_id']);
				if (! $parent->id) {
					$this->error("Parent not found");
					return false;
				}
				elseif ($parent->id == $this->id) {
					$this->error("Parent cannot be the same as this item");
					return false;
				}
				else {
					$update_object_query .= ",
						parent_id = ?
					";
					$database->AddParam($parameters['parent_id']);
				}
			}
			if (!empty($parameters['alt'])) {
				if ($this->safeString($parameters['alt'])) {
					$update_object_query .= ",
						alt = ?";
					$database->AddParam($parameters['alt']);
				}
				else {
					$this->error("Invalid Alt");
					return false;
				}
			}
			if (isset($parameters['description'])) {
				if ($this->safeString($parameters['description'])) {
					$update_object_query .= ",
						description = ?";
					$database->AddParam($parameters['description']);
				}
				else {
					$this->error("Invalid Description");
					return false;
				}
			}
			if (!empty($parameters['view_order'])) {
				if (is_numeric($parameters['view_order'])) {
					$update_object_query .= ",
						view_order = ?";
					$database->AddParam($parameters['view_order']);
				}
				else {
					$this->error("Invalid View Order");
					return false;
				}
			}
			if (isset($parameters['required_role_id'])) {
				if ($parameters['required_role_id'] == "")
					$update_object_query .= ",
					required_role_id = NULL";
				elseif (is_numeric($parameters['required_role_id'])) {
					$role = new \Register\Role($parameters['required_role_id']);
					if (! $role->exists()) {
						$this->error("Required Role not found");
						return false;
					}
					$update_object_query .= ",
						required_role_id = ?";
					$database->AddParam($parameters['required_role_id']);
				}
				else {
					$this->error("Invalid required role id");
					return false;
				}
			}

			// Prepare Query
			$update_object_query .= "
				WHERE   id = ?
			";
			$database->AddParam($this->id);

			// Execute Query
			$database->Execute($update_object_query);

			// Check for Errors
			if ($database->ErrorMsg()) {
				$this->SQLError($database->ErrorMsg());
				return false;
			}
			return $this->details();
		}

		public function required_role() {
				return new \Register\Role($this->required_role_id);
		}

		/** @method public details()
		 * Get navigation item details
		 * @return bool True if found
		 */
		public function details(): bool {
			// Clear Errors
			$this->clearError();

			// Initialize Database Service
			$database = new \Database\Service();

			// Initialize Cache
			$cache = $this->cache();
			$cachedData = $cache->get();
			if (!empty($cachedData)) {
				foreach ($cachedData as $key => $value) {
					if (property_exists($this,$key)) $this->$key = $value;
				}
				$this->cached(true);
				$this->exists(true);
				return true;
			}

			// Prepare Query
			$get_object_query = "
				SELECT  *
				FROM    navigation_menu_items
				WHERE   id = ?
			";
			// Add Parameters
			$database->AddParam($this->id);

			// Execute Query
			$rs = $database->Execute($get_object_query);
			if (! $rs) {
				$this->SQLError($database->ErrorMsg());
				return false;
			}

			// Fetch Results
			$object = $rs->FetchNextObject(false);
			if ($object->id) {
				$this->id = $object->id;
				$this->menu_id= $object->menu_id;
				$this->title = $object->title;
				$this->target = $object->target;
				$this->view_order = $object->view_order;
				$this->alt = $object->alt;
				$this->description = $object->description;
				$this->parent_id = $object->parent_id;
				$this->required_role_id = $object->required_role_id;
				if ($object->external) $this->external = true;
				else $this->external = false;
				if ($object->ssl) $this->ssl = true;
				else $this->ssl = false;

				if (!empty($this->_cacheKeyPrefix)) $cache->set($object);
			}
			else {
				$this->id = -1;
				$this->menu_id = null;
				$this->title = null;
				$this->target = null;
				$this->view_order = null;
				$this->alt = null;
				$this->description = null;
				$this->parent_id = null;
				$this->external = null;
				$this->ssl = null;
			}
			return true;
		}

		public function menu() {
			return new \Site\Navigation\Menu($this->menu_id);
		}

		public function hasChildren() {
			$get_children_query = "
					SELECT  count(*)
					FROM    navigation_menu_items
					WHERE   parent_id = ?
			";
			$rs = $GLOBALS['_database']->Execute($get_children_query,array($this->id));
			if (! $rs) {
					$this->SQLError($GLOBALS['_database']->ErrorMsg());
					return null;
			}
			list($count) = $rs->FetchRow();
			if ($count > 0) {
					return true;
			}
			else {
					return false;
			}
		}

		public function children() {
			$itemList = new \Site\Navigation\ItemList();
			$items = $itemList->find(array('parent_id' => $this->id));
			return $items;
		}

		public function validTitle($string): bool {
			return $this->validName($string);
		}

		public function validTarget($string): bool {
			if (preg_match('/\.\./',$string)) return false;
			if (preg_match('/\<\>/',$string)) return false;
			return true;
		}

		public function validMenuCode($string): bool {
			return $this->validCode($string);
		}

		/**
		 * Check if current URL matches this navigation item's target
		 * Supports exact matches and pattern matching
		 * 
		 * @param string $currentURL The current page URL
		 * @return bool True if URL matches this item
		 */
		public function matchesURL($currentURL) {
			if (empty($this->target) || empty($currentURL)) {
				return false;
			}

			// Normalize URLs for comparison
			$target = $this->normalizeURL($this->target);
			$current = $this->normalizeURL($currentURL);

			// Exact match
			if ($target === $current) {
				return true;
			}

			// Pattern matching for wildcards
			if (strpos($target, '*') !== false) {
				$pattern = str_replace('*', '.*', preg_quote($target, '/'));
				return preg_match('/^' . $pattern . '$/', $current);
			}

		// Check if current URL starts with target (for parent matching)
		// Only allow this for proper path segments, not single characters
		if (strpos($current, $target) === 0) {
			$targetLength = strlen($target);
			
			// Exclude very short targets that are too broad (like "/" or "/_")
			if ($targetLength <= 2) {
				return false;
			}
			
			// Additional safety check: don't match root targets like "/" against any URL
			if ($target === '/' || $target === '_' || $target === '' || $target === 'company' || $target === 'sales' || $target === 'datalogger') {
				return false;
			}
			
			// Additional check: ensure the target is a proper module path (starts with _)
			if (substr($target, 0, 1) !== '_') {
				return false;
			}
			
			// Only match if:
			// 1. Exact match, OR
			// 2. Target ends with '/' and current starts with target, OR  
			// 3. Current has a '/' right after the target (proper path segment)
			// BUT exclude cases where target is just a prefix without proper path separation
			if ($current === $target || 
				(substr($target, -1) === '/' && strpos($current, $target) === 0) ||
				($targetLength < strlen($current) && $current[$targetLength] === '/')) {
				
				// Additional check: don't match if target is just a prefix without proper path structure
				// e.g., don't match "/_engineering" against "/_engineering/home" 
				// unless "/_engineering" is meant to be a parent path
				if ($current !== $target && substr($target, -1) !== '/') {
					// If target doesn't end with '/', make sure it's a proper path segment
					// by checking that the next character after target is '/'
					if ($targetLength >= strlen($current) || $current[$targetLength] !== '/') {
						return false;
					}
					// Additional check: make sure the target is a complete path segment
					// Don't match "/_engineering" against "/_engineering/home" unless we want parent matching
					// For now, disable this type of matching to prevent false positives
					return false;
				}
				
				return true;
			}
		}

			return false;
		}

		/**
		 * Normalize URL for consistent comparison
		 * 
		 * @param string $url URL to normalize
		 * @return string Normalized URL
		 */
		private function normalizeURL($url) {
			// Remove leading slash
			$url = ltrim($url, '/');
			
			// Remove query parameters for comparison
			$url = strtok($url, '?');
			
			// Remove trailing slash
			$url = rtrim($url, '/');
			
			return $url;
		}

		/**
		 * Get all parent navigation items for this item
		 * 
		 * @return array Array of parent item IDs
		 */
		public function getParentChain() {
			$parents = array();
			$currentParentId = $this->parent_id;
			
			while ($currentParentId > 0) {
				$parent = new self($currentParentId);
				if ($parent->id > 0) {
					$parents[] = $parent->id;
					$currentParentId = $parent->parent_id;
				} else {
					break;
				}
			}
			
			return $parents;
		}

		/**
		 * Check if this item or any of its children match the current URL
		 * 
		 * @param string $currentURL The current page URL
		 * @return bool True if this item or its children match
		 */
		public function matchesURLRecursive($currentURL) {
			// Check if this item matches
			if ($this->matchesURL($currentURL)) {
				return true;
			}

			// Check children recursively
			$children = $this->children();
			foreach ($children as $child) {
				if ($child->matchesURLRecursive($currentURL)) {
					return true;
				}
			}

			return false;
		}
	}
