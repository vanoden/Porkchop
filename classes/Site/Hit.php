<?php
	namespace Site;

	class Hit Extends \BaseModel {

		public $hit_date;
		public $remote_ip;
		public $secure;
		public $script;
		public $query_string;

		function __construct($id = 0) {
			$this->_tableName = "session_hits";
    		parent::__construct($id);
		}

		function add($parameters = []): ?true {
			// Validate required parameters
			if (! $parameters['session_id']) {
				$this->error("session_id required");
				return null;
			}
			if (isset($_SERVER['HTTPS']) and $_SERVER['HTTPS']) $secure = 1;
			else $secure = 0;

			if (empty($parameters['module_id'])) $parameters['module_id'] = 0;

			// Initialize Database Service
			$database = new \Database\Service();

			// Prepare Query to Insert Hit Record
			$insert_hit_query = "
				INSERT
				INTO	session_hits
				(		session_id,
						hit_date,
						remote_ip,
						secure,
						script,
						query_string,
						module_id
				)
				VALUES
				(		?,sysdate(),?,?,?,?,?
				)
			";

			// Add Parameters
			$database->AddParam($parameters['session_id']);
			$database->AddParam($_SERVER['REMOTE_ADDR']);
			$database->AddParam($secure);
			$database->AddParam($_SERVER['SCRIPT_NAME']);
			$database->AddParam($_SERVER['REQUEST_URI']);
			$database->AddParam($parameters['module_id']);

			// Execute Query
			$database->Execute($insert_hit_query);
			if ($database->ErrorMsg()) {
				$this->SQLError($database->ErrorMsg());
				return null;
			}
			return true;
		}
	}
