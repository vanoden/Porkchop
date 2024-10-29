<?php
	namespace Register;

	class TagList Extends \BaseListClass {
		public function __construct() {
			$this->_modelName = '\Register\Tag';
		}

		public function findAdvanced($parameters, $advanced, $controls): array {
			$this->clearError();
			$this->resetCount();

			// Initialize Database Service
			$database = new \Database\Service();

			// Build Query
			$get_tags_query = "
				SELECT	id, name
				FROM	register_tags
				WHERE	id = id
			";

			// Add Parameters
			$validationClass = new $this->_modelName;
			if (!empty($parameters['type'])) {
				if ($validationClass->validType($parameters['type'])) {
					$get_tags_query .= "
						AND		type = ?
					";
					$database->AddParam($parameters['type']);
				}
				else {
					$this->setError('Invalid type');
					return [];
				}	
			}
			if (!empty($parameters['register_id']) && is_numeric($parameters['register_id'])) {
				$get_tags_query .= "
				AND     register_id = ?";
				$database->AddParam($parameters['register_id']);
			}
			if (!empty($parameters['name'])) {
				if ($validationClass->validName($parameters['name'])) {
					$get_tags_query .= "
						AND		name = ?
					";
					$database->AddParam($parameters['name']);
				}
				else {
					$this->setError('Invalid name');
					return [];
				}
			}			
			if (!empty($parameters['id']) && is_numeric($parameters['id'])) {
				$get_tags_query .= "
				AND     id = ?";
				$database->AddParam($parameters['id']);
			}

			// Limit Clause
			$get_tags_query .= $this->limitClause($controls);

			// Execute Query
			$rs = $database->Execute($get_tags_query);
			if (! $rs) {
				$this->SQLError($database->ErrorMsg());
				return [];
			}
			
			$objects = array();
			while (list($id) = $rs->FetchRow()) {
			    $object = new $this->_modelName($id);
			    $this->incrementCount();
			    array_push($objects,$object);
			}
			
			return $objects;
		}
		
		public function getDistinct() {
            app_log("Register::TagList::getDistinct()",'trace',__FILE__,__LINE__);
			$this->resetCount();
			$this->clearError();

			$bind_params = array();
			$get_tags_query = "
				SELECT	distinct(name)
				FROM	register_tags
				WHERE	id = id
			";

			query_log($get_tags_query,$bind_params);
			$rs = $GLOBALS['_database']->Execute($get_tags_query,$bind_params);
			if (! $rs) {
				$this->SQLError($GLOBALS['_database']->ErrorMsg());
				return null;
			}

			$regsterTags = array();
			while (list($name) = $rs->FetchRow()) {
				$this->incrementCount();
				$regsterTags[] = $name;
			}
			return $regsterTags;
		}
	}
