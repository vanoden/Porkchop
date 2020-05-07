<?php
	namespace Company;

	class Company {
		private $schema_version = 1;
		public	$error;
		public	$id;
		public $login;
		public $primary_domain;
		public $status;
		public $deleted;

		public function __construct($id = 0) {
			if ($id > 0) {
				$this->id = $id;
				$this->details();
			}
		}

		public function get($name) {
			$get_object_query = "
				SELECT	id
				FROM	company_companies
				WHERE	name = ?
			";
			$rs = $GLOBALS['_database']->Execute(
				$get_object_query,
				array($name)
			);
			if (! $rs) {
				$this->error = "SQL Error in Site::Company::get(): ".$GLOBALS['_database']->ErrorMsg();
				return undef;
			}
			list($id) = $rs->FetchRow();
			$this->id = $id;
			return $this->details();
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
				$this->error = "SQL Error in Site::Company::details(): ".$GLOBALS['_database']->ErrorMsg();
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
				app_log("No company found for id '".$this->id."'",'error');
				return new \stdClass();
			}
		}

		public function add($parameters = array()) {
			if (! preg_match('/\w/',$parameters['name'])) {
				$this->error = "name parameter required in company::Company::add";
				return 0;
			}
			
			$add_object_query = "
				INSERT
				INTO	company_companies
				(name)
				VALUES
				(".$GLOBALS['_database']->qstr($parameters['name'],get_magic_quotes_gpc()).")";
			$GLOBALS['_database']->Execute($add_object_query);
			if ($GLOBALS['_database']->ErrorMsg()) {
				$this->error = "SQL Error in company::Company::add: ".$GLOBALS['_database']->ErrorMsg();
				return 0;
			}
			$this->id = $GLOBALS['_database']->Insert_ID();
			
			return $this->update($this->id,$parameters);
		}

		public function update($parameters = array()){
			if (! preg_match('/^\d+$/',$this->id)) {
				$this->error = "Valid id required for details in company::Company::update";
				return undef;
			}

			if ($parameters['name'])
				$update_object_query .= ",
					name = ".$GLOBALS['_database']->qstr($parameters['name'],get_magic_quotes_gpc());

			# Update Object
			$update_object_query = "
				UPDATE	company_companies
				SET		id = id";
			
			$update_object_query .= "
				WHERE	id = ?
			";

			$GLOBALS['_database']->Execute(
				$update_object_query,
				array($this->id)
			);
			if ($GLOBALS['_database']->ErrorMsg()) {
				$this->error = "SQL Error in Site::Company::update(): ".$GLOBALS['_database']->ErrorMsg();
				return undef;
			}
			
			return $this->details($id);
		}
	}
