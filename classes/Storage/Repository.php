<?php
	namespace Storage;

	class Repository Extends \BaseClass {
	
		public $error;
		public $name;
		public $type;
		public $id;
		public $code;
		protected $default_privileges;
		protected $override_privileges;

		public function __construct($id = 0) {
			$this->_init($id);
		}
		protected function _init($id = 0) {
			app_log("Loading repository $id",'info');
			if ($id > 0) {
				$this->id = $id;
				$this->details();
			}
		}

		public function add($parameters = array()) {
		
			if (! isset($parameters['code']) || ! strlen($parameters['code'])) $parameters['code'] = uniqid();
			if (isset($parameters['type'])) $this->type = $parameters['type'];
			
			if (! $this->_valid_code($parameters['code'])) {
				$this->error = "Invalid code";
				return false;
			}
			
			if (! isset($parameters['status']) || ! strlen($parameters['status'])) {
				$parameters['status'] = 'NEW';
			} else if (! $this->_valid_status($parameters['status'])) {
				$this->error = "Invalid status";
				return false;
			}
			
			if (! $this->_valid_name($parameters['name'])) {
				$this->error = "Invalid name";
				return false;
			}
	
			$add_object_query = "
				INSERT
				INTO	storage_repositories
				(		code,name,type,status)
				VALUES
				(		?,?,?,?)
			";
			$GLOBALS['_database']->Execute(
				$add_object_query,
				array(
					$parameters['code'],
					$parameters['name'],
					$this->type,
					$parameters['status']
				)
			);
			
			if ($GLOBALS['_database']->ErrorMsg()) {
				$this->error = "SQL Error in Storage::Repository::add(): ".$GLOBALS['_database']->ErrorMsg();
				return false;
			}
			
			$this->id = $GLOBALS['_database']->Insert_ID();
			app_log("Repo ".$this->id." created, updating");
			return $this->update($parameters);
		}

		public function update($parameters = array()) {
		
			$update_object_query = "
				UPDATE	storage_repositories
				SET		id = id
			";
			$bind_params = array();

			if (isset($parameters['name'])) {
				if ($this->validName($parameters['name'])) {
					$update_object_query .= ",
					name = ?";
					array_push($bind_params,$parameters['name']);
				} else {
					$this->error = "Invalid name '".$parameters['name']."'";
					return false;
				}
			}
			
			if (isset($parameters['status'])) {
				if ($this->validStatus($parameters['status'])) {
					$update_object_query .= ",
					status = ?";
					array_push($bind_params,$parameters['status']);
				} else {
					$this->error = "Invalid status";
					return false;
				}
			}
			
			$update_object_query .= "
				WHERE	id = ?
			";
			
			array_push($bind_params,$this->id);
			query_log($update_object_query);
			$GLOBALS['_database']->Execute($update_object_query,$bind_params);

			if ($GLOBALS['_database']->ErrorMsg()) {
				$this->error = "SQL Error in Storage::Repository::update(): ".$GLOBALS['_database']->ErrorMsg();
				return false;
			}
			if (isset($parameters['path'])) $this->_setMetadata('path',$parameters['path']);
			app_log("Repo ".$this->id." updated, getting details");
			return $this->details();
		}
	
		public function get($code) {
		
			$get_object_query = "
				SELECT	id
				FROM	storage_repositories
				WHERE	code = ?
			";
			
			$rs = $GLOBALS['_database']->Execute(
				$get_object_query,
				array($code)
			);
			
			if (! $rs) {
				$this->error = "SQL Error in Storage::Repository::get(): ".$GLOBALS['_database']->ErrorMsg();
				return false;
			}
			
			list($this->id) = $rs->FetchRow();
			if (! $this->id) {
				$this->error = "Repository not found";
				return false;
			}
			
			return $this->details();
		}
		
		public function find($name) {
		
			$get_object_query = "
				SELECT	id
				FROM	storage_repositories
				WHERE	name = ?
			";
			
			$rs = $GLOBALS['_database']->Execute(
				$get_object_query,
				array(trim($name))
			);
			
			if (! $rs) {
				$this->error = "SQL Error in Storage::Repository::find(): ".$GLOBALS['_database']->ErrorMsg();
				return false;
			}
			list($this->id) = $rs->FetchRow();
			if (! $this->id) {
				$this->error = "Repository not found";
				return false;
			}
			
			return $this->details();
		}

		public function details() {
		
			$get_object_query = "
				SELECT	*
				FROM	storage_repositories
				WHERE	id = ?
			";
			
			$rs = $GLOBALS['_database']->Execute(
				$get_object_query,
				array($this->id)
			);
			
			if (! $rs) {
				$this->error = "SQL Error in Storage::Repository::details(): ".$GLOBALS['_database']->ErrorMsg();
				return false;
			}

			$object = $rs->FetchNextObject(false);
			$this->name = $object->name;
			$this->type = $object->type;
			$this->code = $object->code;
			$this->status = $object->status;
			$default_privileges_json = $object->default_privileges;
			$this->default_privileges = json_decode($default_privileges_json,true);
			$override_privileges_json = $object->override_privileges;
			$this->override_privileges = json_decode($override_privileges_json,true);
			
			$get_object_query = "
				SELECT	*
				FROM	`storage_repository_metadata`
				WHERE	`repository_id` = ?
			";
			
			$rs = $GLOBALS['_database']->Execute(
				$get_object_query,
				array($this->id)
			);
			
			if (! $rs) {
				$this->error = "SQL Error in Storage::Repository::details(): ".$GLOBALS['_database']->ErrorMsg();
				return false;
			}
			
			while ($row = $rs->fetchRow(false)) $this->$row['key'] = $row['value'];
			return true;
		}
		
		public function files($path = "/") {
			$filelist = new FileList();
			return $filelist->find(array('repository_id' => $this->id,'path' => $path));
		}
		
		public function directories($path = "/") {
			$directorylist = new DirectoryList();
			return $directorylist->find(array('repository_id' => $this->id,'path' => $path));
		}

        public function _updateMetadata($key, $value) {
		
            $update_object_query = "
				UPDATE	`storage_repository_metadata`
				SET		`repository_id` = `repository_id`
			";
			$bind_params = array();
			if (!empty($key)) {
				$update_object_query .= ",
				`value` = ?";
				array_push($bind_params, $value);
			}
			$update_object_query .= "
				WHERE	`repository_id` = ? AND `key` = ?
			";            
			array_push($bind_params, $this->id);
			array_push($bind_params, $key);
			query_log($update_object_query);
			$GLOBALS['_database']->Execute($update_object_query,$bind_params);
			if ($GLOBALS['_database']->ErrorMsg()) {
				$this->error = "SQL Error in Storage::Repository::_updateMetadata(): ".$GLOBALS['_database']->ErrorMsg();
				return false;
			}
            return true;
		}
		
		public function _setMetadata($key,$value) {
		
			$set_object_query = "
				INSERT
				INTO	storage_repository_metadata
				(repository_id,`key`,value)
				VALUES	(?,?,?)
				ON DUPLICATE KEY UPDATE
				value = ?
			";
			
			$GLOBALS['_database']->Execute(
				$set_object_query,
				array($this->id,$key,$value,$value)
			);
			
			if ($GLOBALS['_database']->ErrorMsg()) {
				$this->error = "SQL Error in Storage::Repository::setMetadata: ".$GLOBALS['_database']->ErrorMsg();
				return false;
			}
			return true;
		}
		
		public function _metadata($key) {
			$get_value_query = "
				SELECT	value
				FROM	storage_repository_metadata
				WHERE	repository_id = ?
				AND		`key` = ?
			";
			$rs = $GLOBALS['_database']->Execute(
				$get_value_query,
				array($this->id,$key)
			);
			if (! $rs) {
				$this->error = "SQL Error in Storage::Repository::metadata(): ".$GLOBALS['_database']->ErrorMsg();
				return null;
			}
			list($value) = $rs->FetchRow();
			return $value;
		}

		public function getMetadata($key) {
			return $this->_metadata($key);
		}

		public function getFileFromPath($path) {
			$file = new \Storage\File();
			return $file->fromPath($this->id,$path);
		}

		public function default_privileges() {
			return json_decode($this->default_privileges_json,true);
		}

		public function override_privileges() {
			return json_decode($this->override_privileges_json,true);
		}
		
		public function validCode($string) {
			if (preg_match('/^\w[\w\-\_\.]*$/',$string)) return true;
			return false;
		}
		public function validName($string) {
			if (preg_match('/^\w[\w\-\_\.\s]*$/',$string)) return true;
			return false;
		}
		public function validStatus($string) {
			if (preg_match('/^(NEW|ACTIVE|DISABLED)$/i',$string)) return true;
			return false;
		}
		public function validType($string) {
			if (preg_match('/^(Local|S3)$/',$string)) return true;
			return false;
		}
		public function validPath($string) {
			// Only certain instances require path
			if (empty($string)) return true;
			else return false;
		}
		public function validAccessKey($string) {
			// Only certain instances require accessKey
			if (empty($string)) return true;
			else return false;
		}
		public function validSecretKey($string) {
			// Only certain instances require accessKey
			if (empty($string)) return true;
			else return false;
		}
		public function validBucket($string) {
			// Only certain instances require bucket
			if (empty($string)) return true;
			else return false;
		}
		public function validRegion($string) {
			// Only certain instances require bucket
			if (empty($string)) return true;
			else return false;
		}
	}
