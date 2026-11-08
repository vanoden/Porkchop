<?php
	namespace Register\Partner;

	class ImplicitShareList Extends \BaseListClass {
		public function __construct() {
			$this->_modelName = '\Register\Partner\ImplicitShare';
		}

		public function findAdvanced($parameters, $advanced, $controls): array {
			$this->clearError();
			$this->resetCount();

			$database = new \Database\Service();
			$query = "
				SELECT	id
				FROM	partner_implicit_shares
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

			$query .= " ORDER BY resource_class ASC, id ASC";

			$rs = $database->Execute($query);
			if (!$rs) {
				$this->SQLError($database->ErrorMsg());
				return [];
			}

			$objects = array();
			while (list($id) = $rs->FetchRow()) {
				$object = new ImplicitShare($id);
				if ($object->exists()) {
					array_push($objects, $object);
					$this->incrementCount();
				}
			}
			return $objects;
		}
	}
