<?php
	namespace Site;

	class TermsOfUseAction Extends \BaseModelList {
		public int $id;

		/********************************************/
		/* Instance Constructor						*/
		/********************************************/
		public function __construct(int $id = null) {
			// Set Table Name
			$this->_tableName = 'site_terms_of_use_actions';

			// Set cache key name - MUST Be Unique to Class
			$this->_cacheKeyPrefix = $this->_tableName;

			// Possible Types
			$this->_addType(array('VIEWED','ACCEPTED','DECLINED'));

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
		public function add(array $params): bool {
			// Clear Any Existing Errors
			$this->clearError();

			// Initialize Services
			$porkchop = new \Porkchop();
			$database = new \Database\Service();

			// Default New Object Code If Not Provided
			if (empty($params['code'])) $params['code'] = $porkchop->uuid();
			if (empty($params['status'])) $params['status'] = 'NEW';

			// Prepare Query
			$add_object_query = "
				INSERT
				INTO	`".$this->_tableName."`
				(		version_id,user_id,type,date_action)
				VALUES
				(		?,?,?,sysdate())
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
		public function details() {
			// Clear Errors
			$this->clearError();

			// Load Services
			$database = new \Database\Service();
			$cache = $this->cache();

			// Fetch Cached Data
	        if ($cache && $cache->exists()) {
				$data = $this->cache->get();

				// Fetch Each Class Attribute
				$this->code = $data->code;

				// Tag Class as Cached
				$this->_cached = true;

				$this->_exists = true;

				// Return Success - No DB Hit Needed!
				return true;
			}

			// Initialize Query
			$get_object_query = "
				SELECT	*
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
				$this->code = $object->code;

				// Cache Database Results
				$cache->set($object);

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
	}