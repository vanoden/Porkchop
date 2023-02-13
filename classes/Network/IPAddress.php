<?php
	namespace Network;

	class IPAddress Extends \BaseClass {

		public $address;
		public $prefix;
		public $gateway;
		public $adapter_id;

		public function __construct(int $id = 0) {
			$this->_tableName = "network_addresses";
			$this->_tableUKColumn = null;

			if ($id > 0) {
				$this->id = $id;
				$this->details();
			}
		}

		public function get($address): bool {
			$this->clearError();

			$database = new \Database\Service;

			if (! isset($address)) {
				$this->error("address required");
				return false;
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
			elseif(preg_match('/^[a-f0-9\:]+$/',$address)) {
				$type = 'ipv6';
			}
			elseif(preg_match('/^(\d+\.\d+\.\d+\.\d+)$/',$address)) {
				$type = 'ipv4';
			}
			else {
				$this->error('Invalid ip address');
				return false;
			}

			$get_object_query = "
				SELECT	na.id
				FROM	network_addresses na,
						network_subnets ns
				WHERE	na.address = ?
				AND		na.subnet_id = ns.id
				AND		ns.type = ?
			";
			$database->AddParam($address);
			$database->AddParam($type);

			$rs = $database->Execute($get_object_query);

			if (! $rs) {
				$this->SQLError($GLOBALS['_database']->ErrorMsg());
				return false;
			}

			list($id) = $rs->FetchRow();
			$this->id = $id;
			return $this->details();
		}
		public function add($parameters = array()) {
			if (! isset($parameters['address'])) {
				$this->error("address required for new address");
				return false;
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
				$this->SQLError($GLOBALS['_database']->ErrorMsg());
				return false;
			}

			$this->id = $GLOBALS['_database']->Insert_ID();
			return $this->update($parameters);
		}

		public function update($parameters = array()): bool {
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
				$this->SQLError($GLOBALS['_database']->ErrorMsg());
				return false;
			}

			return $this->details();
		}

		public function details(): bool {
			$get_object_query = "
				SELECT	 *
				FROM	network_addresses
				WHERE	id = ?
			";

			$rs = $GLOBALS['_database']->Execute($get_object_query,array($this->id));

			if (! $rs) {
				$this->SQLError($GLOBALS['_database']->ErrorMsg());
				return false;
			}

			$object = $rs->FetchNextObject(false);
			if (isset($object->id)) {
				$this->address = $object->address;
				$this->prefix = $object->prefix;
				$this->adapter_id = $object->adapter_id;
			}
			return true;
		}

		public function adapter() {
			return new \Network\Adapter($this->adapter_id);
		}

		public function cidr() {
		}
	}
