<?php
	namespace Site;

	class SiteMessagesList Extends \BaseListClass {
		public function __construct() {
			$this->_modelName = '\Site\SiteMessage';
		}

		public function findAdvanced(array $parameters, array $advanced, array $controls): array {
			$this->clearError();
			$this->resetCount();

			// Initialize Database Service
			$database = new \Database\Service();

			// Dereference Working Class
			$workingClass = new $this->_modelName;

			// Build Query
			$find_objects_query = "
				SELECT	sm.id
				FROM	site_messages sm
				LEFT JOIN site_message_deliveries smd
				ON smd.message_id = sm.id
				WHERE	sm.id = sm.id 
			";

			// Add Parameters
			if (!empty($parameters['user_created']) && is_numeric($parameters['user_created'])) {
				$user = new \Register\Person($parameters['user_created']);
				if ($user->exists()) {
					$find_objects_query .= "
					AND		sm.user_created = ?";
					$database->AddParam($parameters['user_created']);
				}
				else {
					$this->error("User not found");
					return [];
				}
			}

			if (!empty($parameters['recipient_id']) && is_numeric($parameters['recipient_id'])) {
				$user = new \Register\Person($parameters['recipient_id']);
				if ($user->exists()) {
					$find_objects_query .= "
					AND		smd.recipient_id = ?";
					$database->AddParam($parameters['recipient_id']);
				}
				else {
					$this->error("User not found");
					return [];
				}
			}

			if (isset($parameters['acknowledged']) && $parameters['acknowledged'] == 'read') {
				$find_objects_query .= "
				AND smd.date_acknowledged is NOT NULL";
			}
			else if (isset($parameters['acknowledged']) && $parameters['acknowledged'] == 'unread') {
				$find_objects_query .= "
				AND smd.date_acknowledged is NULL";
			}

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
			    $this->incrementCount();
			}
			return $objects;
		}
		
		public function getUnreadForUserId ($userId) {
			$this->clearError();
			$this->resetCount();
	
			// Initialize Database Service
			$database = new \Database\Service();

			// Build Query
			$find_objects_query = "
				SELECT	count(sm.id) as total_messages
				FROM	site_messages sm
				WHERE	sm.id = sm.id
			";			

			if (isset($parameters['user_created'])) {
				$find_objects_query .= "
				AND user_created = ?";
				array_push($bind_params,$userId);
			}

			$rs = $database->Execute($find_objects_query);
			if (! $rs) {
				$this->SQLError($database->ErrorMsg());
				return [];
			}
			
			$totalMessages = 0;
			while (list($row) = $rs->FetchRow()) {
			    $totalMessages = $row;
			};			

			$find_objects_query = "
				SELECT	sm.id
				FROM	site_messages sm
				LEFT JOIN site_message_deliveries smd
				ON smd.message_id = sm.id
				WHERE	sm.id = sm.id
				AND smd.date_acknowledged == NULL
			";			

			if (isset($parameters['user_created']) && is_numeric($parameters['user_created'])) {
				$user = new \Register\Person($parameters['user_created']);
				if ($user->exists()) {
					$find_objects_query .= "
					AND user_created = ?";
					$database->AddParam($userId);
				}
				else {
					$this->error("User not found");
					return [];
				}
			}

			$rs = $database->Execute($find_objects_query);
			if (! $rs) {
				$this->SQLError($database->ErrorMsg());
				return [];
			}

			$acknowledgedMessages = 0;
			while (list($id) = $rs->FetchRow()) $acknowledgedMessages ++;	
			return $totalMessages - $acknowledgedMessages;		
		}
	}
