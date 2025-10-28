<?php
	namespace Register;

	class LocationList Extends \BaseListClass {
		public function __construct() {
			$this->_modelName = '\Register\Location';
		}

		public function findAdvanced($parameters, $advanced, $controls): array {
			$this->clearError();
			$this->resetCount();

			// Initialize Database Service
			$database = new \Database\Service();

			// Build Query
			$find_objects_query = "
				SELECT  id
				FROM    register_locations
				WHERE   id = id
			";

			// Execute Query
            $rs = $database->Execute($find_objects_query);
            if (! $rs) {
                $this->SQLError($database->ErrorMsg());
                return [];
            }

            $objects = array();
            while (list($id) = $rs->FetchRow()) {
                $objectOptions = array();
                if (isset($parameters['recursive'])) {
                    $objectOptions['recursive'] = $parameters['recursive'];
                }
                $object = new $this->_modelName($id, $objectOptions);
                array_push($objects,$object);
                $this->incrementCount();
            }
            return $objects;
		}
	}
