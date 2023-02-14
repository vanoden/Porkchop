<?php
	namespace Network;

	class Subnet Extends \BaseClass {
		public $id;
		public $address;
		public $size;
		public $type;
		public $description;

		public function add($params = array()) {
			$database = new \Database\Service();
			if ($params['type'] == 'ipv4') {
				if (! $this->validipv4($params['address'])) {
					$this->error("Invalid address");
					return false;
				}
			}
			elseif ($params['type'] == 'ipv6') {
				if (! $this->validipv6($params['address'])) {
					$this->error("Invalid address");
					return false;
				}
			}
			else {
				$this->error("Invalid type");
				return false;
			}
			if (!is_numeric($params['size'])) {
				$this->error("Invalid size");
				return false;
			}

			$database->addParam($params['address']);
			$database->addParam($params['size']);
			$database->addParam($params['type']);
				
			$add_object_query = "
				INSERT
				INTO	network_subnets
				(		address,
						size,
						type
				)
				VALUES
				(		?,?,?)
			";

			$database->Execute($add_object_query);
			if ($database->ErrorMsg()) {
				$this->SQLError($database->ErrorMsg());
				return false;
			}
			$this->id = $database->Insert_ID();

			return $this->upgrade($params);
		}

		public function getByCIDR($cidr) {
			return $this->details();
		}

		public function update($params = []): bool {
			$database = new \Database\Service();
			$update_object_query = "
				UPDATE	network_subnets
				SET		id = id";

			if (isset($params['type'])) {
				if (! $this->validType($params['type'])) {
					$this->error("Invalid type");
					return false;
				}
				else {
					$update_object_query .= ",
					type = ?";
					$database->addParam($params['type']);
				}
			}
			if (isset($params['size'])) {
				if (! is_numeric($params['size'])) {
					$this->error("Invalid size");
					return false;
				}
			}
			if (isset($params['description'])) {
				$update_object_query .= ",
					description = ?";
				$database->addParam(noXSS(trim($params['description'])));
			}

			$database->Execute($update_object_query);
			if ($database->ErrorMsg()) {
				$this->SQLError($database->ErrorMsg());
				return false;
			}
				
			return $this->details();
		}

		public function delete() {
			$database = new \Database\Service();
			$delete_object_query = "
				DELETE
				FROM	network_subnets
				WHERE	id = ?
			";
			$database->addParam($this->id);
			$database->Execute($delete_object_query);
			if ($database->ErrorMsg()) {
				$this-SQLError($database->ErrorMsg());
				return false;
			}
			return true;
		}

		public function details() {
			$database = new \Database\Service();
			$get_object_query = "
				SELECT	*
				FROM	network_subnets
				WHERE	id = ?
			";
			$database->addParam($this->id);
			$rs = $database->Execute($get_object_query);
			if (! $rs) {
				$this->SQLError($database->ErrorMsg());
				return false;
			}

			$rs->FetchNextObject(false);
			if ($rs->id) {
				return true;
			}
			else {
				$this->id = null;
				return false;
			}
		}
	}
