<?php
	namespace Site;

	class TermsOfUseEvent Extends \BaseModel {

		/********************************************/
		/* Instance Constructor						*/
		/********************************************/
		public function __construct(int $id = null) {
			// Set Table Name
			$this->_tableName = 'site_terms_of_use_events';

			// Set cache key name - MUST Be Unique to Class
			// Comment out to disable cache
			//$this->_cacheKeyPrefix = $this->_tableName;

			// Add Types for Validation
			$this->_addType(array('CREATION','ACTIVATION','RETRACTION'));

			// Load Record for Specified ID if given
			if (isset($id) && is_numeric($id)) {
				$this->id = $id;
				$this->details();
			}
		}

		/********************************************/
		/* Add New Object Record Given Parameters	*/
		/* Must include non-nullable fields.		*/
		/* Others should be handled in update().	*/
		/********************************************/
		public function add($params = []): bool {
			// Clear Any Existing Errors
			$this->clearError();

			// Initialize Services
			$porkchop = new \Porkchop();
			$database = new \Database\Service();

			if (!isset($params['type'])) $params['type'] = 'CREATION';

			if (!$this->validStatus($params['type'])) {
				$this->error("Invalid type");
				return false;
			}

			// Prepare Query
			$add_object_query = "
				INSERT
				INTO	`".$this->_tableName."`
				(		version_id, user_id, type, date_event)
				VALUES	(?,?,?,sysdate())
			";

			// Add Parameters
			$database->AddParam($param['version_id']);
			$database->AddParam($GLOBALS['_SESSION_']->customer->id);
			$database->AddParam($param['type']);

			// Execute Query
			$rs = $database->Execute($add_object_query);
			if (! $rs) {
				$this->SQLError($rs->ErrorMsg());
				return false;
			}

			// Fetch New ID
			$this->id = $database->Insert_ID();

			// No Update, Load Details
			return $this->details();
		}

		/********************************************/
		/* Load Object Details						*/
		/* from Cache or Database					*/
		/********************************************/
		public function details(): bool {
			// Clear Errors
			$this->clearError();

			// Load Services
			$database = new \Database\Service();

			// Initialize Query
			$get_object_query = "
				SELECT	*,unix_timestamp(date_event) timestamp_event
				FROM	`".$this->_tableName."`
				WHERE	id = ?";

			// Bind Params
			$database->AddParam($this->id);

			// Execute Query
			$rs = $database->Execute($get_object_query);
			if (! $rs) {
				$this->SQLError($database->ErrorMsg());
				return false;
			}

			// Fetch Results From Database
			$object = $rs->FetchNextObject();
			if ($object->id) {
				$this->id = $object->id;
				$this->date_event = $object->date_event;
				$this->timestamp_event = $object->timestamp_event;
				$this->type = $object->type;
				$this->version_id = $object->version_id;
				$this->user_id = $object->user_id;

				$this->_exists = true;
			}
			else {
				// Null out any values
				$this->id = null;
				$this->code = null;

				$this->_exists = false;
			}

			// Return True as long as No Errors - Not Found is NOT an error
			return true;
		}

		public function customer() {
			$customer = new \Register\Customer($this->user_id);
			return $customer;
		}

		public function version() {
			$version = new \TermsOfUseVersion($this->version_id);
		}

		public function date_created() {
			$eventList = new \TermsOfUseEvent();
			list($event) = $eventList->find(array('tou_id' => $this->id, 'type' => 'CREATION'));
			return $event;
		}
	}