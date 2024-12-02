<?php
	namespace Site;

	class SearchTagXrefList extends \BaseListClass {
        public function __construct() {
			$this->_modelName = '\Site\SearchTagXref';
		}

		public function findAdvanced($parameters, $advanced, $controls): array {
			$this->clearError();
			$this->resetCount();

			// Initialize Database Service
			$database = new \Database\Service();

			// Dereference Working Class
			$workingClass = new $this->_modelName;

			// Build Query
			$find_objects_query = "
				SELECT	stx.id
				FROM	search_tags_xref stx
				INNER JOIN search_tags st ON stx.tag_id = st.id
				WHERE	stx.id = stx.id
			";			

			// Add Parameters
			if (isset($parameters['class']) && !empty($parameters['class'])) {
				$find_objects_query .= "
				AND st.class = ?";
				$database->AddParam($parameters['class']);
			}

			if (isset($parameters['object_id'])) {
				$find_objects_query .= "
				AND stx.object_id = ?";
				$database->AddParam($parameters['object_id']);
			}

			// Limit Clause
			$find_objects_query .= $this->limitClause($controls);

			// Execute Query
			$rs = $database->Execute($find_objects_query);
			if (! $rs) {
				$this->SQLError($database->ErrorMsg());
				return [];
			}

			// Assemble Results
			$objects = [];
			while (list($id) = $rs->FetchRow()) {
			    $object = new $this->_modelName($id);
			    $this->incrementCount();
			    array_push($objects,$object);
			}
			return $objects;
		}
	}
