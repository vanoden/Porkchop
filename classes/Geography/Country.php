<?php
	namespace Geography;

	class Country extends \ORM\BaseModel {
	
		public $id;
		public $name;
		public $abbreviation;

		public function add($parameters=array()) {
			if (! isset($parameters['name'])) {
				$this->_error = "Country name required";
				return false;
			} elseif (! preg_match('/^\w[\w\.\-\_\s\,]*$/',$parameters['name'])) {
				$this->_error = "Invalid country name";
				return false;
			}
			$add_object_query = "
				INSERT
				INTO	geography_countries
				(		id,name)
				VALUES
				(		null,?)
			";
			$GLOBALS['_database']->Execute($add_object_query,array($parameters["name"]));
			if ($GLOBALS['_database']->ErrorMsg()) {
				$this->_error = "SQL Error in Geography::Country::add(): ".$GLOBALS['_database']->ErrorMsg();
				return false;
			}
			$this->id = $GLOBALS['_database']->Insert_ID();
			return $this->update($parameters);
		}

		public function update($parameters = array()) {
			$update_object_query = "
				UPDATE	geography_countries
				SET		id = id";

			$bind_params = array();
			if (isset($parameters['name'])) {
				$update_object_query .= ", name = ?";
				array_push($bind_params,$parameters['name']);
			}
			if (isset($parameters['abbreviation'])) {
				$update_object_query .= ", abbreviation = ?";
				array_push($bind_params,$parameters['abbreviation']);
			}

			$update_object_query .= "
				WHERE	id = ?";
			array_push($bind_params,$this->id);

			$GLOBALS['_database']->Execute($update_object_query,$bind_params);
			if ($GLOBALS['_database']->ErrorMsg()) {
				$this->_error = "SQL Error in Geography::Country::update(): ".$GLOBALS['_database']->ErrorMsg();
				return false;
			}
			return $this->details();
		}

		public function get($name, $columnName = 'code') {
			$get_object_query = "
				SELECT	id
				FROM	geography_countries
				WHERE	name = ?
			";

			$rs = $GLOBALS['_database']->Execute($get_object_query,array($name));
			if (! $rs) {
				$this->_error = "SQL Error in Geography::Country::get(): ".$GLOBALS['_database']->ErrorMsg();
				return false;
			}
			list($this->id) = $rs->FetchRow();
			if ($this->id) {
				app_log("Found country ".$this->id);
				return $this->details();
			} else {
				return false;
			}
		}

		public function details() {
			$get_details_query = "
				SELECT	*
				FROM	geography_countries
				WHERE	id = ?
			";
			$rs = $GLOBALS['_database']->Execute($get_details_query,array($this->id));
			if (! $rs) {
				$this->_error = "SQL Error in Geography::Country::details(): ".$GLOBALS['_database']->ErrorMsg();
				return false;
			}
			$object = $rs->FetchNextObject(false);
			if ($this->id) {
				app_log("Got details for ".$this->id,'trace');
				$this->id = $object->id;
				$this->name = $object->name;
				$this->abbreviation = $object->abbreviation;
				return true;
			} else {
				return false;
			}
		}

		public function provinces() {
			$provinceList = new \Geography\ProvinceList();
			return $provinceList->find(array('country_id' => $this->id));
		}

		public function error() {
			return $this->_error;
		}
	}
