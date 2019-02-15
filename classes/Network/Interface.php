<?php
	namespace Network;

	class Interface {
		private $_error;
		public $id;
		public $name;
		public $mac_address;
		public $type;
		public $host;

		public function __construct($id = 0) {
			if ($id > 0) {
				$this->id = $id;
				$this->details();
			}
		}

		public function add($parameters = array()) {
			if (! isset($parameters['name'])) {
				$this->_error = "name required for new interface";
			}
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
				$this->_error = "SQL Error in Network::Interface::add(): ".$GLOBALS['database']->ErrorMsg();
				return false;
			}

			$this->id = $GLOBALS['database']->Insert_ID();
			return $this->update($parameters);
		}

		public function update($parameters = array()) {
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
				$this->_error = "SQL Error in Network::Interface::update(): ".$GLOBALS['database']->ErrorMsg();
				return false;
			}

			return $this->details();
		}

		public function details() {
			$get_object_query = "
				SELECT	 *
				FROM	network_interfaces
				WHERE	id = ?
			";

			$rs = $GLOBALS['database']->Execute(array($this->id));

			if (! $rs) {
				$this->_error = "SQL Error in Network::Interface::details(): ".$GLOBALS['database']->ErrorMsg();
				return false;
			}

			$object = $rs->FetchNextObject($false);
			if ($object->id) {
				$this->id = $object->id;
				$this->name = $object->name;
				$this->mac_address = $object->mac_address;
				$this->type = $object->type;
				$this->host = new Network::Host($object->host_id);
			}
			return true;
		}

		public function ip_addresses() {
			$addressList = new AddressList();

			$addresses = $addressList->find(array('interface_id' => $this->id));
			if ($addressList->error()) {
				$this->_error = $addressList->error();
				return null;
			}

			return $addresses;
		}

		public function error() {
			return $this->_error;
		}
	}
?>
