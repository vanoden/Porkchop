<?php
class BaseListClass extends \BaseClass {

	protected $_count = 0;
	protected $_modelName;

	// Default Sort Controls
	protected $_tableDefaultSortBy;
	protected $_tableDefaultSortOrder;

	public function count() {
		return $this->_count;
	}

	public function incrementCount() {
		$this->_count++;
	}

	public function resetCount() {
		$this->_count = 0;
	}

	/**
	 * Polymorphic method for find and search
	 * @param mixed $name 
	 * @param mixed $parameters 
	 * @return mixed 
	 */
	public function __call($name, $parameters) {
		if ($name == "find") {
			if (count($parameters) == 3) {
				return $this->findAdvanced($parameters[0], $parameters[1], $parameters[2]);
			}
			elseif (count($parameters) == 2) {
				return $this->findControlled($parameters[0], $parameters[1], []);
			}
			elseif (count($parameters) == 1) {
				return $this->findSimple($parameters[0]);
			}
			else {
				return $this->findSimple([]);
			}
		}
		elseif ($name == "search") {
			if (count($parameters) == 3) {
				return $this->searchAdanced($parameters[0], $parameters[1], $parameters[2]);
			}
			elseif (count($parameters) == 2) {
				return $this->searchControlled($parameters[0], $parameters[1]);
			}
			else {
				return $this->searchSimple($parameters[0]);
			}
		}
		else {
			$this->error("Invalid method '$name'");
			return false;
		}
	}

	/**
	 * Simple search for messages based on a search string.  This method is a wrapper for searchControlled
	 *
	 * @param array $parameters Search parameters
	 * @return array|int Array of Content\Message objects or 0 on error
	 */
	public function searchSimple($search_string): array {
		if (! $this->validSearchString($search_string)) {
			$this->error("Invalid Search String");
			return array();
		}
		$controls = [];
		$advanced = [];
		return $this->searchAdvanced($search_string, $advanced, $controls);
	}

	/**
	 * Search for messages based on a search string with controls as separate parameters
	 * @param mixed $parameters 
	 * @param mixed $controls 
	 * @return array|int 
	 */
	public function searchControlled($search_string, $controls) {
		if (! $this->validSearchString($search_string)) {
			$this->error("Invalid Search String");
			return array();
		}
		$advanced = [];
		return $this->searchAdvanced($search_string, $advanced, $controls);
	}

	/**
	 * Transfer control parameters from parameters array to controls array
	 * @param array $parameters 
	 * @param array $controls 
	 * @return array
	 */
	public function findSimple($parameters = []) {
		// Initialize controls array with defaults
		$controls = [
			'order'		=> 'ASC',
			'ids'		=> false,
			'offset'	=> 0
		];

		// Control Parameters
		// sort - Sort by column
		// order - ASC or DESC
		// limit - Limit the number of records returned
		// offset - Start at a specific record
		// ids - Return only the ID's
		// recursive - Include recursive objects

		// Transfer control parameters from parameters array to controls array
		if (!empty($parameters['_sort'])) $controls['sort'] = $parameters['_sort'];
		unset($parameters['_sort']);
		if (!empty($parameters['_order'])) $controls['order'] = $parameters['_order'];
		unset($parameters['_order']);
		if (!empty($parameters['_limit'])) $controls['limit'] = $parameters['_limit'];
		unset($parameters['_limit']);
		if (!empty($parameters['_offset'])) $controls['offset'] = $parameters['_offset'];
		unset($parameters['_offset']);
		if (!empty($parameters['recursive'])) $controls['recursive'] = $parameters['recursive'];

		// Other Backwards Compatibility
		if (!empty($parameters['_count'])) $controls['ids'] = $parameters['_count'];
		if (!empty($parameters['_sort_desc'])) $controls['order'] = 'DESC';
		if (!empty($parameters['_sort_order'])) $controls['order'] = $parameters['_sort_order'];
		if (!empty($parameters['_flat']) && $parameters['_flat']) $controls['ids'] = true;
		return $this->findAdvanced($parameters, [], $controls);
	}

	public function findControlled($parameters, array $controls): array {
		if (!empty($controls['count'])) $controls['ids'] = $controls['count'];
		if (!empty($controls['showCachedObjects'])) $controls['showCachedObjects'] = $controls['showCachedObjects'];
		else $controls['showCachedObjects'] = true;
		return $this->findAdvanced($parameters, [], $controls);
	}

