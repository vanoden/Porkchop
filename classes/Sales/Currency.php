<?php
	namespace Sales;

	class Currency Extends \BaseModel {

		public $name;
		public $symbol;

		public function __construct($id = 0) {
			$this->_tableName = 'sales_currencies';
			$this->_cacheKeyPrefix = 'sales.currency';
			$this->_tableUKColumn = 'name';
			$this->_addField('name','symbol');
    		parent::__construct($id);
		}

		public function add($parameters = []) {

			$this->clearError();
			if (empty($parameters['name'])) {
				$this->error("Currency name required");
				return false;
			}

			if (! $this->validName($parameters['name'])) {
				$this->error("Invalid currency name '".$parameters['name']."'");
				return false;
			}

			$add_currency_query = "
				INSERT
				INTO	sales_currencies
						(name)
				VALUE	(?)
			";
			$GLOBALS['_database']->Execute($add_currency_query,array($parameters['name']));
			if ($GLOBALS['_database']->ErrorMsg()) {
				$this->SQLError($GLOBALS['_database']->ErrorMsg());
				return false;
			}
			list($id) = $GLOBALS['_database']->Insert_ID();
			
            // audit the add event
            $auditLog = new \Site\AuditLog\Event();
            $auditLog->add(array(
                'instance_id' => $this->id,
                'description' => 'Added new '.$this->_objectName(),
                'class_name' => get_class($this),
                'class_method' => 'add'
            ));

			$this->id = $id;
			return $this->update($parameters);
		}

		public function update($parameters = []): bool {
			$update_object_query = "
				UPDATE	sales_currencies
				SET		id = id
			";
			$bind_params = array();
			if (!empty($parameters['name'])) {
				if (! $this->validName($parameters['name'])) {
					$this->error("Invalid name");
					return false;
				}
				$update_object_query .= ",
						name = ?";
				array_push($bind_params,$parameters['name']);
			}

			if (!empty($parameters['symbol'])) {
				$update_object_query .= ",
						symbol = ?";
				array_push($bind_params,$parameters['symbol']);
			}
			$update_object_query .= "
				WHERE	id = ?";
			array_push($bind_params,$this->id);
			$GLOBALS['_database']->Execute($update_object_query,$bind_params);
			if ($GLOBALS['_database']->ErrorMsg()) {
				$this->SQLError($GLOBALS['_database']->ErrorMsg());
				return false;
			}
			$cache = $this->cache();
			$cache->delete();
			
			// audit the update event
			$auditLog = new \Site\AuditLog\Event();
			$auditLog->add(array(
				'instance_id' => $this->id,
				'description' => 'Updated '.$this->_objectName(),
				'class_name' => get_class($this),
				'class_method' => 'update'
			));	
						
			return $this->details();
		}

		public function get($name): bool {
			$get_object_query = "
				SELECT	id
				FROM	sales_currencies
				WHERE	name = ?";
			$rs = $GLOBALS['_database']->Execute($get_object_query,array($name));
			if (! $rs) {
				$this->SQLError($GLOBALS['_database']->ErrorMsg());
				return null;
			}
			list ($id) = $rs->FetchRow();
			if (empty($id)) {
				$this->error("Currency not found");
				return false;
			}
			$this->id = $id;
			return $this->details();
		}
	}
?>
