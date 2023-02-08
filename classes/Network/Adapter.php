<?php
	namespace Network;

	class Adapter Extends \BaseClass {
		public $id;
		public $name;
		public $mac_address;
		public $type;
		public $host_id;

		public function __construct(int $id = 0) {
			$this->_tableName = 'network_adapters';
			$this->_tableUKColumn = null;

			if ($id > 0) {
				$this->id = $id;
				$this->details();
			}
		}

		public function __call($name,$parameters) {
			if ($name == "get") return $this->getAdapter($parameters[0],$parameters[1]);
			else $this->error("No method $name");
		}

		public function getAdapter($param_1,$param_2) {
			if (preg_match('/^\w\w\:\w\w\:\w\w\:\w\w\:\w\w\:\w\w$/',$param_1)) {
				$mac_address = $param_1;

				$get_object_query = "
					SELECT	id
					FROM	network_adapters
					WHERE	mac_address = ?
				";
				$rs = $GLOBALS['_database']->Execute($get_object_query,array($mac_address));
			}
			else {
				$host_id = $param_1;
				$name = $param_2;
				
				$get_object_query = "
					SELECT	id
					FROM	network_adapters
					WHERE	host_id = ?
					AND		name = ?
				";
				$rs = $GLOBALS['_database']->Execute($get_object_query,array($host_id,$name));
			}
			if (! $rs) {
				$this->SQLError($GLOBALS['_database']->ErrorMsg());
				return false;
			}
			list($id) = $rs->FetchRow();
			$this->id = $id;
			return $this->details();
		}

		public function add($parameters = array()) {
			if (! isset($parameters['name'])) {
				$this->error("name required for new adapter");
			}
			$add_object_query = "
				INSERT
				INTO	network_adapters
				(		`name`,
						`mac_address`,
						`type`,
						`host_id`
				)
				VALUES
				(		?,?,?,?)
			";

			$GLOBALS['_database']->Execute(
				$add_object_query,
				array(
					$parameters['name'],
					$parameters['mac_address'],
					$parameters['type'],
					$parameters['host_id']
				)
			);

			if ($GLOBALS['_database']->ErrorMsg()) {
				$this->SQLError($GLOBALS['_database']->ErrorMsg());
				return false;
			}

			$this->id = $GLOBALS['_database']->Insert_ID();
			return $this->update($parameters);
		}

		public function update($parameters = array()) {
			$bind_params = array();

			$update_object_query = "
				UPDATE	network_adapters
				SET		id = id
			";

			$update_object_query .= "
				WHERE	id = ?
			";
			array_push($bind_params,$this->id);

			$GLOBALS['_database']->Execute($update_object_query,$bind_params);

			if ($GLOBALS['_database']->ErrorMsg()) {
				$this->SQLError($GLOBALS['_database']->ErrorMsg());
				return false;
			}

			return $this->details();
		}

		public function details() {
			$get_object_query = "
				SELECT	 *
				FROM	network_adapters
				WHERE	id = ?
			";

			$rs = $GLOBALS['_database']->Execute($get_object_query,array($this->id));

			if (! $rs) {
				$this->SQLError($GLOBALS['_database']->ErrorMsg());
				return false;
			}

			$object = $rs->FetchNextObject(false);
			if ($object->id) {
				$this->id = $object->id;
				$this->name = $object->name;
				$this->mac_address = $object->mac_address;
				$this->type = $object->type;
				$this->host_id = $object->host_id;
			}
			return true;
		}

		public function host() {
			return new \Network\Host($this->host_id);
		}

		public function ip_addresses() {
			$addressList = new IPAddressList();

			$addresses = $addressList->find(array('adapter_id' => $this->id));
			if ($addressList->error()) {
				$this->error($addressList->error());
				return null;
			}

			return $addresses;
		}
	}
