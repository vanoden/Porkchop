<?php
	namespace Contact;

	class Event Extends \BaseClass {

		public function __construct($id = 0) {
			app_log("Initializing Contact Module",'debug',__FILE__,__LINE__);
			$this->_tableName = 'contact_events';
			parent::__construct($id);
		}

		public function add($parameters) {
			$add_object_query = "
				INSERT
				INTO	contact_events
				(		id,date_event,content,status)
				VALUES
				(		null,sysdate(),?,'NEW')
			";
			$GLOBALS['_database']->Execute(
				$add_object_query,
				array(json_encode($parameters))
			);
			if ($GLOBALS['_database']->ErrorMsg()) {
				$this->SQLError($GLOBALS['_database']->ErrorMsg());
				return NULL;
			}

			$this->id = $GLOBALS['_database']->Insert_ID();
			return $this->update($parameters);
		}
		public function update($parameters): bool {
			$update_object_query = "
				UPDATE	contact_events
				SET		id = id";
			if (isset($parameters["status"]) && in_array($parameters["status"],array("NEW","OPEN","CLOSED"))) {
				$update_object_query .= ",
						status = '".$parameters["status"]."'";
			}
			$update_object_query .= "
				WHERE	id = ?
			";

			$GLOBALS['_database']->Execute(
				$update_object_query,
				array($this->id)
			);
			if ($GLOBALS['_database']->ErrorMsg()) {
				$this->SQLError($GLOBALS['_database']->ErrorMsg());
				return NULL;
			}
			return $this->details();
		}
	}
