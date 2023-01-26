<?php
	namespace Geography;

	class Country extends \BaseClass {
		public $id;
		public $name;
		public $abbreviation;

		public function __construct(int $id = 0) {
			$this->_tableName = "geography_countries";
			$this->_tableUKColumn = "name";

			if ($id > 0) {
				$this->id = $id;
				$this->details();
			}
		}
		public function add($parameters=array()) {
			if (! isset($parameters['name'])) {
				$this->_error = "Country name required";
				return false;
			} elseif (! preg_match('/^\w[\w\.\-\_\s\,]*$/',$parameters['name'])) {
				$this->_error = "Invalid country name '".$parameters['name']."'";
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
	}
