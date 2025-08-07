<?php
	namespace Resource;

	class PrivilegeList extends \BaseClass {
		private $_data = array();	// Array of Privilege Objects
		public $message = "";

		/**
		 * Class Constructor
		 * @param existing privileges
		 * @return void
		 */
		public function __construct($privileges = null) {
			if (!empty($privileges)) $this->_data = $privileges;
		}

		/**
		 * Parse JSON String to get Privilege List
		 * @param string $json
		 * @return array of Storage\Privilege objects
		 */
		public function fromJSON($json) {
			app_log("Privilege JSON: ".$json,'info');
			if (empty($json)) {
				return array(new \stdClass());
			}
			else {
				// Parse JSON String into Multidimensional Associative Array
				$data = json_decode($json);
				if (!empty($data)) {
					$privileges = array();	// Array of Privilege Objects
					$allSet = false;		// We found a matching privilege

					$already = [];
					$entity_id = 0;
					// Loop Through Privilege Entities
					foreach ($data as $entity_type => $privs) {
						$read = false;
						$write = false;
						$authSet = false;
						if (isset($already[$entity_type][$entity_id])) {
							$this->error("Duplicate privilege entity type found in JSON data!");
							continue;
						}
						// Handle 'All' privileges
						if ($entity_type == 'a') {
							$allSet = true;
							if (is_array($privs)) {
								foreach ($privs as $id => $mask) {
									if (preg_match('/r/',$mask)) $read = true;
									if (preg_match('/w/',$mask)) $write = true;
								}
							}
							else {
								if (is_object($privs)) $privs = implode('',get_object_vars($privs));
								if (preg_match('/r/',$privs)) $read = true;
								if (preg_match('/w/',$privs)) $write = true;
							}
							$privilege = new \Resource\Privilege();
							$privilege->entity_type = $entity_type;
							$privilege->entity_id = 0;
							$privilege->read = $read;
							$privilege->write = $write;
							array_push($privileges,$privilege);
							array_push($this->_data,$privilege);
							$already[$entity_type][$entity_id] = 1;
						}
						elseif ($entity_type == 't') {
							$authSet = true;
							if (is_array($privs)) {
								foreach ($privs as $id => $mask) {
									if (preg_match('/r/',$mask)) $read = true;
									if (preg_match('/w/',$mask)) $write = true;
								}
							}
							else {
								if (is_object($privs)) $privs = implode('',get_object_vars($privs));
								if (preg_match('/r/',$privs)) $read = true;
								if (preg_match('/w/',$privs)) $write = true;
							}
							$privilege = new \Resource\Privilege();
							$privilege->entity_type = $entity_type;
							$privilege->entity_id = 0;
							$privilege->read = $read;
							$privilege->write = $write;
							array_push($privileges,$privilege);
							array_push($this->_data,$privilege);
							$already[$entity_type][$entity_id] = 1;
						}
						// Handle Specific Entity Privileges
						else {
							foreach ($privs as $id => $mask) {
								if (is_array($mask)) {
									foreach ($mask as $set) {
										if (is_object($set)) $set = implode('',get_object_vars($set));
										if (preg_match('/r/',$set)) $read = true;
										if (preg_match('/w/',$set)) $write = true;
										if (preg_match('/x/',$set)) $execute = true;
									}
								}
								else {
									if (is_object($mask)) $set = implode('',get_object_vars($mask));
									if (preg_match('/r/',$mask)) $read = true;
									if (preg_match('/w/',$mask)) $write = true;
								}
								$privilege = new \Resource\Privilege();
								$privilege->entity_type = $entity_type;
								$privilege->read = $read;
								$privilege->write = $write;
								if ($entity_type == 'o') {
									$organization = new \Register\Organization($id);
									if (! $organization->id) {
										$this->error("Organization not found!");
										continue;
									}
									$privilege->entity_id = $organization->id;
									array_push($privileges,$privilege);
									array_push($this->_data,$privilege);
								}
								elseif ($entity_type == 'u') {
									$user = new \Register\Customer($id);
									$privilege->entity_id = $user->id;
									array_push($privileges,$privilege);
									array_push($this->_data,$privilege);
								}
								elseif ($entity_type == 'r') {
									$role = new \Register\Role($id);
									$privilege->entity_id = $role->id;
									array_push($privileges,$privilege);
									array_push($this->_data,$privilege);
								}
								else {
									// Invalid entity type!
									// Ignore it
								}
								$already[$entity_type][$id] = 1;
							}
						}
					}
					if (! $allSet) {
						// Always Show All
						$privilege = new \Resource\Privilege();
						$privilege->entity_type = 'a';
						$privilege->entity_id = 0;
						$privilege->read = false;
						$privilege->write = false;
						array_push($privileges,$privilege);
						array_push($this->_data,$privilege);
					}
					if (! $authSet) {
						// Always Show Authenticated
						$privilege = new \Resource\Privilege();
						$privilege->entity_type = 't';
						$privilege->entity_id = 0;
						$privilege->read = false;
						$privilege->write = false;
						array_push($privileges,$privilege);
						array_push($this->_data,$privilege);
					}
					return $this->_sort($privileges);
				}
				else {
					// Build 'Empty' Privileges Array
					return [
						new \Resource\Privilege('a',0,false,false),
						new \Resource\Privilege('t',0,false,false)
					];
				}
			}
		}

		/**
		 * Write Privilege Object to JSON String
		 * @return JSON formatted string
		 */
		public function toJSON(): string {
			$privilege_array = [];
			foreach ($this->_data as $privilege) {
				if ($privilege->entity_type == 'a') {
					if ($privilege->read && $privilege->write) $privilege_array['a'] = array('r','w');
					elseif ($privilege->read) $privilege_array['a'] = array('r');
					elseif ($privilege->write) $privilege_array['a'] = array('w');
				}
				elseif ($privilege->entity_type == 't') {
					if ($privilege->read && $privilege->write) $privilege_array['t'] = array('r','w');
					elseif ($privilege->read) $privilege_array['t'] = array('r');
					elseif ($privilege->write) $privilege_array['t'] = array('w');
				}
				elseif ($privilege->entity_type == 'u' || $privilege->entity_type == 'r' || $privilege->entity_type == 'o') {
					if ($privilege->read && $privilege->write) $privilege_array[$privilege->entity_type][$privilege->entity_id] = array('r','w');
					elseif ($privilege->read) $privilege_array[$privilege->entity_type][$privilege->entity_id] = array('r');
					elseif ($privilege->write) $privilege_array[$privilege->entity_type][$privilege->entity_id] = array('w');
				}
			}
			return json_encode($privilege_array);
		}

		/**
		 * Apply updates to internal data structure
		 * @param mixed $data 
		 * @return void 
		 */
		public function apply($form_data) {
			$current = [];
			$current['a'][0] = [];
			$current['t'][0] = [];
			$applied = [];
			$applied['a'][0]['read'] = 0;
			$applied['a'][0]['write'] = 0;
			$applied['t'][0]['read'] = 0;
			$applied['t'][0]['write'] = 0;
			$keys = [];
			$keys['a']['0'] = 1;
			$keys['t']['0'] = 1;

			// Copy Internal Data to Array
			foreach ($this->_data as $privilege) {
				$keys[$privilege->entity_type][$privilege->entity_id] = 1;
				if ($privilege->entity_type == 'a') {
					if ($privilege->read) $current['a']['0']['read'] = 1;
					else $current['a']['0']['read'] = 0;
					if ($privilege->write) $current['a']['0']['write'] = 1;
					else $current['a']['0']['write'] = 0;
				}
				elseif ($privilege->entity_type == 't') {
					if ($privilege->read) $current['t']['0']['read'] = 1;
					else $current['t']['0']['read'] = 0;
					if ($privilege->write) $current['t']['0']['write'] = 1;
					else $current['t']['0']['write'] = 0;
				}
				else {
					if ($privilege->read) $current[$privilege->entity_type][$privilege->entity_id]['read'] = 1;
					else $current[$privilege->entity_type][$privilege->entity_id]['read'] = 0;
					if ($privilege->write) $current[$privilege->entity_type][$privilege->entity_id]['write'] = 1;
					else $current[$privilege->entity_type][$privilege->entity_id]['write'] = 0;
				}
			}

			// Parse Form Data to Array
			foreach ($form_data as $entity_type => $entity_data) {
				foreach ($entity_data as $entity_id => $accessLevels) {
					$entity_type = preg_replace('/\'/','',$entity_type);
					$keys[$entity_type][$entity_id] = 1;
					// Store in Array
					if ($entity_type == 'a') {
						if (isset($accessLevels["'r'"]) && $accessLevels["'r'"] == 1) $applied['a']['0']['read'] = 1;
						else $applied['a']['0']['read'] = 0;
						if (isset($accessLevels["'w'"]) && $accessLevels["'w'"] == 1) $applied['a']['0']['write'] = 1;
						else $applied['a']['0']['write'] = 0;
					}
					elseif ($entity_type == 't') {
						if (isset($accessLevels["'r'"]) && $accessLevels["'r'"] == 1) $applied['t']['0']['read'] = 1;
						else $applied['t']['0']['read'] = 0;
						if (isset($accessLevels["'w'"]) && $accessLevels["'w'"] == 1) $applied['t']['0']['write'] = 1;
						else $applied['t']['0']['write'] = 0;
					}
					else {
						if (isset($accessLevels["'r'"]) && $accessLevels["'r'"] == 1) $applied[$entity_type][$entity_id]['read'] = 1;
						else $applied[$entity_type][$entity_id]['read'] = 0;
						if (isset($accessLevels["'w'"]) && $accessLevels["'w'"] == 1) $applied[$entity_type][$entity_id]['write'] = 1;
						else $applied[$entity_type][$entity_id]['write'] = 0;
					}
				}
			}

			// Compare Current and Applied Data
			foreach ($keys as $entity_type => $entity_data) {
				foreach ($entity_data as $id => $y) {
					$privilege_elem_id = $this->_privilegeElem($entity_type,$id);
					if ($privilege_elem_id < 0) {
						$this->error("Privilege not found!");
						continue;
					}
					foreach (array('read','write') as $action) {
						$existing	= $current[$entity_type][$id][$action];
						$form		= $applied[$entity_type][$id][$action];
						$msgPriv = new \Resource\Privilege($entity_type,$id);
						if ($existing && ! $form) {
							if ($entity_type == 'a') $this->message .= "Revoked ".$action." privilege from ".$msgPriv->entity_type_name()."\n";
							elseif ($entity_type == 't') $this->message .= "Revoked ".$action." privilege from ".$msgPriv->entity_type_name()."\n";
							else $this->message .= "Revoked ".$action." privilege from ".$msgPriv->entity_type_name()." ".$msgPriv->entity_name()."\n";
							$this->_data[$privilege_elem_id]->$action = false;
						}
						elseif (! $existing && $form) {
							if ($entity_type == 'a') $this->message .= "Granted ".$action." privilege to ".$msgPriv->entity_type_name()."\n";
							elseif ($entity_type == 't') $this->message .= "Granted ".$action." privilege to ".$msgPriv->entity_type_name()."\n";
							else $this->message .= "Granted ".$action." privilege to ".$msgPriv->entity_type_name()." ".$msgPriv->entity_name()."\n";
							$this->_data[$privilege_elem_id]->$action = true;
						}
					}
				}
			}
		}

		/**
		 * Add a Privilege Object to the List
		 * @param mixed $entity_type
		 * @param id $entity_id
		 * @param bool $read
		 * @param bool $write
		 * @return int
		 */
		public function grant($entity_type,$entity_id,$read = false,$write = false) {
			$privilege = new \Resource\Privilege($entity_type,(int)$entity_id,$read,$write);
			array_push($this->_data,$privilege);
			return count($this->_data) - 1;
		}

		/**
		 * Find and return the id of a specified privilege element existing in the internal data array
		 * @param mixed $entity_type 
		 * @param mixed $entity_id 
		 * @return int 
		 */
		private function _privilegeElem($entity_type,$entity_id) {
			$elem = 0;
			for ($elem = 0; $elem < count($this->_data); $elem ++) {
				if ($this->_data[$elem]->entity_type == $entity_type && $this->_data[$elem]->entity_id == $entity_id) return $elem;
			}
			return -1;
		}

		public function privileges() {
			return $this->_sort($this->_data);
		}

		public function privilege($entity_type,$entity_id = 0) {
			for ($elem = 0; $elem < count($this->_data); $elem ++) {
				if ($this->_data[$elem]->entity_type == $entity_type && $this->_data[$elem]->entity_id == $entity_id) {
					if ($entity_type == 't' && empty($entity_id)) return null;
					return $this->_data[$elem];
				}
			}
		}

		private function _sort($privs) {
			$return = array();
			foreach ($privs as $priv) {
				if ($priv->entity_type == 'a') array_push($return,$priv);
			}
			foreach ($privs as $priv) {
				if ($priv->entity_type == 't') array_push($return,$priv);
			}
			foreach ($privs as $priv) {
				if ($priv->entity_type == 'r') array_push($return,$priv);
			}
			foreach ($privs as $priv) {
				if ($priv->entity_type == 'o') array_push($return,$priv);
			}
			foreach ($privs as $priv) {
				if ($priv->entity_type == 'u') array_push($return,$priv);
			}
			return $return;
		}
	}