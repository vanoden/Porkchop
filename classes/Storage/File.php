<?php
	namespace Storage;

	class File {
	
		private $_repository_id;
		public $code;
		public $id;
		public $error;
		public $success;
		public $uri;
		public $read_protect;
		public $write_protect;
		public $mime_type;
		public $size;
		public $timestamp;
		public $name;
		private $path;
		private $original_name;

		public function __construct($id = 0) {
			if ($id > 0) {
				$this->id = $id;
				$this->details();
			}
		}

		public function add($parameters = array()) {
	
            app_log('Storage::File::add(): '.print_r($parameters,true));
            
			if (! isset($parameters['code']) || ! strlen($parameters['code'])) $parameters['code'] = uniqid();
			
			if (! preg_match('/^[\w\-\.\_]+$/',$parameters['code'])) {
				$this->error = "Invalid code '".$parameters['code']."'";
				return false;
			}
			
			$this->code = $parameters['code'];
			if (! $this->_valid_type($parameters['mime_type'])) {
				$this->error = "Invalid mime_type '".$parameters['mime_type']."'";
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
				$this->error = "SQL Error in Storage::File::add(): ".$GLOBALS['_database']->ErrorMsg();
				return false;
			}
			
			$this->id = $GLOBALS['_database']->Insert_ID();
			return $this->update($parameters);
		}
		
		public function update($parameters = array()) {
		
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
			
			query_log($update_object_query);
			$update_object_query .= "
				WHERE	id = ?
			";
			array_push($bind_params,$this->id);

			$GLOBALS['_database']->Execute($update_object_query,$bind_params);

			if ($GLOBALS['_database']->ErrorMsg()) {
				$this->error = "SQL Error in Storage::File::update(): ".$GLOBALS['_database']->ErrorMsg();
				return false;
			}
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
			if ($object->id) {
				$this->code = $object->code;
				$this->name = $object->name;
				$this->path = $object->path;
				$this->display_name = $object->display_name;
				$this->description = $object->description;
				$this->mime_type = $object->mime_type;
				$this->size = $object->size;
				$this->user = new \Register\Customer($object->user_id);
				$this->date_created = $object->date_created;
				$this->timestamp = strtotime($this->timestamp);
				$factory = new RepositoryFactory();
				$this->repository = $factory->load($object->repository_id);
				if ($this->repository->endpoint) $this->uri = $this->repository->endpoint."/".$this->name;
				else $this->endpoint = 'N/A';
				$this->read_protect = $object->read_protect;
				$this->write_protect = $object->write_protect;
			} else {
				$this->id = null;
				$this->code = null;
			}
			
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
				} else {
					$this->name = $name;
				}
			}
			return $this->name;
		}
		
		public function path() {
			return $this->path;
		}
		
		public function repository_id($id = 0) {
			if ($id > 0) {
			
				// Get Repository 
				$repository = new Repository($id);
				if (! $repository->id) {
					$this->error = "Repository not found";
					return false;
				} else {
					$this->_repository_id = $id;
				}
			}
			return $this->_repository_id;
		}

		public function repository() {
			return new Repository($this->_repository_id);
		}

		private function _valid_type($name) {
			if (preg_match('/^(image|application|text)\/(png|jpg|jpeg|tif|tiff|plain|html|csv|cs|js|xml|json|gzip|tar\+gzip|pdf|octet\-stream)$/',$name)) return true;
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
					} else {
						$this->error = "Role not found";
						return false;
					}
				}
		
				return false;
				
			} else if ($type == 'write') {
			
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
					} else {
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
				} else if ($this->read_protect == 'AUTH') {
					$this->error = "File is readable by all authenticated users";
					return false;
				} else if ($this->read_protect == 'ROLE') {
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
					} else {
						$this->error = "Role not found";
						return false;
					}
				}				
				return false;

			} else if ($type == 'write') {
				if ($this->write_protect == 'NONE') {
					$this->error = "File is globally writable";
					return false;
				} else if ($this->write_protect == 'AUTH') {
					$this->error = "File is writable by all authenticated users";
					return false;
				} else if ($this->write_protect == 'ROLE') {
				
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
					} else {
						$this->error = "Role not found";
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
			if ($this->user->id == $GLOBALS['_SESSION_']->customer->id) return true;

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
					$this->error = "SQL Error in Storage::File::readable(): ".$GLOBALS['_database']->ErrorMsg();
					return false;
				};
				
				list($ok) = $rs->fetchrow();
				if ($ok > 0) return true;
			}
			return false;
		}

		public function download() {
			return $this->repository->retrieveFile($this);
		}
		
        public function code() {
            return $this->code;
        }

        public function addError($errorMessage = '') {
            $this->error = $errorMessage;        
            return $this->error;
        }

        public function error() {
            return $this->error;
        }

        public function success() {
            return $this->success;
        }

		public function downloadURI() {
			return '/_storage/file/'.$this->code.'/download';
		}

		public function exists() {
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
		    $repository = $factory->find($parameters['repository_name']);
		    
		    if ($factory->error) {
			    $this->addError("Error loading repository: ".$factory->error);
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
					    if ($this->error) $this->addError("Error initializing file: " . $this->error);
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
					        if ($this->error) { 
					            $this->addError("Error adding file: " . $this->error);
					        } else if (! $repository->addFile($this, $_FILES['uploadFile']['tmp_name'], $destinationPath)) {
						        $this->delete();
						        $this->addError('Unable to add file to repository: '.$repository->error);
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
					        $this->addError('System Exception has occured, unable to add file to repository: '.$repository->error);
    						app_log("repository->addFile(): Exception" . $e->getMessage());
                        }
				    }
			    }
		    }
		}
	}
