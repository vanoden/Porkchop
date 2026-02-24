<?php
	/** @class Site\Navigation\Menu
	 * Add, get, edit and delete navigation menus.
	 * Each menu can have multiple items (Site\Navigation\Item) which can be nested to create submenus.
	 * Used by Site\Page to render navigation based on menu code.
	 */
	namespace Site\Navigation;

class Menu Extends \BaseModel {
	public $code;								# Unique code for this menu (e.g. 'admin', 'mainNav')
	public $title;								# Optional title for this menu (not used for rendering but can be helpful for admin interface)
	private $_page = null;						# Optional reference to the Page object when rendering admin menu for page editing (used for admin menu section override)
	public ?bool $show_close_button = false;	# Whether to show a close button on this menu (used for admin menu)

	/** @method __construct(id)
	 * Initialize navigation menu object. If id is provided, load menu details.
	 * @param id, optional id of navigation menu to load
	*/
	public function __construct($id = 0) {
		$this->_tableName = 'navigation_menus';
		$this->_cacheKeyPrefix = 'navigation.menu';
		parent::__construct($id);
	}

	/** @method public setPage($page)
	 * Set the page object for admin menu section override
	 * 
	 * @param \Site\Page $page The page object
	 * @return void
	 */
	public function setPage($page) {
		$this->_page = $page;
	}

	/** @method public get($code)
	 * Get navigation menu by code (used by Page when rendering menu).
	 * Delegates to getByCode() so loading and validation are consistent.
	 * @param string $code Menu code (e.g. 'admin')
	 * @return bool True if menu was found and loaded
	 */
	public function get($code) {
		if ($code === null || $code === '') {
			return false;
		}
		$result = $this->getByCode((string) $code);
		return ($result === true);
	}

	/** @method public getByCode($code)
	 * Get navigation menu by code
	 * 
	 * @param string $code Code of navigation menu
	 * @return bool True if menu was found and loaded, false if invalid code, null if not found, or menu details if successful load
	 */
	public function getByCode($code): bool {
		$this->clearError();

		// Initialize Database Service
		$database = new \Database\Service();

		// Build the Query
		$get_object_query = "
			SELECT	id
			FROM	navigation_menus
			WHERE	code = ?
		";

		// Add Parameters
		if (! $this->validCode($code)) {
			$this->error("Invalid Code");
			return false;
		}
		$database->AddParam($code);

		// Execute the Query
		$rs = $database->Execute($get_object_query);
		if (! $rs) {
			$this->SQLError($database->ErrorMsg());
			return false;
		}
		list($id) = $rs->FetchRow();
		if ($id > 0) {
			$this->id = $id;
			return $this->details();
		}
		return false;
	}

	/** @method public add(parameters)
	 * Add new navigation menu
	 * @param $parameters, array of parameters for new navigation menu
	 * @return boolean success
	*/
	public function add($parameters = array ()) {

		if (! isset($parameters ['code'])) {
			$this->error("code required");
			return false;
		}
		$add_object_query = "
				INSERT
				INTO	navigation_menus
				(code)
				VALUES
				(?)
			";
			$GLOBALS ['_database']->Execute($add_object_query, array ($parameters ['code']));
			if ($GLOBALS['_database']->ErrorMsg()) {
				$this->SQLError($GLOBALS['_database']->ErrorMsg());
				return false;
			}
			$this->id = $GLOBALS['_database']->Insert_ID();

			// add audit log
			$auditLog = new \Site\AuditLog\Event();
			$auditLog->add(array(
				'instance_id' => $this->id,
				'description' => 'Added new '.$this->_objectName(),
				'class_name' => get_class($this),
				'class_method' => 'add'
			));

			return $this->update($parameters);
	}

