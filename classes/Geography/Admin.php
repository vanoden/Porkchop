<?php
	namespace Geography;

	class Admin extends \BaseClass {
		public $id;
		public $_error;
		public $country_id;
		public $name;
		public $abbreviation;
		public $code;

		public function __construct(int $id = 0) {
			$this->_tableName = 'geography_provinces';

			if ($id > 0) {
				$this->id = $id;
				$this->details();
			}
		}

		public function add($parameters) {
			if (isset($parameters['country_id'])) {
				$country = new Country($parameters['country_id']);
				if (!$country->id) {
					$this->_error = "Country not found";
					return false;
				}
			}
			else {
				$this->_error = "country_id required";
				return false;
			}
			if (! isset($parameters['name']) || ! preg_match('/^\w.*$/',$parameters['name'])) {
				$this->_error = "Name required";
				return false;
			}
            if (! isset($parameters['abbreviation'])) {
                $this->_error = "Abbreviation required";
                return false;
            }
			if ($this->get($country->id,$parameters['name'])) {
				$this->_error = "Area already exists";
				return false;
			}
			if (empty($parameters['code'])) {
				$parameters['code'] = uniqid();
			}
			$add_object_query = "
				INSERT
				INTO	geography_provinces
				(		abbreviation,code,country_id,name)
				VALUES
				(		?,?,?,?)
			";
			$GLOBALS['_database']->Execute(
                $add_object_query,
                array(
                    $parameters['abbreviation'],
                    $parameters['code'],
                    $country->id,$parameters['name']
                )
            );
			if ($GLOBALS['_database']->ErrorMsg()) {
				$this->_error = "SQL Error in Geography::Admin::add(): ".$GLOBALS['_database']->ErrorMsg();
				return false;
			}
			$this->id = $GLOBALS['_database']->Insert_ID();
			return $this->update($parameters);
		}

		public function update($parameters = array()) {
			if (! isset($this->id)) {
				$this->_error = "id required for update";
				return false;
			}

			$bind_params = array();
			$update_object_query = "
				UPDATE	geography_provinces
				SET		id = id";
			if (isset($parameters['name'])) {
				$update_object_query .= ", name = ?";
				array_push($bind_params,$parameters['name']);
			}
			if (isset($parameters['country_id'])) {
				$update_object_query .= ", country_id = ?";
				array_push($bind_params,$parameters['country_id']);
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
				$this->_error = "SQL Error in Geography::Admin::update(): ".$GLOBALS['_database']->ErrorMsg();
				return false;
			}
			return $this->details();
		}

		public function __call($name, $arguments) {
			if ($name == "get") return $this->getProvince($arguments[0],$arguments[1]);
			else $this->error("Method '$name' not found");
		}

		public function getProvince($country_id,$name): bool {
            app_log("Country $country_id Name $name");
			if (strlen($name) < 3) return $this->getByAbbreviation($country_id,$name);
			$get_object_query = "
				SELECT	id
				FROM	geography_provinces
				WHERE	country_id = ?
				AND		name = ?
			";

			$rs = $GLOBALS['_database']->Execute($get_object_query,array($country_id,$name));
			if (! $rs) {
				$this->SQLError($GLOBALS['_database']->ErrorMsg());
				return false;
			}
			list($this->id) = $rs->FetchRow();
			if ($this->id > 0) {
				app_log("Found province ".$this->id);
				return $this->details();
			}
			return false;
		}

		public function getByAbbreviation($country_id,$abbrev) {
			$get_object_query = "
				SELECT	id
				FROM	geography_provinces
				WHERE	country_id = ?
				AND		abbreviation = ?
			";
			$rs = $GLOBALS['_database']->Execute($get_object_query,array($country_id,$abbrev));
			if (! $rs) {
				$this->SQLError($GLOBALS['_database']->ErrorMsg());
				return false;
			}
			list($this->id) = $rs->FetchRow();
			return $this->details();
		}
		public function details() {
			$get_object_query = "
				SELECT	*
				FROM	geography_provinces
				WHERE	id = ?
			";
			$rs = $GLOBALS['_database']->Execute($get_object_query,array($this->id));
			if (! $rs) {
				$this->_error = "SQL Error in Geography::Admin::details(): ".$GLOBALS['_database']->ErrorMsg();
				return false;
			}
			$object = $rs->FetchNextObject(false);
			if ($object->id) {
				$this->id = $object->id;
				$this->country_id = $object->country_id;
				$this->name = $object->name;
				$this->abbreviation = $object->abbreviation;
				$this->code = $object->code;
				return true;
			}
			else {
				$this->id = null;
				return false;
			}
		}

		public function country() {
			return new \Geography\Country($this->country_id);
		}
	}
