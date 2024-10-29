<?php
    namespace Contact;

    class EventList Extends \BaseListClass {
		public function __construct() {
			$this->_modelName = 'Contact\Event';
		}

		public function findAdvanced($parameters, $advanced, $controls): array {
			$this->clearError();
			$this->resetCount();

			// Initialize Database Service
			$database = new \Database\Service();

			// Build the query
			$find_object_query = "
				SELECT	id
				FROM	contact_events
				WHERE	id = id
			";

			// Add Parameters
			$validationClass = new $this->_modelName();

			if ($validationClass->validStatus($parameters['status'])) {
				$find_object_query = "
				AND		status = ?";
				$database->AddParam($parameters['status']);
			}
			else {
				$this->error("Invalid status");
				return [];
			}
	
			// Order Clause
			$find_object_query .= "
				ORDER BY date_event";

			// Execute the query
			$rs = $database->Execute($find_object_query);
			if (! $rs) {
				$this->SQLError($database->ErrorMsg());
				return [];
			}

			$objects = array();
			while (list($id) = $rs->FetchRow()) {
                $object = new \Contact\Event($id);
				array_push($objects,$object);
				$this->incrementCount();
			}
			return $objects;
		}
    }