	/** @method public update(parameters)
	 * Update navigation menu
	 * @param $parameters, array of parameters to update
	 * @return boolean success
	*/
	public function update($parameters = []): bool {
		$this->clearError();

		// Initialize Database Service
		$database = new \Database\Service();

		// Build the Query
		$update_object_query = "
				UPDATE	navigation_menus
				SET		id = id
			";

		$audit_changes = [];

		// Add Parameters
		if (isset($parameters['code']) && $parameters['code'] != $this->code) {
			$update_object_query .= ",
						code = ?";
			$database->AddParam($parameters ['code']);
			array_push($audit_changes, "code changed to '".$parameters['code']."'");
		}
		if (isset($parameters['title']) && $parameters['title'] != $this->title) {
			$update_object_query .= ",
						title = ?";
			$database->AddParam($parameters ['title']);
			array_push($audit_changes, "title changed to '".$parameters['title']."'");
		}
		if (isset($parameters['show_close_button']) && $parameters['show_close_button'] !== $this->show_close_button) {
			$update_object_query .= ",
						show_close_button = ?";
			$database->AddParam($parameters ['show_close_button'] ? 1 : 0);
			$changeValue = $parameters['show_close_button'] ? 'true' : 'false';
			array_push($audit_changes, "show_close_button changed to '".$changeValue."'");
		}
		$update_object_query .= "
				WHERE	id = ?
		";
		$database->AddParam($this->id);

		if (count($audit_changes) < 1) {
			// Nothing to update
			return true;
		}

		// Clear the Cache
		$this->clearCache();

		// Execute the Query
		$database->Execute($update_object_query);

		// Check for SQL Error
		if ($database->ErrorMsg()) {
			$this->SQLError($database->ErrorMsg());
			return false;
		}

		// audit the update event
		$this->recordAuditEvent($this->id,'Updated: '.implode("; ", $audit_changes),get_class($this),'update');

		return $this->details ();
	}

	/** @method public details()
	 * Get navigation menu details
	 * @return boolean success
	 */
	public function details(): bool {
		$this->clearError();

		// Prepare Database Service
		$database = new \Database\Service();

		// Connect to Cache
		$cache = $this->cache();
		$cachedData = $cache->get();
		if ($cachedData) {
			$this->id = $cachedData->id;
			$this->code = $cachedData->code;
			$this->title = $cachedData->title;
			$this->show_close_button = (isset($cachedData->show_close_button) ? (bool)$cachedData->show_close_button : false);
			$this->cached(true);
			$this->exists(true);
			return true;
		}

		// Build the Query
		$get_default_query = "
				SELECT  *
				FROM    navigation_menus
				WHERE   id = ?
			";
		$rs = $database->Execute($get_default_query, array($this->id ) );
		if (! $rs) {
			$this->SQLError($database->ErrorMsg());
			return false;
		}
		$object = $rs->FetchNextObject ( false );
		if ($object->id) {
			$this->id = $object->id;
			$this->code = $object->code;
			$this->title = $object->title;
			$this->show_close_button = (isset($object->show_close_button) ? (bool)$object->show_close_button : false);
			$cache->set($object);
			$this->exists(true);
		}
		else {
			$this->id = null;
			$this->code = null;
			$this->title = null;
			$this->show_close_button = false;
		}
		return true;
	}

	/** @method public items($parent_id)
	 * Get list of navigation items for this menu
	 * @param $parent_id, parent id of items to get, default 0 for top level items
	 * @return array of navigation items
	 */
	public function items($parent_id = 0) {
		if (! preg_match("/^\d+$/", $parent_id )) $parent_id = 0;

		$itemlist = new \Site\Navigation\ItemList();
		$items = $itemlist->find(array('menu_id' => $this->id, 'parent_id' => $parent_id));
		if ($itemlist->error()) {
			$this->error($itemlist->error());
			return null;
		}
		return $items;
	}

