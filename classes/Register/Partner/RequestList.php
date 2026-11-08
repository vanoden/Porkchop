<?php
	namespace Register\Partner;

	class RequestList Extends \BaseListClass {
		public function __construct() {
			$this->_modelName = '\Register\Partner\Request';
		}

		public function findAdvanced($parameters, $advanced, $controls): array {
			$this->clearError();
			$this->resetCount();

			$request = new Request();
			$request->expireStale($parameters['requesting_organization_id'] ?? null);

			$database = new \Database\Service();
			$query = "
				SELECT	id
				FROM	partner_requests
				WHERE	id = id
			";

			if (!empty($parameters['requesting_organization_id'])) {
				if (!is_numeric($parameters['requesting_organization_id'])) {
					$this->error("Invalid requesting organization id");
					return [];
				}
				$query .= " AND requesting_organization_id = ?";
				$database->AddParam($parameters['requesting_organization_id']);
			}
			if (!empty($parameters['target_organization_id'])) {
				if (!is_numeric($parameters['target_organization_id'])) {
					$this->error("Invalid target organization id");
					return [];
				}
				$query .= " AND target_organization_id = ?";
				$database->AddParam($parameters['target_organization_id']);
			}
			if (!empty($parameters['status'])) {
				if (!$request->validStatus($parameters['status'])) {
					$this->error("Invalid status");
					return [];
				}
				$query .= " AND status = ?";
				$database->AddParam($parameters['status']);
			}
			if (!empty($parameters['email'])) {
				$query .= " AND email = ?";
				$database->AddParam($parameters['email']);
			}

			$query .= " ORDER BY date_request DESC";

			$rs = $database->Execute($query);
			if (!$rs) {
				$this->SQLError($database->ErrorMsg());
				return [];
			}

			$objects = array();
			while (list($id) = $rs->FetchRow()) {
				$object = new Request($id);
				if ($object->exists()) {
					array_push($objects, $object);
					$this->incrementCount();
				}
			}
			return $objects;
		}
	}
