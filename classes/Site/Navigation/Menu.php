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

			    // Nav Container
			    $html .= '<nav id="' . $parameters ['nav_id'] . '">' . "\n";
          $html .= '<a href="javascript:void(0)" class="closebtn" onclick="closeNav()">&times;</a>' . "\n";
			    $items = $this->cascade ();
			    foreach ( $items as $item ) {
				    if ($item->hasChildren ()) $has_children = 1;
				    else $has_children = 0;

				    // Parent Nav Button
				    $html .= "\t" . '<a id="left_nav[' . $item->id . ']" class="' . $parameters ['nav_button_class'] . '"';

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
					    if (isset ( $_REQUEST ['expandNav'] ) && $_REQUEST ['expandNav'] == $item->id) $html .= ' style="display: block"';
					    $html .= '>';
					    foreach ( $item->item as $subitem ) {
						    // Sub Nav Button
						    $target = $subitem->target;
						    if (preg_match ( '/\?/', $target )) $target .= "&expandNav=" . $item->id;
						    else $target .= "?expandNav=" . $item->id;
						    $html .= '<a href="' . $target . '" class="' . $parameters ['subnav_button_class'] . '">' . $subitem->title . '</a>';
					    }
					    $html .= '</div>';
				    }
			    }
				$html.= '</nav>' . "\n";
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
    }
