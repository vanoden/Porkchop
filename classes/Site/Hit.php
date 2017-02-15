<?php
	namespace Site;

	class Hit {
		public $error;
		public $id;
		public $hit_date;
		public $remote_ip;
		public $secure;
		public $script;
		public $query_string;
		
		function __construct($id = 0) {
			$this->error = '';
			$schema = new Schema();
			if ($schema->error) {
				$this->error = "Failed to initialize schema: ".$schema->error;
			}

			if ($id > 0) {
				$this->details($id);
			}
		}
		function add($parameters = array()) {
			if (! $parameters['session_id']) {
				$this->error = "session_id required for SessionHit::add";
				return null;
			}
			if (isset($_SERVER['HTTPS']) and $_SERVER['HTTPS']) $secure = 1;
			else $secure = 0;

			$insert_hit_query = "
				INSERT
				INTO	session_hits
				(		session_id,
						hit_date,
						remote_ip,
						secure,
						script,
						query_string
				)
				VALUES
				(		?,sysdate(),?,?,?,?
				)
			";
			$GLOBALS['_database']->Execute(
				$insert_hit_query,
				array(
					$parameters['session_id'],
					$_SERVER['REMOTE_ADDR'],
					$secure,
					$_SERVER['SCRIPT_NAME'],
					$_SERVER['REQUEST_URI']
				)
			);
			if ($GLOBALS['_database']->ErrorMsg()) {
				$this->error = "SQL Error in SessionHit::add: ".$GLOBALS['_database']->ErrorMsg();
				return null;
			}
			return 1;
		}
		function find($parameters = array()) {
			$find_objects_query .= "
				SELECT	id
				FROM	session_hits
				WHERE	id = id
			";

			if ($parameters['session_id'])
				$find_objects_query .= "
					AND	session_id = ".$GLOBALS['_database']->qstr($parameters['session_id'],get_magic_quotes_gpc);
			$find_objects_query .= "
				ORDER BY id desc
			";
			if (preg_match('/^\d+$/',$parameters['_limit']))
				$find_objects_query .= "
					limit ".$parameters['_limit'];
			$rs = $GLOBALS['_database']->Execute($find_objects_query);
			if (! $rs) {
				$this->error = "SQL Error in SessionHit::find: ".$GLOBALS['_database']->ErrorMsg();
				return null;
			}
			$hits = array();
			while (list($id) = $rs->FetchRow()) {
				array_push($hits,$this->details($id));
			}
			return $hits;
		}
		function details($id) {
			$get_object_query = "
				SELECT	h.id,
						h.hit_date,
						h.remote_ip,
						h.secure,
						h.script,
						h.query_string
				FROM	session_hits h
				WHERE	h.id = ?
			";

			$rs = $GLOBALS['_database']->Execute(
				$get_object_query,
				array($id)
			);
			if (! $rs)
			{
				$this->error = "SQL Error in SessionHit::details: ".$GLOBALS['_database']->ErrorMsg();
				return null;
			}
			$object = $rs->FetchNextObject(false);

			$this->id = $object->id;
			$this->hit_date = $object->hit_date;
			$this->remote_ip = $object->remote_ip;
			$this->secure = $object->secure;
			$this->script = $object->script;
			$this->query_string = $object->query_string;

			return $object;
			
		}
	}
?>