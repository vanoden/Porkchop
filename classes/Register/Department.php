<?php
	namespace Register;

	class Department {
		public $id;
		public $name;
		public $error;

		public function __construct() {
			# Clear Error Info
			$this->error = '';
			# Database Initialization
			$schema = new Schema();
			if ($schema->error) {
				$this->error = "Failed to initialize schema: ".$schema->error;
			}
		}
		public function find($parameters = array()) {
			$get_department_query = "
				SELECT	id
				FROM	register_departments
				WHERE	id = id
			";
			$rs = $GLOBALS['_database']->Execute($get_department_query);
			if (! $rs)
			{
				$this->error = "SQL Error in register::department::find: ".$GLOBALS['_database']->ErrorMsg();
				return 0;
			}
			$departments = array();
			while (list($id) = $rs->FetchRow())
			{
				$details = (object) $this->details($id);
				array_push($departments,$details);
			}
			return $departments;
		}
		public function members($id) {
			$_admin = new Admin();
			$admins = $_admin->find(array("department" => $id));
			if ($_admin->error)	{
				$this->error = $_admin->error;
				return 0;
			}
			return $admins;
		}
		
		public function details($id) {
			$get_object_query = "
				SELECT	id,
						name,
						parent_id,
						manager_id
				FROM	register_departments
				WHERE	id = ".$GLOBALS['_database']->qstr($id,get_magic_quotes_gpc());
			$rs = $GLOBALS['_database']->Execute($get_object_query);
			if (! $rs) {
				$this->error = $GLOBALS['_database']->ErrorMsg();
				return 0;
			}
			return (object) $rs->FetchRow();
		}
	}
?>