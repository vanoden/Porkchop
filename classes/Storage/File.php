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
	
            app_log('Storage::File::add(): '.print_r($parameters,false));
            
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
							$this->SQLError($GLOBALS['_database']->ErrorMsg());
							return false;
						}
					} else {
						$this->error("Role not found");
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
							$this->SQLError($GLOBALS['_database']->ErrorMsg());
							return false;
						}
					} else {
						$this->error("Role not found");
						return false;
					}
				}
				return false;
			}
		}

		public function revoke($id,$type) {
		
			if ($type == 'read') {
			
				if ($this->read_protect == 'NONE') {
					$this->error("File is globally readable");
					return false;
				}
				else if ($this->read_protect == 'AUTH') {
					$this->error("File is readable by all authenticated users");
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
							$this->SQLError($GLOBALS['_database']->ErrorMsg());
							return false;
						}
					} else {
						$this->error("Role not found");
						return false;
					}
				}				
				return false;

			} else if ($type == 'write') {
				if ($this->write_protect == 'NONE') {
					$this->error("File is globally writable");
					return false;
				}
				else if ($this->write_protect == 'AUTH') {
					$this->error("File is writable by all authenticated users");
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
							$this->SQLError($GLOBALS['_database']->ErrorMsg());
							return false;
						}
					} else {
						$this->error("Role not found");
						return false;
					}
				}
				return false;
			}
		}

		public function readable($user_id) {
		
			// World Readable 
			if ($this->read_protect == 'NONE') return true;

			// Owner Can Always Access 
			if ($this->user_id == $GLOBALS['_SESSION_']->customer->id) return true;

			// Any Authenticated Visitor 
			if ($this->read_protect == 'AUTH' && $GLOBALS['_SESSION_']->customer->id > 0) return true;

			// Visitor in Specified Role 
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
					$this->SQLError($GLOBALS['_database']->ErrorMsg());
					return false;
				};
				
				list($ok) = $rs->fetchrow();
				if ($ok > 0) return true;
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
                        }
				    }
			    }
		    }
		}

		public function writePermitted($user_id = null) {
			if (!isset($user_id)) $user_id = $GLOBALS['_SESSION_']->customer->id;
			$user = new \Register\Person($user_id);

			$privileges = array();
			if (!empty($this->access_privilege_json)) {
				$privileges = json_decode($this->access_privilege_json,true);
			}
			if (empty($this->repository())) return false;
			$default_privileges = $this->repository()->default_privileges();
			$override_privileges = $this->repository()->override_privileges();

			app_log($this->user_id." vs ".$user_id,'info');
			if ($this->user_id == $user_id) return true;
			app_log(print_r($override_privileges['o'][$user->organization()->id],false),'info');
			if (in_array('-w',$override_privileges['o'][$user->organization()->id])) return false;
			app_log("Here 4",'info');
			if (in_array('-w',$override_privileges['u'][$user->id])) return false;
			app_log("Here 5",'info');
			if (in_array('w',$privileges['o'][$user->organization()->id])) return true;
			app_log("Here 6",'info');
			if (in_array('w',$privileges['u'][$user->id])) return true;
			app_log("Here 7",'info');
			if (in_array('w',$default_privileges['o'][$user->organization()->id])) return true;
			app_log("Here 8",'info');
			if (in_array('w',$default_privileges['u'][$user->id])) return true;
			app_log("Here 9",'info');
			return false;
		}

		public function readPermitted($user_id = null) {
			if (!isset($user_id)) $user_id = $GLOBALS['_SESSION_']->customer->id;
			$user = new \Register\Person($user_id);

			$privileges = array();
			if (!empty($this->access_privilege_json)) {
				$privileges = json_decode($this->access_privilege_json,true);
			}
			if (empty($this->repository())) return false;
			$default_privileges = $this->repository()->default_privileges();
			$override_privileges = $this->repository()->override_privileges();

			app_log($this->user_id." vs ".$user_id,'info');
			if ($this->user_id == $user_id) return true;
			app_log(print_r($override_privileges['o'][$user->organization()->id],false),'info');
			if (in_array('-r',$override_privileges['o'][$user->organization()->id])) return false;
			app_log("Here 4",'info');
			if (in_array('-r',$override_privileges['u'][$user->id])) return false;
			app_log("Here 5",'info');
			if (in_array('r',$privileges['o'][$user->organization()->id])) return true;
			app_log("Here 6",'info');
			if (in_array('r',$privileges['u'][$user->id])) return true;
			app_log("Here 7",'info');
			if (in_array('r',$default_privileges['o'][$user->organization()->id])) return true;
			app_log("Here 8",'info');
			if (in_array('r',$default_privileges['u'][$user->id])) return true;
			app_log("Here 9",'info');
			return false;
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
			if (preg_match('/^\/[\w\-\.\_\/]*$/',$string)) return true;
			else return false;
		}
	}