	/** @method public getItem($title)
	 * Get navigation item belonging to this menu by title
	 * @param $title, title of item to get
	 * @param $parent_id parent id of item to get, default 0 for top level items
	 * @return navigation item object
	 */
	public function getItem($title, $parent_id = 0) {
		$item = new \Site\Navigation\Item();
		$item->getItem($this->id, $title, $parent_id);
		if ($item->error()) {
			$this->error($item->error());
			return null;
		}
		return $item;
	}

	/** @method public cascade($parent_id)
	 * Get navigation items in a hierarchical structure
	 * @param $parent_id, parent id of items to get, default 0 for top level items
	 * @return array of navigation items
	 */
	public function cascade($parent_id = 0) {
		$response = array ();
		$items = $this->items ( $parent_id );
		foreach ( $items as $item ) {
			$item->item = $this->cascade ( $item->id );
			array_push ( $response, $item );
		}
		return $response;
	}

	/** @method private extractModuleFromTarget($target)
	 * Extract module name from a navigation target URL (e.g. /_spectros/admin_products -> spectros)
	 * @param string $target URL target
	 * @return string|null Module name or null if not a module URL
	 */
	private function extractModuleFromTarget($target) {
		if (empty($target) || !is_string($target)) return null;
		if (preg_match('#^/_([a-zA-Z0-9_]+)#', $target, $m)) return $m[1];
		return null;
	}

	/** @method private moduleExists($module)
	 * Check if a module exists in the codebase (directory exists under MODULES)
	 * @param string $module Module name
	 * @return bool
	 */
	private function moduleExists($module) {
		if (empty($module) || !defined('MODULES')) return false;
		return is_dir(MODULES . '/' . $module);
	}

	/** @method private shouldShowMenuItem($item)
	 * Whether this menu item should be shown (its target module exists in the codebase)
	 * @param object $item Navigation item with target, item (children)
	 * @return bool
	 */
	private function shouldShowMenuItem($item) {
		$target = isset($item->target) ? trim($item->target) : '';
		if ($target !== '') {
			$module = $this->extractModuleFromTarget($target);
			if ($module !== null) return $this->moduleExists($module);
		}
		if (!empty($item->item) && is_array($item->item)) {
			foreach ($item->item as $child) {
				if ($this->shouldShowMenuItem($child)) return true;
			}
		}
		return false;
	}

