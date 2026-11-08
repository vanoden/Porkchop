<?php
	namespace Register\Partner;

	class ExplicitShareList Extends \BaseListClass {
		public function __construct() {
			$this->_modelName = '\Register\Partner\ExplicitShare';
		}

		public function findAdvanced($parameters, $advanced, $controls): array {
			$this->clearError();
			$this->resetCount();

			$database = new \Database\Service();
			$query = "
				SELECT	id
				FROM	partner_explicit_shares
				WHERE	id = id
			";

			if (!empty($parameters['partnership_id'])) {
				if (!is_numeric($parameters['partnership_id'])) {
					$this->error("Invalid partnership id");
					return [];
				}
				$query .= " AND partnership_id = ?";
				$database->AddParam($parameters['partnership_id']);
			}
			if (!empty($parameters['resource_class'])) {
				$query .= " AND resource_class = ?";
				$database->AddParam($parameters['resource_class']);
			}
			if (!empty($parameters['resource_id'])) {
				if (!is_numeric($parameters['resource_id'])) {
					$this->error("Invalid resource id");
					return [];
				}
				$query .= " AND resource_id = ?";
				$database->AddParam($parameters['resource_id']);
			}

			$query .= " ORDER BY resource_class ASC, resource_id ASC";

			$rs = $database->Execute($query);
			if (!$rs) {
				$this->SQLError($database->ErrorMsg());
				return [];
			}

			$objects = array();
			while (list($id) = $rs->FetchRow()) {
				$object = new ExplicitShare($id);
				if ($object->exists()) {
					array_push($objects, $object);
					$this->incrementCount();
				}
			}
			return $objects;
		}
	}
