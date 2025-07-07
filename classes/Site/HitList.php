<?php
	namespace Site;

	class HitList Extends \BaseListClass {
		public function __construct() {
			$this->_modelName = '\Site\Hit';
			$this->_tableDefaultSortBy = 'date_hit';
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
				SELECT	`".$workingClass->_tableIdColumn()."`
				FROM	`".$workingClass->_tableName()."`
				WHERE	`".$workingClass->_tableIdColumn()."` = `".$workingClass->_tableIdColumn()."`";

			// Add Parameters
			if (!empty($parameters['session_id']) && is_numeric($parameters['session_id'])) {
				$session = new \Site\Session($parameters['session_id']);
				if ($session->exists()) {
					$find_objects_query .= "
					AND		session_id = ?";
					$database->AddParam($parameters['session_id']);
				}
				else {
					$this->error("Session not found");
					return [];
				}
			}

			// Order Clause
			$find_objects_query .= "
				ORDER BY id desc
			";

			// Limit Clause
			$find_objects_query .= $this->limitClause($controls);

			// Execute Query
			$rs = $database->Execute($find_objects_query);
			if (! $rs) {
				$this->SQLError($database->ErrorMsg());
				return [];
			}

			// Build Results
			$objects = array();
			while (list($id) = $rs->FetchRow()) {
				$object = new $this->_modelName($id);
				array_push($objects,$object);
				$this->_incrementCount();
			}
			return $objects;
		}

		public function first($parameters = [], $controls = []): ?\Site\Hit {
			$controls['sort'] = 'date_hit';
			$controls['order'] = 'asc';
			$objects = parent::first($parameters, $controls);
			return !empty($objects) ? $objects[0] : null;
		}
		public function last($parameters = [], $controls = []): ?\Site\Hit {
			$controls['sort'] = 'date_hit';
			$controls['order'] = 'desc';
			$objects = parent::last($parameters, $controls);
			return !empty($objects) ? $objects[0] : null;
		}
	}
