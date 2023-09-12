<?php
	namespace Storage;

	class File Extends \BaseModel {
		private $_repository_id;
		public $code;
		public $display_name;
		public $description;
		public $date_created;
		public $success;
		public $uri;
		public $read_protect;
		public $write_protect;
		public $mime_type;
		public $size;
		public $timestamp;
		public $name;
		private $user_id;
		private $path;
		private $original_name;
		private $access_privilege_json;

		public function __construct($id = 0) {
			$this->_tableName = 'storage_files';
			parent::__construct($id);
		}

		public function add($parameters = []) {
			if (! isset($parameters['code']) || ! strlen($parameters['code'])) $parameters['code'] = uniqid();
			
			if (! preg_match('/^[\w\-\.\_]+$/',$parameters['code'])) {
				$this->error("Invalid code '".$parameters['code']."'");
				return false;
			}
			
			$this->code = $parameters['code'];
			if (! $this->_valid_type($parameters['mime_type'])) {
				$this->error("Invalid mime_type '".$parameters['mime_type']."'");
				return false;
			}

			if (! $parameters['original_name']) $parameters['original_name'] = $parameters['name'];
			if (! $parameters['path']) $parameters['path'] = '/';

			$add_object_query = "
				INSERT
				INTO	storage_files
				(		code,repository_id,name,mime_type,size,date_created,user_id,path)
				VALUES
				(		?,?,?,?,?,sysdate(),?,?)
			";
			
			$GLOBALS['_database']->Execute(
				$add_object_query,
				array(
					$parameters['code'],
					$parameters['repository_id'],
					$parameters['name'],
					$parameters['mime_type'],
					$parameters['size'],
					$GLOBALS['_SESSION_']->customer->id,
					$parameters['path']
				)
			);
			
			if ($GLOBALS['_database']->ErrorMsg()) {
				$this->SQLError($GLOBALS['_database']->ErrorMsg());
				return false;
			}
			
			$this->id = $GLOBALS['_database']->Insert_ID();
			app_log("File '".$this->id."' uploaded");
			return $this->update($parameters);
		}
		
		public function update($parameters = []): bool {
		
			$update_object_query = "
				UPDATE	storage_files
				SET		id = id";

			$bind_params = array();

			if (isset($parameters['display_name'])) {
				$update_object_query .= ",
						display_name = ?";
				array_push($bind_params,$parameters['display_name']);
			}
			
			if (isset($parameters['description'])) {
				$update_object_query .= ",
						description = ?";
				array_push($bind_params,$parameters['description']);
			}
			
			if (isset($parameters['path'])) {
				$update_object_query .= ",
						path = ?";
				array_push($bind_params,$parameters['path']);
			}

			if (isset($parameters['access_privileges'])) {
				$update_object_query .= ",
						access_privileges = ?";
				array_push($bind_params,$parameters['access_privileges']);
			}

			query_log($update_object_query);
			$update_object_query .= "
				WHERE	id = ?
			";
			array_push($bind_params,$this->id);

			$GLOBALS['_database']->Execute($update_object_query,$bind_params);

			if ($GLOBALS['_database']->ErrorMsg()) {
				$this->SQLError($GLOBALS['_database']->ErrorMsg());
				return false;
			}
			return $this->details();
		}

		public function details(): bool {
		
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
				$this->SQLError($GLOBALS['_database']->ErrorMsg());
				return false;
			}
			
			$object = $rs->FetchNextObject(false);
			if ($object->id) {
				$this->code = $object->code;
				$this->name = $object->name;
				$this->path = $object->path;
				$this->display_name = $object->display_name;
				$this->description = $object->description;
				$this->mime_type = $object->mime_type;
				$this->size = $object->size;
				$this->user_id = $object->user_id;
				$this->date_created = $object->date_created;
				$this->timestamp = strtotime($this->timestamp);
				$this->_repository_id = $object->repository_id;
				$factory = new RepositoryFactory();
				$this->read_protect = $object->read_protect;
				$this->write_protect = $object->write_protect;
				$this->access_privilege_json = $object->access_privileges;
			}
			else {
				$this->id = null;
				$this->code = null;
			}
			
			return true;
		}

		public function owner() {
			return new \Register\Customer($this->user_id);
		}

		public function name($name = '') {
			if (strlen($name)) {
				if (! preg_match('/^[\w\-\.\_\s]+$/',$name)) {
					$this->error("Invalid File Name");
					return false;
				} else {
					$this->name = $name;
				}
			}
			return $this->name;
		}

		public function uri() {
			return $this->repository()->endpoint."/".$this->name;
		}

		public function path() {
			return $this->path;
		}

		public function fromPath($repository_id,$path) {
			if (preg_match('/^(.*)\/([^\/]+)/',$path,$matches)) {
				$path = $matches[1];
				$file = $matches[2];
			}
			elseif (!empty($path)) {
				$file = $path;
				$path = "";
			}
			else {
				$this->error("Path required");
				return null;
			}
			$get_file_query = "
				SELECT	id
				FROM	storage_files
				WHERE	repository_id = ?
				AND		name = ?
			";
			$bind_params = array($repository_id,$file);
			if (!empty($path)) {
				$get_file_query .= "
				AND		path = ?";
				array_push($bind_params,$path);
			}

			query_log($get_file_query,$bind_params);
			$rs = $GLOBALS['_database']->Execute($get_file_query,$bind_params);
			if (! $rs) {
				$this->SQLError($GLOBALS['_database']->ErrorMsg());
				return null;
			}
			list($file_id) = $rs->FetchRow();
			if (!empty($file_id)) {
				return new \Storage\File($file_id);
			}
			else {
				$this->error("File not found");
				return null;
			}
		}

		public function repository_id($id = 0) {
			if ($id > 0) {
			
				// Get Repository 
				$repository = new Repository($id);
				if (! $repository->id) {
					$this->error("Repository not found");
					return false;
				} else {
					$this->_repository_id = $id;
				}
			}
			return $this->_repository_id;
		}

		public function repository() {
			$factory = new \Storage\RepositoryFactory();
			return $factory->load($this->_repository_id);
		}

		private function _valid_type($name) {
			if (preg_match('/^(image|application|text|video)\/(png|jpg|jpeg|tif|tiff|plain|html|csv|cs|js|xml|json|gzip|tar\+gzip|pdf|octet\-stream|mp4)$/',$name)) return true;
			return false;
		}

		public function grant($level,$id,$type) {
			$level = substr($level,0,1);
			if (!in_array($level,array('a','o','u','r'))) {
				$this->error("Invalid level");
				return false;
			}
			$type = substr($type,0,1);
			if (!in_array($type,array('r','w'))) {
				$this->error("Invalid type $type");
				return false;
			}

			// Get Existing Privileges
			$privileges = $this->getPrivileges();
			if ($level == 'a') {
				if (!preg_match("/$type/",$privileges->a)) $privileges->a .= $type;
			}
			else {
				if (!preg_match("/$type/",$privileges->$level->$id)) $privileges->$level->$id .= $type;
			}
			return $this->update(array('access_privileges' => json_encode($privileges)));
		}

		public function revoke($level,$id,$type) {
			$level = substr($level,0,1);
			if (!in_array($level,array('a','o','u','r'))) {
				$this->error("Invalid level");
				return false;
			}
			$type = substr($type,0,1);
			if (!in_array($type,array('r','w'))) {
				$this->error("Invalid type $type");
				return false;
			}

			// Get Existing Privileges
			$privileges = $this->getPrivileges();
			if ($level == 'a') {
				$privileges->$level = preg_replace("/$type/","",$privileges->$level);
			}
			else {
				$privileges->$level->$id = preg_replace("/$type/","",$privileges->$level->$id);
			}
			return $this->update(array('access_privileges' => json_encode($privileges)));
		}

		public function readable($user_id = null) {
			if (!isset($user_id)) $user_id = $GLOBALS['_SESSION_']->customer->id;
			$user = new \Register\Customer($user_id);
			$organization_id = $user->organization_id;

			$flag = 'r';

			if ($this->user_id == $user_id) return true;
			$privileges = $this->getPrivileges();

			if (isset($privileges->a) && preg_match("/$flag/",$privileges->a)) return true;

			if (isset($privileges->u->$user_id) && preg_match("/$flag/",$privileges->u->$user_id)) return true;

			if (isset($privileges->o->$organization_id) && preg_match("/$flag/",$privileges->o->$organization_id)) return true;

			$roles = $user->roles();
			foreach ($roles as $role) {
				$role_id = $role->id;
				if (isset($privileges->r->$role_id) && preg_match("/$flag/",$privileges->r->$role_id)) return true;
			}
			return false;
		}

		public function writable($user_id = null) {
			if (!isset($user_id)) $user_id = $GLOBALS['_SESSION_']->customer->id;
			$user = new \Register\Customer($user_id);
			$organization_id = $user->organization_id;

			$flag = 'w';

			if ($this->user_id == $user_id) return true;
			$privileges = $this->getPrivileges();

			if (isset($privileges->a) && preg_match("/$flag/",$privileges->a)) return true;

			if (isset($privileges->u->$user_id) && preg_match("/$flag/",$privileges->u->$user_id)) return true;

			if (isset($privileges->o->$organization_id) && preg_match("/$flag/",$privileges->o->$organization_id)) return true;

			$roles = $user->roles();
			foreach ($roles as $role) {
				$role_id = $role->id;
				if (isset($privileges->r->$role_id) && preg_match("/$flag/",$privileges->r->$role_id)) return true;
			}
			return false;
		}

		public function download() {
			$repository = $this->repository();
			$repository->retrieveFile($this);
			$this->error($repository->error());
		}
		
		public function code() {
			return $this->code;
		}

		public function addError($errorMessage = '') {
			$this->error($errorMessage);
			return $this->error();
		}

		public function success() {
			return $this->success;
		}

		public function downloadURI() {
			return '/_storage/file/'.$this->code.'/download';
		}

		public function inodeExists() {
			if ($this->id && file_exists($this->path())) return true;
			return false;
		}

		public function setMetaData($key,$value) {
			$metadata = new \Storage\FileMetadata($this->id);
			$metadata->add($key,$value);
			return true;
		}

		public function getMetadata($key) {
			$metadata = new \Storage\FileMetadata($this->id);
			$metadata->get($this->id,$key);
			return $metadata;
		}

		public function upload ($parameters) {
		
			// make sure we have a file present in the request to upload it
			if (empty($_FILES)) $this->addError("Repository not found");
		
			// make sure all the required parameters are set for upload to continue
			if (! preg_match('/^\//',$parameters['path'])) $parameters['path'] = '/'.$parameters['path'];
			$factory = new \Storage\RepositoryFactory();
			if (!empty($parameters['repository_id'])) {
				$repository = $factory->load($parameters['repository_id']);
			}
			elseif (!empty($parameters['repository_code'])) {
				$repository = $factory->get($parameters['repository_code']);
			}
			elseif (!empty($parameters['repository_name'])) {
				$repository = $factory->find($parameters['repository_name']);
			}

			if ($factory->error()) {
				$this->addError("Error loading repository: ".$factory->error());
			} else if (! $repository->id) {
				$this->addError("Repository not found");
			} else {
				app_log("Identified repo '".$repository->name."'");
				
				if (! file_exists($_FILES['uploadFile']['tmp_name'])) {
					if (empty($_FILES['uploadFile']['tmp_name']) && empty($_FILES['uploadFile']['name'])) {
						$this->addError("No file was selected to upload");
					} else if (empty($_FILES['uploadFile']['tmp_name']) && !empty($_FILES['uploadFile']['name'])) {
						$this->addError("File chosen was file too large: php.ini upload_max_filesize=".ini_get('upload_max_filesize'));
					} else {
						$this->addError("Temp file '" . $_FILES['uploadFile']['tmp_name'] . "' not found");
					}
				} else {
				
					// Check for Conflict 
					$filelist = new \Storage\FileList();
					list($existing) = $filelist->find(
						array (
							'repository_id' => $repository->id,
							'path'	=> $parameters['path'],
							'name' => $_FILES['uploadFile']['name'],
						)
		  			);
		  			
		 			if ($existing->id) {
						$this->addError("File already exists with that name in repository: ". $repository->name);
					} else {
					
						// Add File to Library
						if ($this->error()) $this->addError("Error initializing file: " . $this->error());
						$this->add(
							array(
								'repository_id'     => $repository->id,
								'name'              => $_FILES['uploadFile']['name'],
								'path'				=> $parameters['path'],
								'mime_type'         => $_FILES['uploadFile']['type'],
								'size'              => $_FILES['uploadFile']['size'],
							)
						);

						// Upload File Into Repository 
						$destinationPath = $parameters['type'] . "/" . $parameters['ref_id']  . "/";
						try {
							if ($this->error()) { 
								$this->addError("Error adding file: " . $this->error());
							} else if (! $repository->addFile($this, $_FILES['uploadFile']['tmp_name'], $destinationPath)) {
								$this->delete();
								$this->addError('Unable to add file to repository: '.$repository->error());
							} else {
								app_log("Stored file ".$this->id." at ".$repository->path."/".$this->code);
								$this->success = "File uploaded";
								
								// add file type refrence for this file to be a part of the support/engineering ticket
								if (isset($parameters['type']) && isset($parameters['ref_id'])) {
									$fileMetaData = new \Storage\FileMetadata($this->id);
									$fileMetaData->add(array('file_id' => $this->id, 'key' => $parameters['type'], 'value' => $parameters['ref_id']));   
								}                            
							}
						} catch (\Exception $e) {
							$this->delete();
							$this->addError('System Exception has occured, unable to add file to repository: '.$repository->error());
							app_log("repository->addFile(): Exception" . $e->getMessage(),'notice');
							return false;
						}
					}
				}
			}
			return true;
		}

		public function writePermitted($user_id = null) {
			return $this->writable($user_id);
		}

		public function readPermitted($user_id = null) {
			return $this->readable($user_id);
		}

		public function getPrivileges() {
			app_log("File: ".$this->name,'info');
			app_log("Privilege JSON: ".$this->access_privilege_json,'info');
			if (empty($this->access_privilege_json)) {
				return new \stdClass();
			}
			else {
				return json_decode($this->access_privilege_json);
			}
		}

		public function privilegeList() {
			$privileges = $this->getPrivileges();
			$list = array();
			$allSet = false;
			foreach ($privileges as $level => $privilege) {
				$read = false;
				$write = false;

				if ($level == 'a') {
					$allSet = true;
					if (is_array($privilege)) {
						foreach ($privilege as $id => $mask) {
							if (preg_match('/r/',$mask)) $read = true;
							if (preg_match('/w/',$mask)) $write = true;
						}
					}
					else {
						if (is_object($privilege)) $privilege = implode('',get_object_vars($privilege));
						if (preg_match('/r/',$privilege)) $read = true;
						if (preg_match('/w/',$privilege)) $write = true;
					}
					$list['a'][0] = (object) array(
						'level'			=> 'a',
						'levelName'		=> 'All',
						'entityId'		=> 0,
						'entityName'	=> 'N/A',
						'mask'			=> $privilege,
						'read'			=> $read,
						'write'			=> $write
					);
				}
				else {
					foreach ($privilege as $id => $mask) {
						if (is_array($mask)) {
							foreach ($mask as $set) {
								if (is_object($set)) $set = implode('',get_object_vars($set));
								if (preg_match('/r/',$set)) $read = true;
								if (preg_match('/w/',$set)) $write = true;
								if (preg_match('/x/',$set)) $execute = true;
							}
						}
						else {
							if (is_object($mask)) $set = implode('',get_object_vars($mask));
							if (preg_match('/r/',$mask)) $read = true;
							if (preg_match('/w/',$mask)) $write = true;
							if (preg_match('/x/',$mask)) $execute = true;
						}
						if ($level == 'o') {
							$organization = new \Register\Organization($id);
							$levelName = "Organization";
							$entityName = $organization->name;
						}
						elseif ($level == 'u') {
							$user = new \Register\Customer($id);
							$levelName = "User";
							$entityName = $user->login;
						}
						elseif ($level == 'r') {
							$role = new \Register\Role($id);
							$levelName = "Role";
							$entityName = $role->name;
						}
						$list[$level][$id] = (object) array(
							'level' => $level,
							'levelName'		=> $levelName,
							'entityId'		=> $id,
							'entityName'	=> $entityName,
							'mask'			=> $mask,
							'read'			=> $read,
							'write'			=> $write
						);
					}
				}
			}
			if (! $allSet) {
				$list['a'][0] = (object) array(
					'level'			=> 'a',
					'levelName' 	=> 'All',
					'entityId'		=> 0,
					'entityName'	=> 'N/A',
					'mask'			=> null,
					'read'			=> false,
					'write'			=> false,
					'execute'		=> false
				);
			}
			return $list;
		}

		public function updatePrivileges($object_type, $object_id, $mask) {
			app_log("Updating privileges on ".$this->code,'info');
			$privileges = new \stdClass();
			if (!empty($this->access_privilege_json)) {
				$privileges = json_decode($this->access_privilege_json);
			}
			if (preg_match('/^a/i',$object_type)) $object_type = 'a'; // All/Everyone
			elseif (preg_match('/^o/i',$object_type)) $object_type = 'o'; // Organization
			elseif (preg_match('/^r/i',$object_type)) $object_type = 'r'; // Role
			elseif (preg_match('/^u/i',$object_type)) $object_type = 'u'; // User/Customer
			else {
				$this->error("Invalid permision object type");
				return false;
			}

			if (!is_object($privileges->$object_type)) $privileges->$object_type = new \stdClass();
			if (!is_array($privileges->$object_type->$object_id)) $privileges->$object_type->$object_id = array();
			foreach (array('w','r','g') as $priv) {
				app_log($priv,'info');
				if (in_array('+'.$priv,$mask) && !in_array($priv,$privileges->$object_type->$object_id)) {
					app_log("Adding $priv to ".$this->name,'info');
					array_push($privileges->$object_type->$object_id,$priv);
				}
				elseif (in_array('-'.$priv,$mask)) {
					$idx = array_search($priv,$privileges->$object_type->$object_id,true);
					if (is_numeric($idx)) {
						app_log("IDX = $idx",'info');
						app_log("Dropping $priv from ".$this->name,'info');
						unset($privileges->$object_type->$object_id[$idx]);
						\array_values($privileges->$object_type->$object_id);
					}
				}
				elseif (in_array('-'.$priv,$mask)) {
					app_log(" => ".print_r($privileges->$object_type->$object_id,false),'info');
					app_log("Nothing for $priv");
				}
			}
			app_log("Writing ".json_encode($privileges)." to ".$this->name,'info');
			return $this->update(array('access_privileges' => json_encode($privileges)));
		}

		public function accessPrivilege() {

		}

		public function validPath($string) {
			// Only a virtual path!
			if (preg_match('/\.{2}/',$string)) return false;
			if (preg_match('/\/{2}/',$string)) return false;
			if (preg_match('/^\/[\w\-\.\_\/]*$/',$string)) return true;
			else return false;
		}

		public function validExtension($string) {
			// Images
			if (preg_match('/^(png|jpg|jpeg|gif|tif)$/',$string)) return true;
			// Documents
			if (preg_match('/^(doc|docx|pdf|xls|xlsx|ppt|pptx|txt|rtf|html|htm|css|xml|json|csv|odt|ods)$/',$string)) return true;
			// Media
			if (preg_match('/^(mp3|mp4|mov|avi|wmv)$/',$string)) return true;
			// Archives
			if (preg_match('/^(zip|tar|gz|tgz|bz2|rar)$/',$string)) return true;

			return false;
		}
	}
