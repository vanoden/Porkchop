<?php
	namespace Register\Partner;

	class EntityList Extends \BaseListClass {
		public function __construct() {
			$this->_modelName = '\Register\Partner\Entity';
		}

		public function findAdvanced($parameters, $advanced, $controls): array {
			$this->clearError();
			$this->resetCount();

			$database = new \Database\Service();
			$query = "
				SELECT	id
				FROM	partner_entities
				WHERE	id = id
			";

			if (!empty($parameters['organization_id'])) {
				if (!is_numeric($parameters['organization_id'])) {
					$this->error("Invalid organization id");
					return [];
				}
				$query .= " AND organization_id = ?";
				$database->AddParam($parameters['organization_id']);
			}
			if (!empty($parameters['partner_id'])) {
				if (!is_numeric($parameters['partner_id'])) {
					$this->error("Invalid partner id");
					return [];
				}
				$query .= " AND partner_id = ?";
				$database->AddParam($parameters['partner_id']);
			}

			$query .= " ORDER BY date_created DESC";

			$rs = $database->Execute($query);
			if (!$rs) {
				$this->SQLError($database->ErrorMsg());
				return [];
			}

			$objects = array();
			while (list($id) = $rs->FetchRow()) {
				$object = new Entity($id);
				if ($object->exists()) {
					array_push($objects, $object);
					$this->incrementCount();
				}
			}
			return $objects;
		}
	}
