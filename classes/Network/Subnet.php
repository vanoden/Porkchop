<?php
	namespace Network;

	class Subnet Extends \BaseModel {
		public $address;
		public $size;
		public $type;
		public $description;

		public function __construct($id = 0) {
			$this->_tableName = 'network_subnets';
			parent::__construct($id);
		}

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

			$database->AddParam($params['address']);
			$database->AddParam($params['size']);
			$database->AddParam($params['type']);
				
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

			// add audit log
			$auditLog = new \Site\AuditLog\Event();
			$auditLog->add(array(
				'instance_id' => $this->id,
				'description' => 'Added new '.$this->_objectName(),
				'class_name' => get_class($this),
				'class_method' => 'add'
			));

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
					$database->AddParam($params['type']);
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
				$database->AddParam(noXSS(trim($params['description'])));
			}

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

			return $this->details();
		}

		public function delete(): bool {
			
			$database = new \Database\Service();
			$delete_object_query = "
				DELETE
				FROM	network_subnets
				WHERE	id = ?
			";
			$database->AddParam($this->id);
			$database->Execute($delete_object_query);
			if ($database->ErrorMsg()) {
				$this->SQLError($database->ErrorMsg());
				return false;
			}

			// audit the delete event
			$auditLog = new \Site\AuditLog\Event();
			$auditLog->add(array(
				'instance_id' => $this->id,
				'description' => 'Deleted '.$this->_objectName(),
				'class_name' => get_class($this),
				'class_method' => 'delete'
			));	

			return true;
		}

		public function details(): bool {
			$database = new \Database\Service();
			$get_object_query = "
				SELECT	*
				FROM	network_subnets
				WHERE	id = ?
			";
			$database->AddParam($this->id);
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