	/** @method public asHTML($parameters = array())
	 * Render navigation menu as HTML
	 * This version is DEPRECATED in favor of asHTMLV2 which has improved markup and accessibility, but this version is still used for the admin menu to support the expandNav parameter for manual expansion control.
	 * @param $parameters, array of parameters for rendering
	 * @return string HTML of navigation menu
	*/
	public function asHTML($parameters = array()) {
		$html = '';
		
		// Get current URL for navigation matching
		$currentURL = $this->getCurrentURL();

		if (empty($parameters)) {
			$parameters = array();
		}
		if (!is_array($parameters)) {
			$nav_id = $parameters;
			$parameters = array();
			$parameters['nav_id'] = $nav_id;
		}

		if (isset($parameters['type']) && $parameters['type'] == 'left_nav') {
			if (!isset($parameters['nav_id'])) $parameters['nav_id'] = 'left_nav';
			if (!isset($parameters['a_class'])) $parameters['a_class'] = 'left_nav_button';
			$html .= '<nav id="' . $parameters['nav_id'] . '">';
			$items = $this->cascade ();
			foreach ( $items as $item ) $html .= '<a class="' . $parameters['a_class'] . '">' . $item->title . "</a>";
		}
		else {
			// Defaults
			if (!isset($parameters['nav_id'])) $parameters['nav_id'] = 'left_nav';
			if (!isset($parameters['nav_button_class'])) $parameters['nav_button_class'] = 'left_nav_button';
			if (!isset($parameters['subnav_button_class'])) $parameters['subnav_button_class'] = 'left_subnav_button';

			// Get items that should be expanded based on current URL
			$expandedItems = $this->findItemsToExpand($currentURL);
			// Get items that should be highlighted as current page
			$currentPageItems = $this->findCurrentPageItems($currentURL);

			// Nav Container
			$html .= '<nav id="' . $parameters['nav_id'] . '">' . "\n";
			$items = $this->cascade();
			if (!empty($parameters['code']) && $parameters['code'] === 'admin') {
				$items = array_values(array_filter($items, array($this, 'shouldShowMenuItem')));
				foreach ($items as $item) {
					if (!empty($item->item)) {
						$item->item = array_values(array_filter($item->item, array($this, 'shouldShowMenuItem')));
					}
				}
				$items = array_values(array_filter($items, function ($item) {
					if ($item->hasChildren() && (empty($item->item) || count($item->item) === 0)) return false;
					return true;
				}));
			}
			foreach ( $items as $item ) {
				if ($item->hasChildren()) $has_children = 1;
				else $has_children = 0;

				// Parent Nav Button
				$buttonClass = $parameters['nav_button_class'];
				if (in_array($item->id, $currentPageItems)) {
					$buttonClass .= ' current-page';
				}
				if (in_array($item->id, $expandedItems)) {
					$buttonClass .= ' open-section';
				}
				$html .= "\t" . '<a id="left_nav[' . $item->id . ']" class="' . $buttonClass . '"';

				if ($has_children) {
					$html .= ' href="javascript:void(0)"';
					$html .= ' onclick="toggleMenu(this)"';
				} else {
					$html .= ' href="' . $item->target . '"';
				}
				$html .= '>' . $item->title . "</a>\n";
				if ($has_children) {
					// Sub Nav Container
					$html .= '<div id="left_subnav[' . $item->id . ']" class="left_subnav"';
					// Use new URL-based expansion instead of expandNav parameter
					if (in_array($item->id, $expandedItems)) $html .= ' style="display: block"';
					$html .= '>';
					foreach ( $item->item as $subitem ) {
						// Sub Nav Button - no longer append expandNav parameter
						$subButtonClass = $parameters ['subnav_button_class'];
						if (in_array($subitem->id, $currentPageItems)) {
							$subButtonClass .= ' current-page';
						}
						$html .= '<a href="' . $subitem->target . '" class="' . $subButtonClass . '">' . $subitem->title . '</a>';
					}
					$html .= '</div>';
				}
			}
			$html.= '</nav>' . "\n";
			
			// Add JavaScript for navigation auto-expansion
			$html .= $this->generateNavigationScript($currentURL, $expandedItems);
		}
		return $html;
	}

