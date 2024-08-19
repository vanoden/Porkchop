<?php
	namespace Site;

	class SearchTagXrefList extends \BaseListClass {
        public function __construct() {
			$this->_modelName = '\Site\SearchTagXref';
		}

		public function find($parameters = array()) {
			$this->clearError();
			$this->resetCount();

			$database = new \Database\Service();

			$get_objects_query = "
				SELECT	stx.id
				FROM	search_tags_xref stx
				INNER JOIN search_tags st ON stx.tag_id = st.id
				WHERE	stx.id = stx.id
			";			

			if (isset($parameters['class'])) {
				$get_objects_query .= "
				AND st.class = ?";
				$database->AddParam($parameters['class']);
			}

			if (isset($parameters['object_id'])) {
				$get_objects_query .= "
				AND stx.object_id = ?";
				$database->AddParam($parameters['object_id']);
			}

			$rs = $database->Execute($get_objects_query);
			if (! $rs) {
				$this->SQLError($GLOBALS['_database']->ErrorMsg());
				return null;
			}
			
			$xrefs = array();
			while (list($id) = $rs->FetchRow()) {
			    $xref = new \Site\SearchTagXref($id);
			    $this->incrementCount();
			    array_push($xrefs,$xref);
			}
			return $xrefs;
		}
	}
