<?php
	namespace Register;

	class Department extends \BaseModel {
		public $name;
		public $parent_id;
		public $manager_id;

		public function __construct($id = null) {
			# Clear Error Info
			$this->clearError();

			if (is_numeric($id)) {
				$this->id = $id;
				$this->details();
			}
		}
		public function find($parameters = array()) {
			$get_department_query = "
				SELECT	id
				FROM	register_departments
				WHERE	id = id
			";
			$rs = $GLOBALS['_database']->Execute($get_department_query);
			if (! $rs) {
				$this->SQLError($GLOBALS['_database']->ErrorMsg());
				return array();
			}
			$departments = array();
			while (list($id) = $rs->FetchRow()) {
				$details = (object) $this->details($id);
				array_push($departments,$details);
			}
			return $departments;
		}

		public function members() {
			$adminlist = new AdminList();
			$admins = $adminlist->find(array("department" => $this->id));
			if ($adminlist->error())	{
				$this->error($adminlist->error());
				return null;
			}
			return $admins;
		}

		public function details(): bool {
			$get_object_query = "
				SELECT	id,
						name,
						parent_id,
						manager_id
				FROM	register_departments
				WHERE	id = ?
			";
			$rs = $GLOBALS['_database']->Execute($get_object_query,array($this->id));
			if (! $rs) {
				$this->SQLError($GLOBALS['_database']->ErrorMsg());
				return false;
			}
			$object = $rs->FetchNextObject(false);
			$this->id = $object->id;
			$this->name = $object->name;
			$this->parent_id = $object->parent_id;
			$this->manager_id = $object->manager_id;

			return true;
		}
	}
