<?php
	namespace Site;

	class SiteMessageMetaDataList Extends \BaseMetadataListClass {
		public function __construct() {
			$this->_modelName = '\Site\SiteMessageMetaData';
		}

        public function getListByItemId($itemId) {
			$this->clearError();
			$this->resetCount();

			// Initialize Database Service
			$database = new \Database\Service();

			// Dereference Working Class
			$workingClass = new $this->_modelName;

			// Build Query
			$find_objects_query = "
				SELECT	`item_id`, `label`, `value`
				FROM	`site_messages_metadata`
				WHERE	`item_id` = ?
			";

			$database->AddParam($itemId);

			if (!isset($itemId) || empty($itemId) || !is_numeric($itemId)) {
                $this->error("missing itemId");
                return [];
            }

			// Execute Query
			$rs = $GLOBALS['_database']->Execute($find_objects_query);
			if (! $rs) {
				$this->SQLError($database->ErrorMsg());
				return [];
			}

			// Build Results
			$objects = [];
			while ($siteMessage = $rs->FetchRow()) {
				$object = new $this->_modelName();
				$object->set('item_id', $siteMessage['item_id']);
				$object->set('label', $siteMessage['label']);
				$object->set('value', $siteMessage['value']);
				array_push($objects,$object);
				$this->incrementCount();
			}
			return $objects;
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
				SELECT	`item_id`, `label`
				FROM	`site_messages_metadata`
				WHERE	`item_id` = `item_id`
			";			

			// Add Parameters
			if (!empty($parameters['label'])) {
				if ($workingClass->validLabel($parameters['label'])) {
					$find_objects_query .= "
					AND		`label` = ?";
					$database->AddParam($parameters['label']);
				}
				else {
					$this->error("Invalid label");
					return [];
				}
			}

			if (isset($parameters['item_id']) && is_numeric($parameters['item_id'])) {
				$find_objects_query .= "
				AND `item_id` = ?";
				$database->AddParam($parameters['item_id']);
			}

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
			while (list($id, $label) = $rs->FetchRow()) {
			    if (!isset($siteMessages[$label])) $siteMessages[$label] = array();
			    $object = new \Site\SiteMessageMetaData($id, $label);
			    $object->details();			    
			    $this->incrementCount();
			    array_push($objects[$label],$object);
			}
			return $objects;
		}
	}