	/** @method public asHTMLV2($parameters = array())
	 * Render navigation menu as HTML - Version 2
	 * When $_config->site->nav_v2_enhanced is true: enhanced toggle markup, "Firstname (Logout)" when logged in, My Account before Login/Logout.
	 * @param $parameters, array of parameters for rendering
	 * @return string HTML of navigation menu
	*/
	public function asHTMLV2($parameters = array()) {
		$items = $this->items();
		$buffer = '';
		$enhanced = !empty($GLOBALS['_config']->site->nav_v2_enhanced);

		if (count($items)) {
			if ($enhanced) {
				$buffer = <<<END
<ul>
	<li class="nav-toggle">
		<input type="checkbox" id="collapse" aria-label="Toggle menu" aria-haspopup="true" aria-expanded="false" />
		<label for="collapse" aria-label="Open menu"></label>
	</li>

END;
			} else {
					$buffer = <<<END
<ul>
	<input type="checkbox" id="collapse" aria-haspopup="true"/>
	<label for="collapse"></label>

END;
			}
			foreach ($items as $item) {
				if ($item->authentication_required && ! $GLOBALS['_SESSION_']->customer->id) {
					continue;
				}
				if ($item->required_role_id > 0) {
					if (! $GLOBALS['_SESSION_']->customer->has_role_id($item->required_role_id)) {
						continue;
					}
				}
				// Administration link: only show when logged in and user has admin role
				$is_administration = (stripos($item->title, 'Administration') !== false && strpos($item->target, 'admin_home') !== false);
				if (!empty($parameters['code']) && $parameters['code'] === 'mainNav' && $is_administration) {
					if (! $GLOBALS['_SESSION_']->customer->id || ! $GLOBALS['_SESSION_']->customer->has_role('administrator')) {
						continue;
					}
				}
				// Special Macro Support, ie [register::loginusername], [register::logout], [register::account]
				if (preg_match('/\[(\w[\w\-\.\_]+)\:\:(\w[\w\-\.\_]+)/', $item->title, $matches)) {
					if ($matches[1] == 'register') {
						// [register::loginusername] -> show login username when logged in, "Login" when logged out; [register::logout] -> show "Firstname (Logout)" when logged in, "Login" when logged out; [register::account] -> show "My Account" when logged in, "Login" when logged out
						if ($matches[2] == 'loginusername') {
							if ($GLOBALS['_SESSION_']->customer->id) {
								$item->title = $GLOBALS['_SESSION_']->customer->first_name;
								$item->target = '/_register/account';
							}
							else {
								$item->title = 'Login';
								$item->target = '/_register/login';
							}
						}
						// Support both [register::logout] and legacy "Logout" link detection for enhanced logout display
						elseif ($matches[2] == 'logout') {
							if ($enhanced) {
								if ($GLOBALS['_SESSION_']->customer->id) {
									$name = !empty($GLOBALS['_SESSION_']->customer->first_name)
										? $GLOBALS['_SESSION_']->customer->first_name
										: (!empty($GLOBALS['_SESSION_']->customer->login) ? $GLOBALS['_SESSION_']->customer->login : 'Account');
									$item->title = htmlspecialchars($name) . ' (Logout)';
									$item->target = '/_register/logout?redirect='.urlencode($this->getCurrentURL());
								} else {
									$item->title = 'Login';
									$item->target = '/_register/login';
								}
							} else {
								$item->title = 'Logout';
								$item->target = '/_register/logout?redirect='.urlencode($this->getCurrentURL());
							}
						}
						// [register::account] or legacy "Account" link -> show "My Account" when logged in, "Login" when logged out
						else {
							if ($GLOBALS['_SESSION_']->customer->id) {
								$item->title = 'My Account';
								$item->target = '/_register/account';
							}
							else {
								$item->title = "Login";
								$item->target = '/_register/login';
							}
						}
					}
				}
				if ($enhanced) {
					// Literal "Logout" link: show "Firstname (Logout)" when logged in, "Login" when logged out
					if (strpos($item->target, '/_register/logout') !== false || (stripos($item->title, 'logout') !== false && strpos($item->target, '_register') !== false)) {
						if ($GLOBALS['_SESSION_']->customer->id) {
							$name = !empty($GLOBALS['_SESSION_']->customer->first_name)
								? $GLOBALS['_SESSION_']->customer->first_name
								: (!empty($GLOBALS['_SESSION_']->customer->login) ? $GLOBALS['_SESSION_']->customer->login : 'Account');
							$item->title = htmlspecialchars($name) . ' (Logout)';
							$item->target = '/_register/logout?redirect=' . urlencode($this->getCurrentURL());
						} else {
							$item->title = 'Login';
							$item->target = '/_register/login';
						}
					}
					// When mainNav and logged in, add My Account link before the Logout/Login item
					$is_logout_login = (strpos($item->target, '/_register/logout') !== false || (stripos($item->title, 'logout') !== false && strpos($item->target, '_register') !== false) || $item->target === '/_register/login');
					if (!empty($parameters['code']) && $parameters['code'] === 'mainNav' && $GLOBALS['_SESSION_']->customer->id && $is_logout_login) {
						$buffer .= "\t<li><a href=\"/_register/account\">My Account</a></li>\n";
					}
				}
				if (empty($item->target)) $buffer .= "\t<li><button aria-expanded=\"false\" aria-controls=\"m".$item->id."\">".$item->title."</button>\n";
				else $buffer .= "\t<li><a href=\"".$item->target."\">".$item->title."</a>\n";
				$children = $item->children();
				if (count($children)) {
					$buffer .= "\t<ul>\n";
					foreach ( $children as $child ) {
						if ($child->authentication_required && ! $GLOBALS['_SESSION_']->customer->id) {
							continue;
						}
						if ($child->required_role_id > 0) {
							if (! $GLOBALS['_SESSION_']->customer->has_role_id($child->required_role_id)) {
								continue;
							}
						}
						$buffer .= "\t\t<li><a href=\"".$child->target."\">".$child->title."</a></li>\n";
					}
					$buffer .= "\t</ul>\n";
				}
				$buffer .= "\t</li>\n";
			}
			$buffer .= "</ul>\n";
		}
		return $buffer;
	}

