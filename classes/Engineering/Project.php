<?php
	namespace Engineering;

	class Project {
	
		private $_error;
		public $id;
		public $code;
		public $title;
		public $description;

		public function __construct($id = 0) {
			if (is_numeric($id) and $id > 0) {
				$this->id = $id;
				$this->details();
			}
		}

		public function add($parameters = array()) {

			if (isset($parameters['code']) && strlen($parameters['code'])) {
				if (preg_match('/^[\w\-\.\_\s]+$/',$parameters['code'])) {
					$code = $parameters['code'];
				} else {
					$this->_error = "Invalid code";
					return null;
				}
			} else {
				$code = uniqid();
			}
		
			if (empty($parameters['manager_id'])) {
				$this->_error = "Manager field is required";
				return null;
			}  

			$check_dups = new Project();
			if ($check_dups->get($code)) {
				$this->_error = "Duplicate code";
				return null;
			}

			$add_object_query = "
				INSERT
				INTO	engineering_projects
				(		code,title)
				VALUES
				(		?,?)
			";

			$GLOBALS['_database']->Execute(
				$add_object_query,
				array($code,$_REQUEST['title'])
			);

			if ($GLOBALS['_database']->ErrorMsg()) {
				$this->_error = "SQL Error in Engineering::Project::add(): ".$GLOBALS['_database']->ErrorMsg();
				return null;
			}

			$this->id = $GLOBALS['_database']->Insert_ID();
			return $this->update($parameters);
		}

		public function update($parameters = array()) {
		    
			$update_object_query = "
				UPDATE	engineering_projects
				SET		id = id
			";

            if (empty($parameters['manager_id'])) {
			    $this->_error = "Manager field is required";
			    return null;
            }

			$bind_params = array();
			if (isset($parameters['title'])) {
				$update_object_query .= ",
						title = ?";
				array_push($bind_params,$parameters['title']);
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

			$GLOBALS['_database']->Execute(
				$update_object_query,
				$bind_params
			);

			if ($GLOBALS['_database']->ErrorMsg()) {
				$this->_error = "SQL Error in Engineering::Projects::update(): ".$GLOBALS['_database']->ErrorMsg();
				return null;
			}

			return $this->details();
		}

		public function get($code) {
		
			$get_object_query = "
				SELECT	id
				FROM	engineering_projects
				WHERE	code = ?
			";

			$rs = $GLOBALS['_database']->Execute(
				$get_object_query,
				array($code)
			);

			if (! $rs) {
				$this->_error = "SQL Error in Engineering::Project::get(): ".$GLOBALS['_database']->ErrorMsg();
				return false;
			}

			list($id) = $rs->FetchRow();
			$this->id = $id;
            app_log("Found project $id");
			return $this->details();
		}

		public function details() {

			$get_object_query = "
				SELECT	*
				FROM	engineering_projects
				WHERE	id = ?
			";
            app_log("Getting details for project ".$this->id);

			$rs = $GLOBALS['_database']->Execute(
				$get_object_query,
				array($this->id)
			);

			if (! $rs) {
				$this->_error = "SQL Error in Engineering::Project::details(): ".$GLOBALS['_database']->ErrorMsg();
				return null;
			};

			if ($rs->RecordCount()) {
				$object = $rs->FetchNextObject(false);
				$this->title = $object->title;
				$this->code = $object->code;
				$this->description = $object->description;
				$this->manager = new \Register\Customer($object->manager_id);
				$this->status = $object->status;
				return $object;
			} else {
				return null;
			}
		}

		public function error() {
			return $this->_error;
		}
	}
