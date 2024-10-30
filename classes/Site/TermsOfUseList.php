<?php
	namespace Site;

	class TermsOfUseList Extends \BaseListClass {
		public function __construct() {
			$this->_modelName = "\Site\TermsOfUse";
		}

		public function findAdvanced($params, $advanced, $controls): array {
			$this->clearError();
			$this->resetCount();

			// Initialize Database Service
			$database = new \Database\Service();

			// Dereference Working Class
			$workingClass = new $this->_modelName;

			// Build Query
			$find_objects_query = "
				SELECT	id
				FROM	site_terms_of_use
				WHERE	id = id";

			// Add Parameters
			if (!empty($params['name'])) {
				if ($workingClass->validName($params['name'])) {
					$find_objects_query .= "
						AND		name = ?";
					$database->AddParam($params['name']);
				}
				else {
					$this->error("Invalid name");
					return [];
				}
			}

			// Execute Query
			$rs = $database->Execute($find_objects_query);
			if (! $rs) {
				$this->SQLError($database->ErrorMsg());
				return [];
			}

			// Assemble Results
			$objects = array();
			while (list($id) = $rs->FetchRow()) {
				$object = new $this->_modelName($id);
				array_push($objects,$object);
				$this->incrementCount();
			}
			return $objects;
		}
	}
