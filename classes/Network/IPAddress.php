<?php
	namespace Network;

	class IPAddress {
		private $_error;
		public $id;
		public $address;
		public $prefix;
		public $gateway;

		public function __construct($id = 0) {
			if ($id > 0) {
				$this->id = $id;
				$this->details();
			}
		}
		public function get($address) {
			if (! isset($address)) {
				$this->_error = "address required";
				return null;
			}
			if (preg_match('/^(\d+\.\d+\.\d+\.\d+)\/(\d+)$/',$address,$matches)) {
				$address = $matches[1];
				$prefix = $matches[2];
				$type = 'ipv4';
			}
			elseif(preg_match('/^([a-f0-9\:]+)\/(\d+)$/',$address,$matches)) {
				$address = $matches[1];
				$prefix = $matches[2];
				$type = 'ipv6';
			}
			elseif(preg_match('/^[a-f0-9\:]+/$',$address)) {
				$type = 'ipv6';
			}
			elseif(preg_match('/^(\d+\.\d+\.\d+\.\d+)$/',$address)) {
				$type = 'ipv4';
			}
			else {
				$this->_error = 'Invalid ip address';
				return null;
			}

			$get_object_query = "
				SELECT	id
				FROM	network_addresses
				WHERE	type = ?
				AND		address = ?
			";

			$rs = $GLOBALS['_database']->Execute(
				$get_object_query,
				array($type,$address)
			);

			if (! $rs) {
				$this->_error = "SQL Error in Network::IPAddress::get(): ".$GLOBALS['_database']->ErrorMsg();
				return null;
			}

			list($id) = $rs->FetchRow();
			$this->id = $id;
			return $this->details();
		}
		public function add($parameters = array()) {
			if (! isset($parameters['address'])) {
				$this->_error = "address required for new address";
			}
			$add_object_query = "
				INSERT
				INTO	network_addresses
				(		`address`,
						`prefix`,
						`adapter_id`
				)
				VALUES
				(		?,?,?)
			";

			$GLOBALS['_database']->Execute(
				$add_object_query,
				array(
					$parameters['address'],
					$parameters['prefix'],
					$parameters['adapter_id']
				)
			);

			if ($GLOBALS['_database']->ErrorMsg()) {
				$this->_error = "SQL Error in Network::Address::add(): ".$GLOBALS['_database']->ErrorMsg();
				return false;
			}

			$this->id = $GLOBALS['_database']->Insert_ID();
			return $this->update($parameters);
		}

		public function update($parameters = array()) {
			$bind_params = array();

			$update_object_query = "
				UPDATE	network_addresses
				SET		id = id
			";

			$update_object_query .= "
				WHERE	id = ?
			";
			array_push($bind_params,$this->id);

			$GLOBALS['_database']->Execute($update_object_query,$bind_params);

			if ($GLOBALS['_database']->ErrorMsg()) {
				$this->_error = "SQL Error in Network::Address::update(): ".$GLOBALS['_database']->ErrorMsg();
				return false;
			}

			return $this->details();
		}

		public function details() {
			$get_object_query = "
				SELECT	 *
				FROM	network_addresses
				WHERE	id = ?
			";

			$rs = $GLOBALS['_database']->Execute($get_object_query,array($this->id));

			if (! $rs) {
				$this->_error = "SQL Error in Network::Address::details(): ".$GLOBALS['_database']->ErrorMsg();
				return false;
			}

			$object = $rs->FetchNextObject(false);
			if ($object->id) {
				$this->address = $object->address;
				$this->prefix = $object->prefix;
				$this->adapter = new Adapter($object->adapter_id);
			}
		}

		public function cidr() {
		}

		public function error() {
			return $this->_error;
		}
	}
