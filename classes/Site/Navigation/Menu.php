<?php

    namespace Site\Navigation;

    class Menu Extends \BaseModel {

		public $code;
		public $title;

	    public function __construct($id = 0) {
			$this->_tableName = 'navigation_menus';
    		parent::__construct($id);
	    }

		/**
		 * get navigation menu by code
		 * 
		 * @param $code, code of navigation menu
		 */
		public function getByCode($code) {
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
			$this->AddParam($code);

			// Execute the Query
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
	    public function update($parameters = []): bool {
		    $update_object_query = "
				    UPDATE	navigation_menus
				    SET		id = id
			    ";
		    $bind_params = array ();

		    if (isset ( $parameters ['code'] )) {
			    $update_object_query .= ",
						    code = ?";
			    array_push ( $bind_params, $parameters ['code'] );
		    }
		    if (isset ( $parameters ['title'] )) {
			    $update_object_query .= ",
						    title = ?";
			    array_push ( $bind_params, $parameters ['title'] );
		    }
		    $update_object_query .= "
				    WHERE	id = ?
			";
		    array_push($bind_params,$this->id );
		    query_log($update_object_query,$bind_params);
		    $GLOBALS['_database']->Execute($update_object_query,$bind_params);

		    if ($GLOBALS['_database']->ErrorMsg()) {
			    $this->SQLError($GLOBALS ['_database']->ErrorMsg());
			    return false;
		    }
			
			// audit the update event
			$auditLog = new \Site\AuditLog\Event();
			$auditLog->add(array(
				'instance_id' => $this->id,
				'description' => 'Updated '.$this->_objectName(),
				'class_name' => get_class($this),
				'class_method' => 'update'
			));

		    return $this->details ();
	    }

	    public function details(): bool {

		    $get_default_query = "
				    SELECT  *
				    FROM    navigation_menus
				    WHERE   id = ?
			    ";
		    $rs = $GLOBALS['_database']->Execute($get_default_query, array($this->id ) );
		    if (! $rs) {
			    $this->SQLError($GLOBALS ['_database']->ErrorMsg());
			    return false;
		    }
		    $object = $rs->FetchNextObject ( false );
		    if ($object->id) {
			    $this->id = $object->id;
			    $this->code = $object->code;
			    $this->title = $object->title;
		    }
			else {
			    $this->id = null;
			    $this->code = null;
			    $this->title = null;
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

	    public function cascade($parent_id = 0) {
		    $response = array ();
		    $items = $this->items ( $parent_id );
		    foreach ( $items as $item ) {
			    $item->item = $this->cascade ( $item->id );
			    array_push ( $response, $item );
		    }
		    return $response;
	    }
		public function asHTML($parameters = array ()) {
		    $html = '';
		    
		    // Get current URL for navigation matching
		    $currentURL = $this->getCurrentURL();
		    
		    if (isset ( $parameters ['type'] ) && $parameters ['type'] == 'left_nav') {
			    if (! isset ( $parameters ['nav_id'] )) $parameters ['nav_id'] = 'left_nav';
			    if (! isset ( $parameters ['a_class'] )) $parameters ['a_class'] = 'left_nav_button';
			    $html .= '<nav id="' . $parameters ['nav_id'] . '">';
			    $items = $this->cascade ();
			    foreach ( $items as $item ) $html .= '<a class="' . $parameters ['a_class'] . '">' . $item->title . "</a>";
		    }
			else {
			    // Defaults
			    if (! isset ( $parameters ['nav_id'] )) $parameters ['nav_id'] = 'left_nav';
			    if (! isset ( $parameters ['nav_button_class'] )) $parameters ['nav_button_class'] = 'left_nav_button';
			    if (! isset ( $parameters ['subnav_button_class'] )) $parameters ['subnav_button_class'] = 'left_subnav_button';

			    // Get items that should be expanded based on current URL
			    $expandedItems = $this->findItemsToExpand($currentURL);
			    // Get items that should be highlighted as current page
			    $currentPageItems = $this->findCurrentPageItems($currentURL);

			    // Nav Container
			    $html .= '<nav id="' . $parameters ['nav_id'] . '">' . "\n";
			    // Close button as first menu item
			    $html .= '<div class="nav-close-container">' . "\n";
			    $html .= '<a href="javascript:void(0)" class="nav-close-btn" onclick="closeNav()">Close Menu</a>' . "\n";
			    $html .= '</div>' . "\n";
			    $items = $this->cascade ();
			    foreach ( $items as $item ) {
				    if ($item->hasChildren ()) $has_children = 1;
				    else $has_children = 0;

				    // Parent Nav Button
				    $buttonClass = $parameters ['nav_button_class'];
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

		public function asHTMLV2($parameters = array()) {
			$items = $this->items();
			$buffer = '';

			if (count($items)) {
				$buffer = <<<END
<ul>
	<input type="checkbox" id="collapse" aria-haspopup="true" />
	<label for="collapse"></label>

END;
				foreach ($items as $item) {
					if (empty($item->target)) $buffer .= "\t<li hi=\"1\">".$item->title."\n";
					else $buffer .= "\t<li><a href=\"".$item->target."\">".$item->title."</a>\n";
					$children = $item->children();
					if (count($children)) {
						$buffer .= "\t<ul>\n";
						foreach ( $children as $child ) {
							$buffer .= "\t\t<li><a href=\"".$child->target."\">".$child->title."</a></li>\n";
						}
						$buffer .= "\t</ul>\n";
					}
					$buffer .= "</li>\n";
				}
				$buffer .= "</ul>\n";
			}
			return $buffer;
		}

		public function validTitle($string) {
			if (! preg_match('/\<\>/',urldecode($string))) return true;
			else return false;
		}

		/**
		 * Find navigation items that should be expanded based on current URL
		 * 
		 * @param string $currentURL The current page URL
		 * @return array Array of item IDs that should be expanded
		 */
		public function findItemsToExpand($currentURL) {
			$expandedItems = array();
			$items = $this->cascade();
			
		
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

		/**
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
