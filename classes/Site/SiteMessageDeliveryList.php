<?php
	namespace Site;

	class SiteMessageDeliveryList Extends \BaseListClass {
		public function __construct() {
			$this->_modelName = '\Site\SiteMessageDelivery';
		}

		public function findAdvanced(array $parameters, array $advanced, array $controls): array {
			$this->clearError();
			$this->resetCount();

			// Initialize Database Service
			$database = new \Database\Service();

			// Dereference Working Class
			$workingClass = new $this->_modelName;

			// Build Query
			$get_objects_query = "
				SELECT	smd.id
				FROM	site_message_deliveries smd,
						site_messages sm
				WHERE	smd.message_id = sm.id
			";			

			// Add Parameters
			if (!empty($parameters['user_id']) && is_numeric($parameters['user_id'])) {
				$user = new \Register\Person($parameters['user_id']);
				if ($user->exists()) {
					$get_objects_query .= "
					AND		smd.user_id = ?";
					$database->AddParam($parameters['user_id']);
				}
				else {
					$this->error("User not found");
					return [];
				}
			}

			if (!empty($parameters['user_created']) && is_numeric($parameters['user_created'])) {
				$user = new \Register\Person($parameters['user_created']);
				if ($user->exists()) {
					$get_objects_query .= "
					AND		sm.user_created = ?";
					$database->AddParam($parameters['user_created']);
				}
				else {
					$this->error("User not found");
					return [];
				}
			}

			if (isset($parameters['viewed']) && is_bool($parameters['viewed'])) {
				if ($parameters['viewed'] == false) {
					$get_objects_query .= "
					AND	date_viewed IS NULL
					";
				}
				else {
					$get_objects_query .= "
					AND	date_viewed IS NOT NULL
					";
				}
			}

			if (isset($parameters['acknowledged']) && is_bool($parameters['acknowledged'])) {
				if ($parameters['acknowledged'] == false) {
					$get_objects_query .= "
					AND	date_acknowledged IS NULL
					";
				}
				else {
					$get_objects_query .= "
					AND	date_acknowledged IS NOT NULL
					";
				}
			}

			// Limit Clause
			$get_objects_query .= $this->limitClause($controls);

			// Execute Query
			$rs = $database->Execute($get_objects_query);
			if (! $rs) {
				$this->SQLError($database->ErrorMsg());
				return [];
			}

			// Build Results
			$objects = array();
			while (list($id) = $rs->FetchRow()) {
			    $object = new $this->_modelName($id);
			    $this->incrementCount();
			    array_push($objects,$object);
			}
			return $objects;
		}
	}