	/**
	 * Search for messages based on a search string
	 * @param array $parameters Search parameters
	 * @return array|int Array of Content\Message objects or 0 on error
	 */
	public function searchAdvanced($search_string, array $advanced, array $controls): array {
		return array();
	}

	/**
	 * Find items based on a search string
	 * @param array $parameters 
	 * @param array $advanced 
	 * @param array $controls 
	 * @return array 
	 */
	public function findAdvanced(array $parameters, array $advanced, array $controls): array {
		$this->clearError();
		$this->resetCount();

		// Initialize Database Service
		$database = new \Database\Service();

		// Make Sure we have specified a model
		if (empty($this->_modelName)) {
			$this->error("Model Name Not Set");
			return array();
		}

		$modelName = $this->_modelName;
		$model = new $modelName();

		$tableName = $model->_tableName();
		$tableIDColumn = $model->_tableIDColumn();
		$fields = $model->_fields();

		$find_objects_query = "
				SELECT	`$tableIDColumn`
				FROM	`$tableName`
				WHERE	`$tableIDColumn` = `$tableIDColumn`
			";

		foreach ($parameters as $key => $value) {
			if (in_array($key, $fields)) {
				$find_objects_query .= "
					AND	`$key` = ?";
				$database->AddParam($value);
			}
		}

		if (!empty($controls['sort'])) {
			if (!in_array($controls['sort'], $fields)) {
				$this->error("Invalid sort column name '".$controls['sort']."'");
				return array();
			}
			$find_objects_query .= "
					ORDER BY `" . $controls['sort'] . "`";
			if (!empty($controls['order']) && preg_match('/^(asc|desc)$/i', $controls['order'])) {
				$find_objects_query .= " " . $controls['order'];
			}
		}
		elseif (!empty($this->_tableDefaultSortBy)) {
			$find_objects_query .= "
					ORDER BY `" . $this->_tableDefaultSortBy . "`";
			if (!empty($this->_tableDefaultSortOrder)) {
				$find_objects_query .= " " . $this->_tableDefaultSortOrder;
			}
		}

		if (!empty($controls['limit'])) {
			if (is_numeric($controls['limit'])) {
				if (!empty($controls['offset'])) {
					if (is_numeric($controls['offset'])) {
						$find_objects_query .= "
							LIMIT " . $controls['offset'] . "," . $controls['limit'];
					}
				}
				$find_objects_query .= "
					LIMIT " . $controls['limit'];
			} else {
				$this->error("Invalid limit qty");
				return array();
			}
		}
		$objects = array();
		$rs = $database->Execute($find_objects_query);
		if (!$rs) {
			$this->SQLError($database->ErrorMsg());
			return array();
		}

		while (list($id) = $rs->FetchRow()) {
			$object = new $this->_modelName($id);
			array_push($objects, $object);
			$this->incrementCount();
		}

		return $objects;
	}

	/**
	 * Generate limit clause for SQL
	 * @param array $controls
	 * @return string
	 */
	public function limitClause($controls) {
		$limit = "";
		if (!empty($controls['limit'])) {
			if (is_numeric($controls['limit'])) {
				if (!empty($controls['offset'])) {
					if (is_numeric($controls['offset'])) {
						$limit = " LIMIT " . $controls['offset'] . "," . $controls['limit'];
					}
				}
				else $limit = " LIMIT " . $controls['limit'];
			}
		}
		return $limit;
	}

	// Return Incremented Line Number
	public function nextNumber($parent_id = null) {
		$this->clearError();
		$database = new \Database\Service();
		$modelName = $this->_modelName;
		$model = new $modelName();

		if (empty($model->_tableName() || empty($model->_tableFKColumn() || empty($model->_tableNumberColumn())))) {
			$this->error("Class not configured for Line Numbers");
		}

		$get_number_query = "
				SELECT	max(`$model->_tableNumberColumn`)
				FROM	`$model->_tableName`
			";
		if (isset($parent_id)) {
			$get_number_query .= "
				WHERE	`$model->_tableFKColumn` = ?
				";
			$database->AddParam($parent_id);
		}
		$rs = $database->Execute($get_number_query);
		if (!$rs) {
			$this->SQLError($database->ErrorMsg());
			return null;
		}
		list($last) = $rs->FetchRow();
		if (is_numeric($last))
			return $last + 1;
		else
			return 1;
	}

