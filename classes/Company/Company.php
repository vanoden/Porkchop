<?php
	namespace Company;

	class Company Extends \BaseClass {
		private $schema_version = 1;
		public	$id;
		public $login;
		public $primary_domain;
		public $status;
		public $deleted;

		public function __construct($id = 0) {
			$this->_tableName = 'company_companies';
			$this->_tableUKColumn = 'name';

			if ($id > 0) {
				$this->id = $id;
				$this->details();
			}
		}

		public function details() {
			$get_details_query = "
				SELECT	*
				FROM	company_companies
				WHERE	id = ?
			";
			$rs = $GLOBALS['_database']->Execute(
				$get_details_query,
				array($this->id)
			);
			if (! $rs) {
				$this->SQLError($GLOBALS['_database']->ErrorMsg());
				return null;
			}
			$object = $rs->FetchNextObject(false);
			if (is_object($object)) {
				$this->name = $object->name;
				$this->login = $object->login;
				$this->primary_domain = $object->primary_domain;
				$this->status = $object->status;
				$this->deleted = $object->deleted;
				return $object;
			}
			else {
				app_log("No company found for id '".$this->id."'",'debug');
				return new \stdClass();
			}
		}

		public function add($parameters = array()) {
			if (! preg_match('/\w/',$parameters['name'])) {
				$this->error("name parameter required in Company::Company::add");
				return 0;
			}
			
			$add_object_query = "
				INSERT
				INTO	company_companies
				(name)
				VALUES
				(?)";
			$GLOBALS['_database']->Execute($add_object_query,array($parameters['name']));
			if ($GLOBALS['_database']->ErrorMsg()) {
				$this->SQLError($GLOBALS['_database']->ErrorMsg());
				return 0;
			}
			$this->id = $GLOBALS['_database']->Insert_ID();
			
			return $this->update($parameters);
		}

		public function update($parameters = array()){
			if (! preg_match('/^\d+$/',$this->id)) {
				$this->error("Valid id required for details in company::Company::update");
				return false;
			}

			# Update Object
			$update_object_query = "
				UPDATE	company_companies
				SET		id = id";

			$bind_params = array();
			if (isset($parameters['name'])) {
				$update_object_query .= ",
					name = ?";
				array_push($bind_params,$parameters['name']);
			}
			if (isset($parameters['status'])) {
				$update_object_query .= ",
					status = ?";
				array_push($bind_params,$parameters['status']);
			}

			$update_object_query .= "
				WHERE	id = ?
			";
			array_push($bind_params,$this->id);

			$GLOBALS['_database']->Execute(
				$update_object_query,$bind_params
			);
			if ($GLOBALS['_database']->ErrorMsg()) {
				$this->SQLError($GLOBALS['_database']->ErrorMsg());
				return false;
			}
			
			return true;
		}
	}
