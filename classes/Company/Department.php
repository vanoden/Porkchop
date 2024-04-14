<?php
	namespace Company;

	class Department Extends \BaseModel {

		public $name;
		public $description;
		
		public function __construct($id = 0) {
			$this->_tableName = 'company_departments';
    		parent::__construct($id);
		}

		public function add($parameters = []) {
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
				$this->SQLError($GLOBALS['_database']->ErrorMsg());
				return false;
			}
			list($this->id) = $GLOBALS['_database']->Insert_ID();

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

		public function update($parameters = []): bool {
			$update_object_query = "
				UPDATE	company_departments
				SET		id = id
			";
			$bind_params = array();
			if (isset($parameters['name'])) {
				$update_object_query .= ",
				name = ?";
				array_push($bind_params,$parameters['name']);
			}
			if (isset($parameters['description'])) {
				$update_object_query .= ",
				description = ?";
				array_push($bind_params,$parameters['description']);
			}
			if (isset($parameters['status'])) {
				$update_object_query .= ",
				status = ?";
				array_push($bind_params,$parameters['status']);
			}
			if (isset($parameters['manager_id'])) {
				$update_object_query .= ",
				manager_id = ?";
				array_push($bind_params,$parameters['manager_id']);
			}

			$update_object_query .= "
				WHERE	id = ?
			";
			array_push($bind_params,$this->id);
			$GLOBALS['_database']->Execute($update_object_query,$bind_params);

			if ($GLOBALS['_database']->ErrorMsg()) {
				$this->SQLError($GLOBALS['_database']->ErrorMsg());
				return null;
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

		public function get($code): bool {
			$get_object_query = "
				SELECT	id
				FROM	company_departments
				WHERE	code = ?
			";
			
			$rs = $GLOBALS['_database']->Execute(
				$get_object_query,
				array($code)
			);
			if (! $rs) {
				$this->SQLError($GLOBALS['_database']->ErrorMsg());
				return null;
			}
			if ($GLOBALS['_database']->rows > 0) {
				list($this->id) = $rs->FetchRow();
				return $this->details();
			}
			else {
				$this->error("Department not found");
				return null;
			}
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
