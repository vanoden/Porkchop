<?php
	namespace Engineering;

	class Release {
		private $_error;
		public $id;
		public $title;
		public $description;
		public $status;
		public $date_scheduled;
		public $date_released;
		public $package_version_id;

		public function __construct($id = 0) {
			if (is_numeric($id) && $id > 0) {
				$this->id = $id;
				$this->details();
			}
		}

		public function add($parameters = array()) {
			if (isset($parameters['code']) && strlen($parameters['code'])) {
				if (preg_match('/^[\w\-\.\_\s]+$/',$parameters['code'])) {
					$code = $parameters['code'];
				}
				else {
					$this->_error = "Invalid code";
					return false;
				}
			}
			else {
				$code = uniqid();
			}

			$check_dups = new Release();
			if ($check_dups->get($code)) {
				$this->_error = "Duplicate code";
				return false;
			}

			$add_object_query = "
				INSERT
				INTO	engineering_releases
				(		code,title)
				VALUES
				(		?,?)
			";

			$rs = executeSQLByParams($add_object_query,array($code,$title));
			if ($GLOBALS['_database']->ErrorMsg()) {
				$this->_error = "SQL Error in Engineering::Release::add(): ".$GLOBALS['_database']->ErrorMsg();
				return false;
			}

			$this->id = $GLOBALS['_database']->Insert_ID();
			return $this->update($parameters);
		}

		public function update($parameters = array()) {
			// Bust Cache
			$cache_key = "engineering.release[".$this->id."]";
			$cache_item = new \Cache\Item($GLOBALS['_CACHE_'],$cache_key);
			$cache_item->delete();

			$bind_params = array();

			$update_object_query = "
				UPDATE	engineering_releases
				SET		id = id
			";

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

			if (get_mysql_date($parameters['date_scheduled'])) {
				$update_object_query .= ",
						date_scheduled = ?";
				array_push($bind_params,get_mysql_date($parameters['date_scheduled']));
			}

			if (get_mysql_date($parameters['date_released'])) {
				$update_object_query .= ",
						date_released = ?";
				array_push($bind_params,get_mysql_date($parameters['date_released']));
			}

			if (isset($parameters['package_version_id'])) {
				$update_object_query .= ",
						package_version_id = ?";
				array_push($bind_params,$parameters['package_version_id']);
			}

			if (isset($parameters['status'])) {
				if ($this->_valid_status($parameters['status'])) {
					$update_object_query .= ",
						status = ?";
					array_push($bind_params,$parameters['status']);
				}
				else {
					$this->_error = "Invalid Status";
					return false;
				}
			}

			$update_object_query .= "
				WHERE	id = ?
			";

			array_push($bind_params,$this->id);
            $rs = executeSQLByParams($update_object_query,$bind_params);

			if ($GLOBALS['_database']->ErrorMsg()) {
				$this->_error = "SQL Error in Engineering::Releases::update(): ".$GLOBALS['_database']->ErrorMsg();
				return false;
			}

			return $this->details();
		}

		public function get($code) {
			$get_object_query = "
				SELECT	id
				FROM	engineering_releases
				WHERE	code = ?
			";
			
			$rs = executeSQLByParams($get_object_query,array($code));			
			if (! $rs) {
				$this->_error = "SQL Error in Engineering::Release::get(): ".$GLOBALS['_database']->ErrorMsg();
				return false;
			}
			list($id) = $rs->FetchRow();
			if ($id) {
				$this->id = $id;
				return $this->details();
			}
			else {
				$this->_error = "Release not found";
				return false;
			}
		}
		public function details() {
			$cache_key = "engineering.release[".$this->id."]";
			$cache = new \Cache\Item($GLOBALS['_CACHE_'],$cache_key);
			if ($cache->error) {
				app_log("Error in cache mechanism: ".$cache->error,'error',__FILE__,__LINE__);
			}

			# Cached Object, Yay!
			if ($object = $cache->get()) {
				app_log($cache_key." found in cache",'trace');
				$this->_cached = true;
			}
			else {
				$get_object_query = "
					SELECT	*
					FROM	engineering_releases
					WHERE	id = ?
				";

            	$rs = executeSQLByParams($get_object_query,array($this->id));
				if (! $rs) {
					$this->_error = "SQL Error in Engineering::Release::details(): ".$GLOBALS['_database']->ErrorMsg();
					return false;
				};
	
				$object = $rs->FetchNextObject(false);
				$this->id = $object->id;
				$this->_cached = false;
			}

			$this->package_version_id = $object->package_version_id;
			$this->title = $object->title;
			$this->code = $object->code;
			$this->description = $object->description;
			$this->status = $object->status;
			$this->date_released = $object->date_released;
			$this->date_scheduled = $object->date_scheduled;

			if (! $this->_cached) {
				// Cache Object
				app_log("Setting cache key ".$cache_key,'debug',__FILE__,__LINE__);
				if ($object->id) $result = $cache->set($object);
				app_log("Cache result: ".$result,'trace',__FILE__,__LINE__);	
			}

			return true;
		}

		public function packageVersion() {
			return new \Package\Version($this->package_version_id);
		}

		public function released() {
			$update_object_query = "
				UPDATE	engineering_releases
				SET		status = 'RELEASED',
						date_released = sysdate()
				WHERE	id = ?
			";

            $rs = executeSQLByParams($update_object_query,array($this->id));
			if ($GLOBALS['_database']->ErrorMsg()) {
				$this->_error = "SQL Error in Engineering::Release::released(): ".$GLOBALS['_database']->ErrorMsg();
				return false;
			}

			return $this->details();
		}

		public function addTask($task_id) {
			$task = new Task($task_id);

			if ($task->id) {
				if ($task->update(array("release_id" => $this->id))) return true;
				else {
					$this->_error = $task->error();
					return false;
				}
			}
			else {
				$this->_error = "Task not found";
				return false;
			}
		}

		public function error() {
			return $this->_error;
		}
		private function _valid_status($string) {
			if (preg_match('/^(new|testing|released)$/i',$string)) return true;
			else return false;
		}
	}
