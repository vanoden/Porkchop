<?php
	class Blank Extends \BaseModel {
	
		public int $id;

		/********************************************/
		/* Instance Constructor						*/
		/********************************************/
		public function __construct(int $id = null) {
		    
			// Set Table Name
			$this->_tableName = 'table name here';

			// Set cache key name - MUST Be Unique to Class
			$this->_cacheKeyPrefix = $this->_tableName;

			// Add Status(es) for Validation with validStatus()
			$this->_addStatus(array());

			// Add Type(s) for Validation with validType()
			$this->_addType(array());

			// Load Record for Specified ID if given
    		parent::__construct($id);
		}

		/********************************************/
		/* Add New Object Record Given Parameters	*/
		/* Must include non-nullable fields.		*/
		/* Others should be handled in update().	*/
		/********************************************/
		public function add($parameters = []) {
			// Clear Any Existing Errors
			$this->clearError();

			// Initialize Services
			$porkchop = new \Porkchop();
			$database = new \Database\Service();

			// Default New Object Code If Not Provided
			if (empty($params['code'])) $params['code'] = $porkchop->uuid();

			if ($this->ukExists($params['code'])) {
				$this->error("Code already used");
				return false;
			}

			// Prepare Query
			$add_object_query = "
				INSERT
				INTO	`".$this->_tableName."`
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

			// audit the update event
			$auditLog = new \Site\AuditLog\Event();
			$auditLog->add(array(
				'instance_id' => $this->id,
				'description' => 'Added new '.$this->_objectName(),
				'class_name' => get_class($this),
				'class_method' => 'add'
			));

			// Update Any Nullable Values
			return $this->update($params);
		}

		/********************************************/
		/* Update Existing Record.  Should not		*/
		/* allow update of unique key or id			*/
		/********************************************/
		public function update($params = []): bool {
			// Clear Any Existing Errors
			$this->clearError();

			// Load Existing Data for comparison
			$this->details();

			// Initialize Services
			$database = new \Database\Service();
			$cache = $this->cache();

			// Prepare Query
			$update_object_query = "
				UPDATE	`".$this->_tableName."`
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
			
			// audit the update event
			$auditLog = new \Site\AuditLog\Event();
			$auditLog->add(array(
				'instance_id' => $this->id,
				'description' => 'Updated '.$this->_objectName(),
				'class_name' => get_class($this),
				'class_method' => 'update'
			));

			// Bust Cache for Updated Object
			$cache->delete();

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
