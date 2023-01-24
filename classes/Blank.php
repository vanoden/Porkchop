<?php
	class Blank Extends \BaseClass {
		public int $id;

		/********************************************/
		/* Instance Constructor						*/
		/********************************************/
		public function __constructor(int $id = null) {
			// Set Table Name
			$this->_table_name = 'site_terms_of_use';

			// Set cache key name - MUST Be Unique to Class
			$this->_cache_key_prefix = $this->_table_name;

			// Load Record for Specified ID if given
			if (isset($id) && is_numeric($id)) {
				$this->id = $id;
				$this->details();
			}
		}

		/********************************************/
		/* Get Object Record Using Unique Code		*/
		/********************************************/
		public function get(string $code): bool {
			// Clear Errors
			$this->clearError();

			// Initialize Services
			$database = new \Database\Service();

			// Prepare Query
			$get_object_query = "
				SELECT	id
				FROM	<table_name>
				WHERE	code = ?";

			// Bind Code to Query
			$database->AddParam($code);

			// Execute Query
			$rs = $database->Execute($get_object_code);
			if (! $rs) {
				$this->SQLError($database->ErrorMsg());
				return false;
			}

			// Fetch Results
			list($id) = $rs->FetchRow();
			if (is_numeric($id) && $id > 0) {
				$this->id = $id;
				return $this->details();
			}
			else {
				$this->error("Record not found");
				return false;
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

			// Prepare Query
			$add_object_query = "
				INSERT
				INTO	<table_name>
				VALUES	()
			";

			// Add Parameters
			$database->AddParam($param['code']);

			// Execute Query
			$rs = $database->Execute($add_object_query);
			if (! $rs) {
				$this->SQLError($rs->ErrorMsg());
				return false;
			}

			// Fetch New ID
			$this->id = $database->Insert_ID();

			// Update Any Nullable Values
			return $this->update($params);
		}

		/********************************************/
		/* Update Existing Record					*/
		/********************************************/
		public function update(array $params): bool {
			// Clear Any Existing Errors
			$this->clearError();

			// Load Existing Data for comparison
			$this->details();

			// Initialize Services
			$database = new \Database\Service();

			// Prepare Query
			$update_object_query = "
				UPDATE	<table_name>
				SET		id = id";

			// Add Any Parameters
			if (isset($params['name'])) {
				// Be Sure to Validate! No XSS!
				if (!$this->validName($params['name'])) {
					$this->error("Invalid name");
					return false;
				}
				$update_object_query .= ",
						name = ?";
				$database->AddParam($params['name']);
			}

			// Query Where Clause
			$update_object_query .= "
				WHERE	id = ?";
			$database->AddParam($this->id);

			// Execute Query
			$database->Execute($update_object_query);
			if ($database->ErrorMsg()) {
				$this->SQLError($database->ErrorMsg());
				return false;
			}

			// Bust Cache for Updated Object
			$this->clearCache();

			// Load Updated Details from Database
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
				FROM	<table_name>
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