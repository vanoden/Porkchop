<?php
	namespace Site;

	class SiteMessageDeliveryList Extends \BaseListClass {
		public function find($parameters = array()) {
			$this->clearError();
			$this->resetCount();

			$database = new \Database\Service();

			$get_objects_query = "
				SELECT	smd.id
				FROM	site_message_deliveries smd,
						site_messages sm
				WHERE	smd.message_id = sm.id
			";			

			if (isset($parameters['user_id'])) {
				$get_objects_query .= "
				AND smd.user_id = ?";
				$database->AddParam($parameters['user_id']);
			}
			if (isset($parameters['user_created'])) {
				$get_objects_query .= "
				AND sm.user_created = ?";
				$database->AddParam($parameters['user_created']);
			}
			if (isset($parameters['viewed'])) {
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
			if (isset($parameters['acknowledged'])) {
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

			$rs = $database->Execute($get_objects_query);
			if (! $rs) {
				$this->SQLError($GLOBALS['_database']->ErrorMsg());
				return null;
			}
			
			$deliveries = array();
			while (list($id) = $rs->FetchRow()) {
			    $delivery = new \Site\SiteMessageDelivery($id);
			    $this->incrementCount();
			    array_push($deliveries,$delivery);
			}
			return $deliveries;
		}
	}
