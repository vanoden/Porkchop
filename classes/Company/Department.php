<?php
	namespace Company;

	class Department {
		public $id;
		public $error;
		public $name;
		public $description;
		
		public function __construct($id = 0) {
			if ($id > 0) {
				$this->id = $id;
				$this->details();
			}
		}

		public function add($parameters = array()) {
			if (! isset($parameters['code'])) {
				$parameters['code'] = uniqid();
			}

			$add_object_query = "
				INSERT
				INTO	company_departments
				(		code)
				VALUES
				(		?)
			";
			$GLOBALS['_database']->Execute(
				$add_object_query,
				array($parameters['code'])
			);
			if ($GLOBALS['_database']->ErrorMsg()) {
				$this->error = "SQL Error adding department in Company::Department::add(): ".$GLOBALS['_database']->ErrorMsg();
				return null;
			}
			list($this->id) = $GLOBALS['_database']->Insert_ID();
			return $this->update($parameters);
		}

		public function update($parameters = array()) {
			$update_object_query = "
				UPDATE	company_departments
				SET		id = id
			";
			if (isset($parameters['name'])) {
				$update_object_query .= ",
				name = ".$GLOBALS['_database']->qstr($parameters['name'],get_magic_quotes_gpc());
			}
			if (isset($parameters['description'])) {
				$update_object_query .= ",
				description = ".$GLOBALS['_database']->qstr($parameters['description'],get_magic_quotes_gpc());
			}
			if (isset($parameters['status'])) {
				$update_object_query .= ",
				status = ".$GLOBALS['_database']->qstr($parameters['status'],get_magic_quotes_gpc());
			}
			if (isset($parameters['manager_id'])) {
				$update_object_query .= ",
				manager_id = ".$GLOBALS['_database']->qstr($parameters['manager_id'],get_magic_quotes_gpc());
			}

			$update_object_query .= "
				WHERE	id = ?
			";
			
			$GLOBALS['_database']->Execute(
				$update_object_query,
				array($this->id)
			);

			if ($GLOBALS['_database']->ErrorMsg()) {
				$this->error = "SQL Error updating department in Company::Department::update(): ".$GLOBALS['_database']->ErrorMsg();
				return null;
			}
			
			return $this->details();
		}

		public function get($code) {
			$get_object_query = "
				SELECT	id
				FROM	company_departments
				WHERE	code = ?
			";
			
			$rs = $GLOBALS['_database']->Execute(
				$get_object_query,
				array($parameters['code'])
			);
			if (! $rs) {
				$this->error = "SQL Error in Company::Department::get(): ".$GLOBALS['_database']->ErrorMsg();
				return null;
			}
			if ($GLOBALS['_database']->rows > 0) {
				list($this->id) = $rs->FetchRow();
				return $this->details();
			}
			else {
				$this->error = "Department not found";
				return null;
			}
		}

		public function details() {
			$get_object_query = "
				SELECT	*
				FROM	company_departments
				WHERE	id = ?
			";
			$rs = $GLOBALS['_database']->Execute(
				$get_object_query,
				array($this->id)
			);
			if (! $rs) {
				$this->error = "SQL Error in Company::Department::details(): ".$GLOBALS['_database']->ErrorMsg();
				return null;
			}
			$object = $rs->FetchNextObject(false);
			$this->id = $object->id;
			$this->code = $object->code;
			$this->name = $object->name;
			$this->description = $object->description;
			
			return $object;
		}

		public function add_member($id) {
			$add_member_query = "
				INSERT
				INTO	company_department_users
				(		department_id,user_id)
				VALUES
				(		?,?)
			";
		}

		public function drop_member($id) {
			
		}

		public function members() {
			
		}
	}