	/**
	 * Search Only on Tags
	 * 
	 */
	public function searchTags($search_string, $controls = []): array {
		$this->clearError();
		$this->resetCount();

		// Initialize Database Service
		$database = new \Database\Service();

		// Make Sure we have specified a model
		if (empty($this->_modelName)) {
			$this->error("Model Name Not Set");
			return [];
		}

		// Get Class
		$modelClass = new $this->_modelName();

		// Build Query
		$get_objects_query = "
			SELECT	obj.id
			FROM	".$modelClass->_tableName." obj,
					search_tags st,
					search_tags_xref stx
			WHERE	st.class = ?
			AND		st.value = ?
			AND		st.id = stx.tag_id
			)
		";
		$database->AddParams($modelClass->_className, $search_string);

		$rs = $database->Execute($get_objects_query);
		if (! $rs) {
			$this->SQLError($database->ErrorMsg());
			return [];
		}

		$objects = [];
		while (list($id) = $rs->FetchRow()) {
			$object = new $this->_modelName($id);
			array_push($objects, $object);
			$this->incrementCount();
		}
		return $objects;
	}

	/**
	 * Search Only on Categorized Tags
	 * @param array $parameters
	 * @return mixed
	 */
	public function searchCategorizedTags($tags, $controls = []): array {
		$this->clearError();
		$this->resetCount();

		// Initialize Database Service
		$database = new \Database\Service();

		// Make Sure we have specified a model
		if (empty($this->_modelName)) {
			$this->error("Model Name Not Set");
			return [];
		}

		// Get Class
		$modelClass = new $this->_modelName();

		// Get Tag ID's
		$tags = [];
		$searchTagList = new \Search\TagList();
		foreach ($tags as $tag) {
			$tagParams = ['class' => $modelClass->_className];
			if ($tag['category']) $tagParams['category'] = $tag['category'];
			if ($tag['value']) $tagParams['value'] = $tag['value'];

			$tagResults = $searchTagList->find($tagParams);
			if ($tagResults) {
				array_push($tags, $tag->id);
			}
		}

		// Build Query
		$get_objects_query = "
			SELECT	obj.id
			FROM	".$modelClass->_tableName." obj,
					search_tags_xref stx
			WHERE	stx.id = ?
			AND		obj.id = stx.tag_id
			)
		";

		$rs = $database->Execute($get_objects_query);
		if (! $rs) {
			$this->SQLError($database->ErrorMsg());
			return [];
		}

		$objects = [];
		while (list($id) = $rs->FetchRow()) {
			$object = new $this->_modelName($id);
			array_push($objects, $object);
			$this->incrementCount();
		}
		return $objects;
	}

	public function first($parameters = array()) {
		$objects = $this->findAdvanced($parameters, [], array('sort' => $this->_tableDefaultSortBy, 'order' => 'asc', 'limit' => 1));
		if ($this->error())
			return null;
		if (count($objects) < 1)
			return null;
		return $objects[0];
	}

	public function last($parameters = array()) {
		$objects = $this->findAdvanced($parameters, [], array('sort' => $this->_tableDefaultSortBy, 'order' => 'desc', 'limit' => 1));
		if ($this->error())
			return null;
		return end($objects);
	}

	protected function getTagIds($tags): array {
		$modelClass = new $this->_modelName();

		// Get Tag ID's
		$tags = [];
		$searchTagList = new \Search\TagList();
		foreach ($tags as $tag) {
			$tagParams = ['class' => $modelClass->_className];
			if ($tag['category']) $tagParams['category'] = $tag['category'];
			if ($tag['value']) $tagParams['value'] = $tag['value'];

			$tagResults = $searchTagList->find($tagParams);
			if ($tagResults) {
				array_push($tags, $tag->id);
			}
		}
		return $tags;
	}

	public function validSearchString($string) {
		if (is_array($string)) {
			print_r(debug_backtrace());
			exit;
		}
		if (preg_match('/^[\w\-\.\_\s\*]{3,64}$/', $string)) return true;
		else return false;
	}

	public function setWildCards($string) {
		$string = preg_replace('/\*/', '%', $string);
		$string = preg_replace('/\?/', '_', $string);
		return $string;
	}
}