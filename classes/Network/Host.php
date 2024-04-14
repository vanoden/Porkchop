<?php
	namespace Network;

	class Host Extends \BaseModel {

		public $name;
		public $domain;
		public $os_name;
		public $os_version;

		public function __construct(int $id = 0) {
			$this->_tableName = 'network_hosts';
			$this->_tableUKColumn = null;
    		parent::__construct($id);
		}

		public function add($parameters = []) {

			if (! isset($parameters['name'])) {
				$this->error("name required for new host");
			}
			$add_object_query = "
				INSERT
				INTO	network_hosts
				(		`name`,
						`domain_id`
				)
				VALUES
				(		?,? )
			";

			$GLOBALS['_database']->Execute(
				$add_object_query,
				array(
					$parameters['name'],
					$parameters['domain_id'],
				)
			);

			if ($GLOBALS['_database']->ErrorMsg()) {
				$this->SQLError($GLOBALS['_database']->ErrorMsg());
				return false;
			}

			$this->id = $GLOBALS['_database']->Insert_ID();

			// audit the add event
			$auditLog = new \Site\AuditLog\Event();
			$auditLog->add(array(
				'instance_id' => $this->id,
				'description' => 'Added new '.$this->_objectName(),
				'class_name' => get_class($this),
				'class_method' => 'add'
			));

			return $this->update($parameters);
		}

		public function getByIPAddress($ip_address) {
			$address = new \Network\IPAddress();
			if ($address->get($ip_address)) {
				$adapter = $address->adapter();
				$this->id = $adapter->host_id;
				return $this->details();
			}
			else {
				return false;
			}
		}

		public function getByName($domain_id,$name) {

			$get_object_query = "
				SELECT	id
				FROM	network_hosts
				WHERE	domain_id = ?
				AND		name = ?
			";
			$rs = $GLOBALS['_database']->Execute($get_object_query,array($domain_id,$name));
			if (! $rs) {
				$this->SQLError($GLOBALS['_database']->ErrorMsg());
				return false;
			}
			list($id) = $rs->FetchRow();
			$this->id = $id;
			return $this->details();
		}

		public function update($parameters = []): bool {
			$bind_params = array();

			$update_object_query = "
				UPDATE	network_hosts
				SET		id = id
			";

			if (isset($parameters['name'])) {
				$update_object_query .= ",
						name = ?";
				array_push($bind_params,$parameters['name']);
			}
			if (isset($parameters['domain_id'])) {
				$update_object_query .= ",
						domain_id = ?";
				array_push($bind_params,$parameters['domain_id']);
			}
			if (isset($parameters['os_name'])) {
				$update_object_query .= ",
						os_name = ?";
				array_push($bind_params,$parameters['os_name']);
			}
			if (isset($parameters['os_version'])) {
				$update_object_query .= ",
						os_version = ?";
				array_push($bind_params,$parameters['os_version']);
			}
			$update_object_query .= "
				WHERE	id = ?
			";
			array_push($bind_params,$this->id);

			$GLOBALS['_database']->Execute($update_object_query,$bind_params);

			if ($GLOBALS['_database']->ErrorMsg()) {
				$this->SQLError($GLOBALS['_database']->ErrorMsg());
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

		public function adapters() {
			$adapterList = new AdapterList();

			$adapters = $adapterList->find(array('host_id' => $this->id));
			if ($adapterList->error()) {
				$this->error($adapterList->error());
				return null;
			}

			return $adapters;
		}

		public function fqdn() {
			$fqdn = $this->name;
			if (isset($this->domain)) $fqdn .= ".".$this->domain;
			return $fqdn;
		}

		public function CAPTCHARequired(): bool {
			return false;
		}
	}