	public function validTitle($string) {
		if (! preg_match('/\<\>/',urldecode($string))) return true;
		else return false;
	}

	/** @public method findItemsToExpand($currentURL)
	 * Find navigation items that should be expanded based on current URL
	 * 
	 * @param string $currentURL The current page URL
	 * @return array Array of item IDs that should be expanded
	 */
	public function findItemsToExpand($currentURL) {
		$expandedItems = array();
		$items = $this->cascade();

		// Check for manual override first
		if ($this->_page && $this->_page->getAdminMenuSection()) {
			$sectionName = $this->_page->getAdminMenuSection();
			app_log("Admin menu section override: " . $sectionName, 'debug');
			foreach ($items as $item) {
				if (strtolower($item->title) === strtolower($sectionName)) {
					app_log("Found matching menu item: " . $item->title . " (ID: " . $item->id . ")", 'debug');
					$expandedItems[] = $item->id;
					// Also add parent chain if this item has parents
					$expandedItems = array_merge($expandedItems, $item->getParentChain());
					return array_unique($expandedItems);
				}
			}
		}
		
		// Find all matches first, then select the most specific one
		$allMatches = array();
		foreach ($items as $item) {
			// Check if this item or its children match the current URL
			if ($item->matchesURLRecursive($currentURL)) {
				// Find the specific child that matches
				$matchingChild = $this->findMatchingChild($item, $currentURL);
				if ($matchingChild) {
					$allMatches[] = array(
						'parent_id' => $item->id,
						'child_id' => $matchingChild->id,
						'target' => $matchingChild->target,
						'specificity' => strlen($matchingChild->target)
					);
				}
			}
		}
			
		// If we have matches, find the most specific one
		if (!empty($allMatches)) {
			// Sort by specificity (longest target first)
			usort($allMatches, function($a, $b) {
				return $b['specificity'] - $a['specificity'];
			});
			
			// Take the most specific match
			$bestMatch = $allMatches[0];
			
			// Add the parent item to expanded list
			$expandedItems[] = $bestMatch['parent_id'];
			
			// Add all parent items to expanded list
			$parentItem = new Item($bestMatch['parent_id']);
			$expandedItems = array_merge($expandedItems, $parentItem->getParentChain());
		}
			
		// Remove duplicates and return
		return array_unique($expandedItems);
	}

	/**
	 * Find the specific child item that matches the current URL
	 * 
	 * @param Item $parentItem The parent navigation item
	 * @param string $currentURL The current page URL
	 * @return Item|null The matching child item or null
	 */
	private function findMatchingChild($parentItem, $currentURL) {
		$children = $parentItem->children();
		foreach ($children as $child) {
			if ($child->matchesURL($currentURL)) {
				return $child;
			}
		}
		return null;
	}

