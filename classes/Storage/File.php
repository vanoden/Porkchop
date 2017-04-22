<?
	namespace Storage;

	class File {
		private $_repository_id;
		public $code;
		public $id;
		public $error;
		public $uri;
		public $read_protect;
		public $write_protect;

		public function __construct($id = 0) {
			if ($id > 0) {
				$this->id = $id;
				$this->details();
			}
		}

		public function add($parameters) {
			if (! isset($parameters['code']) || ! strlen($parameters['code'])) {
				$parameters['code'] = uniqid();
			}
			if (! preg_match('/^[\w\-\.\_]+$/',$parameters['code'])) {
				$this->error = "Invalid code '".$parameters['code']."'";
				return false;
			}
			$this->code = $code;
			if (! $this->_valid_type($parameters['mime_type'])) {
				$this->error = "Invalid mime_type '".$parameters['mime_type']."'";
				return false;
			}

			$add_object_query = "
				INSERT
				INTO	storage_files
				(		code,repository_id,name,mime_type,size,date_created,user_id)
				VALUES
				(		?,?,?,?,?,sysdate(),?)
			";
			$GLOBALS['_database']->Execute(
				$add_object_query,
				array(
					$parameters['code'],
					$parameters['repository_id'],
					$parameters['name'],
					$parameters['mime_type'],
					$parameters['size'],
					$GLOBALS['_SESSION_']->customer->id
				)
			);
			if ($GLOBALS['_database']->ErrorMsg()) {
				$this->error = "SQL Error in Storage::File::add(): ".$GLOBALS['_database']->ErrorMsg();
				return false;
			}
			$this->id = $GLOBALS['_database']->Insert_ID();
			return $this->details();
		}

		public function get($code) {
			$get_object_query = "
				SELECT	id
				FROM	storage_files
				WHERE	code = ?
			";
			$rs = $GLOBALS['_database']->Execute(
				$get_object_query,
				array($code)
			);
			if (! $rs) {
				$this->error = "SQL Error in Storage::File::get(): ".$GLOBALS['_database']->ErrorMsg();
				return false;
			}
			list($this->id) = $rs->FetchRow();
			return $this->details();
		}
		public function details() {
			$get_object_query = "
				SELECT	*
				FROM	storage_files
				WHERE	id = ?
			";
			$rs = $GLOBALS['_database']->Execute(
				$get_object_query,
				array($this->id)
			);
			if (! $rs) {
				$this->error = "SQL Error in Storage::File::details(): ".$GLOBALS['_database']->ErrorMsg();
				return false;
			}
			$object = $rs->FetchNextObject(false);
			$this->code = $object->code;
			$this->name = $object->name;
			$this->mime_type = $object->mime_type;
			$this->size = $object->size;
			$this->user = new \Register\Customer($object->user_id);
			$factory = new RepositoryFactory();
			$this->repository = $factory->load($object->repository_id);
			if ($this->repository->endpoint) $this->uri = $this->repository->endpoint."/".$this->name;
			$this->read_protect = $object->read_protect;
			$this->write_protect = $object->write_protect;

			return true;
		}

		public function delete() {
			if (! $this->id) {
				$this->error = "Must select file first";
				return false;
			}
			$delete_object_query = "
				DELETE
				FROM	storage_files
				WHERE	id = ?
			";
			$GLOBALS['_database']->Execute(
				$delete_object_query,
				array($this->id)
			);
			if ($GLOBALS['_database']->ErrorMsg()) {
				$this->error = "SQL Error in Storage::File::delete(): ".$GLOBALS['_database']->ErrorMsg();
				return false;
			}
			return true;
		}

		public function name($name = '') {
			if (strlen($name)) {
				if (! preg_match('/^[\w\-\.\_\s]+$/',$name)) {
					$this->error = "Invalid File Name";
					return false;
				}
				else {
					$this->_name = $name;
				}
			}
			return $this->_name;
		}

		public function repository_id($id = 0) {
			if ($id > 0) {
				# Get Repository
				$repository = new Repository($id);
				if (! $repository->id) {
					$this->error = "Repository not found";
					return false;
				}
				else {
					$this->_repository_id = $id;
				}
			}
			return $this->_repository_id;
		}

		private function _valid_type($name) {
			if (preg_match('/^(image|application|text)\/(png|jpg|tif|plain|html|csv|cs|js|xml|json)$/',$name)) {
				return true;
			}
			return false;
		}

		public function grant($id,$type) {
			if ($type == 'read') {
				if ($this->read_protect == 'NONE') return true;
				else if ($this->read_protect == 'AUTH') return true;
				else if ($this->read_protect == 'ROLE') {
					$role = new \Register\Role($id);
					if ($role->id) {
						$update_privilege_query = "
							INSERT
							INTO	storage_file_roles
							(		read,write)
							VALUES
							(		1,0)
							WHERE	file_id = ?
							AND		role_id = ?
							ON DUPLICATE KEY UPDATE
									read = 1
						";
						$GLOBALS['_database']->Execute(
							$update_privilege_query,
							array($this->id,$role->id)
						);
						if ($GLOBALS['_database']->ErrorMsg()) {
							$this->error = "SQL Error in Storage::File::grant(): ".$GLOBALS['_database']->ErrorMsg();
							return false;
						}
					}
					else {
						$this->error = "Role not found";
						return false;
					}
				}
				return false;
			}
			else if ($type == 'write') {
				if ($this->write_protect == 'NONE') return true;
				else if ($this->write_protect == 'AUTH') return true;
				else if ($this->write_protect == 'ROLE') {
					$role = new \Register\Role($id);
					if ($role->id) {
						$update_privilege_query = "
							INSERT
							INTO	storage_file_roles
							(		read,write)
							VALUES
							(		1,1)
							WHERE	file_id = ?
							AND		role_id = ?
							ON DUPLICATE KEY UPDATE
									write = 1
						";
						$GLOBALS['_database']->Execute(
							$update_privilege_query,
							array($this->id,$role->id)
						);
						if ($GLOBALS['_database']->ErrorMsg()) {
							$this->error = "SQL Error in Storage::File::grant(): ".$GLOBALS['_database']->ErrorMsg();
							return false;
						}
					}
					else {
						$this->error = "Role not found";
						return false;
					}
				}
				return false;
			}
		}

		public function revoke($id,$type) {
			if ($type == 'read') {
				if ($this->read_protect == 'NONE') {
					$this->error = "File is globally readable";
					return false;
				}
				else if ($this->read_protect == 'AUTH') {
					$this->error = "File is readable by all authenticated users";
					return false;
				}
				else if ($this->read_protect == 'ROLE') {
					$role = new \Register\Role($id);
					if ($role->id) {
						$update_privilege_query = "
							UPDATE	storage_file_roles
							SET		read = 0, write = 0
							WHERE	file_id = ?
							AND		role_id = ?
						";
						$GLOBALS['_database']->Execute(
							$update_privilege_query,
							array($this->id,$role->id)
						);
						if ($GLOBALS['_database']->ErrorMsg()) {
							$this->error = "SQL Error in Storage::File::revoke(): ".$GLOBALS['_database']->ErrorMsg();
							return false;
						}
					}
					else {
						$this->error = "Role not found";
						return false;
					}
				}
				return false;
			}
			else if ($type == 'write') {
				if ($this->write_protect == 'NONE') {
					$this->error = "File is globally writable";
					return false;
				}
				else if ($this->write_protect == 'AUTH') {
					$this->error = "File is writable by all authenticated users";
					return false;
				}
				else if ($this->write_protect == 'ROLE') {
					$role = new \Register\Role($id);
					if ($role->id) {
						$update_privilege_query = "
							UPDATE	storage_file_roles
							SET		write = 0
							WHERE	file_id = ?
							AND		role_id = ?
						";
						$GLOBALS['_database']->Execute(
							$update_privilege_query,
							array($this->id,$role->id)
						);
						if ($GLOBALS['_database']->ErrorMsg()) {
							$this->error = "SQL Error in Storage::File::revoke(): ".$GLOBALS['_database']->ErrorMsg();
							return false;
						}
					}
					else {
						$this->error = "Role not found";
						return false;
					}
				}
				return false;
			}
		}

		public function readable($user_id) {
			# World Readable
			if ($this->read_protect == 'NONE') return true;

			# Owner Can Always Access
			if ($this->user->id == $GLOBALS['_SESSION_']->customer->id) return true;

			# Any Authenticated Visitor
			if ($this->read_protect == 'AUTH' && $GLOBALS['_SESSION_']->customer->id > 0) return true;

			# Visitor in Specified Role
			if ($this->read_protect == 'ROLE') {
				$get_privileges_query = "
					SELECT	read
					FROM	storage_file_roles sfr,
							register_user_roles rur
					WHERE	sfr.file_id = ?
					AND		sfr.role_id = rur.role_id
					AND		rur.user_id = ?
				";
				$rs = $GLOBALS['_database']->Execute(
					$get_privileges_query,
					array($this->id,$user_id)
				);
				if (! $rs) {
					$this->error = "SQL Error in Storage::File::readable(): ".$GLOBALS['_database']->ErrorMsg();
					return false;
				};
				list($ok) = $rs->fetchrow();
				if ($ok > 0) return true;
			}
			return false;
		}
	}
?>