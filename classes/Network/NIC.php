<?php
	namespace Network;

	class NIC extends \BaseModel {

		public $name;
		public $mac_address;
		public $type;
		public $host;

		/**
		 * Constructor
		 * @param int $id 
		 * @return void 
		 */
		public function __construct($id = 0) {
            $this->_tableName = 'network_interfaces';
			if ($id > 0) {
				$this->id = $id;
				$this->details();
			}
		}

		/**
		 * Add a new network interface
		 * @param array $parameters Parameters for the new interface
		 * @return bool
		 */
		public function add($parameters = []) {

			if (! isset($parameters['name'])) $this->_error = "name required for new interface";

			$add_object_query = "
				INSERT
				INTO	network_interfaces
				(		`name`,
						`mac_address`,
						`type`,
						`host_id`
				)
				VALUES
				(		?,?,?,?)
			";

			$GLOBALS['database']->Execute(
				$add_object_query,
				array(
					$parameters['name'],
					$parameters['mac_address'],
					$parameters['type'],
					$parameters['host_id']
				)
			);

			if ($GLOBALS['database']->ErrorMsg()) {
				$this->_error = "SQL Error in Network::NIC::add(): ".$GLOBALS['database']->ErrorMsg();
				return false;
			}

			$this->id = $GLOBALS['database']->Insert_ID();

			// add audit log
			$auditLog = new \Site\AuditLog\Event();
			$auditLog->add(array(
				'instance_id' => $this->id,
				'description' => 'Added new '.$this->_objectName(),
				'class_name' => get_class($this),
				'class_method' => 'add'
			));

			return $this->update($parameters);
		}

		/**
		 * Update the network interface in the database
		 * @param array $parameters 
		 * @return bool 
		 */
		public function update($parameters = array()): bool {

			$bind_params = array();

			$update_object_query = "
				UPDATE	network_interfaces
				SET		id = id
			";

			$update_object_query .= "
				WHERE	id = ?
			";
			array_push($bind_params,$this->id);

			$GLOBALS['database']->Execute($update_object_query,$bind_params);

			if ($GLOBALS['database']->ErrorMsg()) {
				$this->_error = "SQL Error in Network::NIC::update(): ".$GLOBALS['database']->ErrorMsg();
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

			return $this->details();
		}

		/**
		 * Get the network interface details from the database
		 * @return bool 
		 */
		public function details(): bool {
			$get_object_query = "
				SELECT	 *
				FROM	network_interfaces
				WHERE	id = ?
			";

			$rs = $GLOBALS['database']->Execute(array($this->id));

			if (! $rs) {
				$this->_error = "SQL Error in Network::NIC::details(): ".$GLOBALS['database']->ErrorMsg();
				return false;
			}

			$object = $rs->FetchNextObject(false);
			if ($object->id) {
				$this->id = $object->id;
				$this->name = $object->name;
				$this->mac_address = $object->mac_address;
				$this->type = $object->type;
				$this->host = new Host($object->host_id);
			}
			return true;
		}

		public function ip_addresses() {
			$addressList = new IPAddressList();

			$addresses = $addressList->find(array('interface_id' => $this->id));
			if ($addressList->error()) {
				$this->_error = $addressList->error();
				return null;
			}

			return $addresses;
		}
	}
