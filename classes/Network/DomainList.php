<?php
	namespace Network;

	class DomainList {
		private $_error;
		private $_count;

		public function find($parameters) {
			$get_list_query = "
				SELECT	id
				FROM	network_domains
				WHERE	id = id
			";

			$bind_params = array();

			if (isset($parameters['name']) && strlen($parameters['name'])) {
				$get_list_query .= "
				AND		name = ?";
				array_push($bind_params,$parameters['name']);
			}

			$get_list_query .= "
				ORDER BY name";

			$rs = $GLOBALS['_database']->Execute(
				$get_list_query,
				$bind_params
			);

			if (! $rs) {
				$this->_error = "SQL Error in Network::DomainList::find(): ".$GLOBALS['_database']->ErrorMsg();
				return null;
			}

			$objects = array();
			while(list($id) = $rs->FetchRow()) {
				$object = new Domain($id);
				array_push($objects,$object);
				$this->_count ++;
			}
			return $objects;
		}

		public function error() {
			return $this->_error;
		}

		public function count() {
			return $this->_count;
		}
	}
?>