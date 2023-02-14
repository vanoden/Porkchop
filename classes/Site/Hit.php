<?php
	namespace Site;

	class Hit Extends \BaseClass {

		public $hit_date;
		public $remote_ip;
		public $secure;
		public $script;
		public $query_string;
		
		function __construct($id = 0) {
			$this->_tableName = "session_hits";
    		parent::__construct($id);
		}
		function add($parameters = []) {
			if (! $parameters['session_id']) {
				$this->error("session_id required");
				return null;
			}
			if (isset($_SERVER['HTTPS']) and $_SERVER['HTTPS']) $secure = 1;
			else $secure = 0;

			if (empty($parameters['module_id'])) $parameters['module_id'] = 0;

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
			$GLOBALS['_database']->Execute(
				$insert_hit_query,
				array(
					$parameters['session_id'],
					$_SERVER['REMOTE_ADDR'],
					$secure,
					$_SERVER['SCRIPT_NAME'],
					$_SERVER['REQUEST_URI'],
					$parameters['module_id']
				)
			);
			if ($GLOBALS['_database']->ErrorMsg()) {
				$this->SQLError($GLOBALS['_database']->ErrorMsg());
				return null;
			}
			return 1;
		}
	}
