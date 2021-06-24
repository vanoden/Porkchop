<?php
	namespace Network;

	class Domain {
		private $_error;
		public $id;
		public $name;

		public function __construct($id = 0) {
			if ($id > 0) {
				$this->id = $id;
				$this->details();
			}
		}

		public function add($parameters = array()) {
			if (! isset($parameters['name'])) {
				$this->_error = "name required for new host";
			}
			$add_object_query = "
				INSERT
				INTO	network_domains
				(		`name`
				)
				VALUES
				(		? )
			";

			$GLOBALS['_database']->Execute(
				$add_object_query,
				array(
					$parameters['name']
				)
			);

			if ($GLOBALS['_database']->ErrorMsg()) {
				$this->_error = "SQL Error in Network::Domain::add(): ".$GLOBALS['_database']->ErrorMsg();
				return false;
			}

			$this->id = $GLOBALS['_database']->Insert_ID();
			return $this->update($parameters);
		}

		public function get($name) {
			$get_object_query = "
				SELECT	id
				FROM	network_domains
				WHERE	name = ?
			";
			$rs = $GLOBALS['_database']->Execute($get_object_query,array($name));
			if (! $rs) {
				$this->_error = "SQL Error in Network::Domain::get(): ".$GLOBALS['_database']->ErrorMsg();
				return false;
			}
			list($id) = $rs->FetchRow();
			$this->id = $id;
			return $this->details();
		}

		public function update($parameters = array()) {
			$bind_params = array();

			$update_object_query = "
				UPDATE	network_domains
				SET		id = id
			";

			if (isset($parameters['name'])) {
				$update_object_query .= ",
						name = ?";
				array_push($bind_params,$parameters['name']);
			}

			$update_object_query .= "
				WHERE	id = ?
			";
			array_push($bind_params,$this->id);

			$GLOBALS['_database']->Execute($update_object_query,$bind_params);

			if ($GLOBALS['_database']->ErrorMsg()) {
				$this->_error = "SQL Error in Network::Domain::update(): ".$GLOBALS['_database']->ErrorMsg();
				return false;
			}

			return $this->details();
		}

		public function details() {
			$get_object_query = "
				SELECT	 *
				FROM	network_domains
				WHERE	id = ?
			";

			$rs = $GLOBALS['_database']->Execute($get_object_query,array($this->id));

			if (! $rs) {
				$this->_error = "SQL Error in Network::Host::details(): ".$GLOBALS['_database']->ErrorMsg();
				return false;
			}

			$object = $rs->FetchNextObject(false);
			if ($object->id) {
				$this->id = $object->id;
				$this->name = $object->name;
			}
			return true;
		}

		public function hosts() {
			$hostList = new HostList();

			$hosts = $hostList->find(array('domain_id' => $this->id));
			if ($hostList->error()) {
				$this->_error = $hostList->error();
				return null;
			}

			return $hosts;
		}

		public function error() {
			return $this->_error;
		}
	}