	/** @public method findCurrentPageItems($currentURL)
	 * Find navigation items that should be highlighted as current page
	 * 
	 * @param string $currentURL The current page URL
	 * @return array Array of item IDs that should be highlighted
	 */
	public function findCurrentPageItems($currentURL) {
		$currentItems = array();
		$items = $this->cascade();
		
		// Find all matches and their specificity (target length)
		$matches = array();
		foreach ($items as $item) {
			// Check if this item exactly matches the current URL
			if ($item->matchesURL($currentURL)) {
				$matches[] = array('id' => $item->id, 'target' => $item->target, 'type' => 'parent');
			}
			
			// Also check children for exact matches
			foreach ($item->item as $child) {
				if ($child->matchesURL($currentURL)) {
					$matches[] = array('id' => $child->id, 'target' => $child->target, 'type' => 'child', 'parent_id' => $item->id);
				}
			}
		}
		
		if (empty($matches)) {
			return $currentItems;
		}
		
		// Find the most specific match (longest target)
		$mostSpecific = null;
		$maxLength = 0;
		foreach ($matches as $match) {
			$targetLength = strlen($match['target']);
			if ($targetLength > $maxLength) {
				$maxLength = $targetLength;
				$mostSpecific = $match;
			}
		}
		
		if ($mostSpecific) {
			$currentItems[] = $mostSpecific['id'];
			
			// If it's a child match, also add the parent
			if ($mostSpecific['type'] === 'child') {
				$currentItems[] = $mostSpecific['parent_id'];
			}
		}
		
		return array_unique($currentItems);
	}

	/**
	 * Get navigation data for JavaScript processing
	 * 
	 * @param string $currentURL The current page URL
	 * @return array Navigation data structure
	 */
	public function getNavigationData($currentURL) {
		$items = $this->cascade();
		$expandedItems = $this->findItemsToExpand($currentURL);
		
		return array(
			'currentURL' => $currentURL,
			'expandedItems' => $expandedItems,
			'items' => $this->buildNavigationTree($items)
		);
	}

	/**
	 * Build navigation tree structure for JavaScript
	 * 
	 * @param array $items Navigation items
	 * @return array Tree structure
	 */
	private function buildNavigationTree($items) {
		$tree = array();
		
		foreach ($items as $item) {
			$node = array(
				'id' => $item->id,
				'title' => $item->title,
				'target' => $item->target,
				'parent_id' => $item->parent_id,
				'hasChildren' => $item->hasChildren(),
				'children' => array()
			);
			
			if ($item->hasChildren()) {
				$node['children'] = $this->buildNavigationTree($item->item);
			}
			
			$tree[] = $node;
		}
		
		return $tree;
	}

	/**
	 * Get current URL for navigation matching
	 * 
	 * @return string Current URL
	 */
	private function getCurrentURL() {
		// Get current URL from request
		if (isset($GLOBALS['_REQUEST_'])) {
			$request = $GLOBALS['_REQUEST_'];
			if ($request->module == 'content') {
				return '/' . $request->index;
			} elseif ($request->module == 'static') {
				return '/' . $request->view;
			} else {
				return '/_' . $request->module . '/' . $request->view . '/' . $request->index;
			}
		}
		
		// Fallback to REQUEST_URI
		return $_SERVER['REQUEST_URI'] ?? '/';
	}

	/**
	 * Generate JavaScript configuration for navigation auto-expansion
	 * 
	 * @param string $currentURL Current page URL
	 * @param array $expandedItems Items that should be expanded
	 * @return string JavaScript configuration code
	 */
	private function generateNavigationScript($currentURL, $expandedItems) {
		$expandedItemsJson = json_encode($expandedItems);
		$currentURLJson = json_encode($currentURL);
		$currentPageItems = $this->findCurrentPageItems($currentURL);
		$currentPageItemsJson = json_encode($currentPageItems);
		
		return <<<SCRIPT
<script>
// Navigation Configuration - Generated by PHP
window.NAV_CONFIG = {
	currentURL: {$currentURLJson},
	expandedItems: {$expandedItemsJson},
	currentPageItems: {$currentPageItemsJson},
	storageKey: 'navigation_expanded_items'
};
</script>
SCRIPT;
	}
	}
