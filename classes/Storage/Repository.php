<?php
	namespace Storage;

	class Repository Extends \BaseModel {
		public $name;
		public $type;
		public $code;
		public $status;
		public $endpoint;
		public $secretKey;
		public $accessKey;
		public $default_privileges_json;
		public $override_privileges_json;

		# Constructor
		public function __construct($id = 0) {
			$this->_tableName = 'storage_repositories';
			parent::__construct($id);
		}

		# Add a Repository Record
		public function add($parameters = []) {

			$this->clearError();

			# Generate Unique Code if none provided
			if (! isset($parameters['code']) || ! strlen($parameters['code'])) $parameters['code'] = uniqid();

			if (! $this->validType($parameters['type'])) {
				$this->error("Invalid type");
				return false;
			}
			else {
				$this->type = $parameters['type'];
			}
			
			if (! $this->validCode($parameters['code'])) {
				$this->error("Invalid code");
				return false;
			}
			
			if (! isset($parameters['status']) || ! strlen($parameters['status'])) {
				$parameters['status'] = 'NEW';
			} else if (! $this->validStatus($parameters['status'])) {
				$this->error("Invalid status");
				return false;
			}
			
			if (! $this->validName($parameters['name'])) {
				$this->error("Invalid name");
				return false;
			}
	
			# Prepare Query
			$add_object_query = "
				INSERT
				INTO	storage_repositories
				(		code,name,type,status)
				VALUES
				(		?,?,?,?)
			";

			# Execute Query
			$GLOBALS['_database']->Execute(
				$add_object_query,
				array(
					$parameters['code'],
					$parameters['name'],
					$this->type,
					$parameters['status']
				)
			);

			# Check for errors
			if ($GLOBALS['_database']->ErrorMsg()) {
				$this->SQLError($GLOBALS['_database']->ErrorMsg());
				return false;
			}

			# Fetch ID of new record
			$this->id = $GLOBALS['_database']->Insert_ID();

			// audit the add event
			$auditLog = new \Site\AuditLog\Event();
			$auditLog->add(array(
				'instance_id' => $this->id,
				'description' => 'Added new '.$this->_objectName(),
				'class_name' => get_class($this),
				'class_method' => 'add'
			));

			app_log("Repo ".$this->id." created, updating");
			return $this->update($parameters);
		}

		# Update Repository Record
		public function update($parameters = []): bool {
			$this->clearError();

			# Prepare Query
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
					$this->error("Invalid name '".$parameters['name']."'");
					return false;
				}
			}
			
			if (isset($parameters['status'])) {
				if ($this->validStatus($parameters['status'])) {
					$update_object_query .= ",
					status = ?";
					array_push($bind_params,$parameters['status']);
				} else {
					$this->error("Invalid status");
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
				$this->SQLError($GLOBALS['_database']->ErrorMsg());
				return false;
			}
			if (isset($parameters['path'])) $this->_setMetadata('path',$parameters['path']);
			app_log("Repo ".$this->id." updated, getting details");
			
			// audit the update event
			$auditLog = new \Site\AuditLog\Event();
			$auditLog->add(array(
				'instance_id' => $this->id,
				'description' => 'Updated '.$this->_objectName(),
				'class_name' => get_class($this),
				'class_method' => 'update'
			));

			return $this->details();
		}

		# Fetch Repository Record and Populate Object Variables
		public function details(): bool {
			$this->clearError();

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
				$this->SQLError($GLOBALS['_database']->ErrorMsg());
				return false;
			}

			$object = $rs->FetchNextObject(false);
			$this->name = $object->name;
			$this->type = $object->type;
			$this->code = $object->code;
			$this->status = $object->status;
			$this->default_privileges_json = $object->default_privileges;
			$this->override_privileges_json = $object->override_privileges;;

			return true;
		}

		# Get Files in Repository
		public function files($path = "/") {
			$filelist = new FileList();
			return $filelist->find(array('repository_id' => $this->id,'path' => $path));
		}

		public function uploadFile($uploadedFile,$path = '/') {
			// Check for Errors
			if ($uploadedFile['error'] == 1) {
				$this->error("Uploaded file too large");
				app_log("Upload file exceeds the upload_max_filesize directive",'info');
				return null;
			}
			elseif ($uploadedFile['error'] == 2) {
				$this->error("Uploaded file too large");
				app_log("Uploaded file exceeds the MAX_FILE_SIZE directive in the HTML form",'info');
				return null;
			}
			elseif ($uploadedFile['error'] == 3) {
				$this->error("Upload failed before completion");
				app_log("The uploaded file was only partially uploaded",'info');
				return null;
			}
			elseif ($uploadedFile['error'] == 4) {
				$this->error("No file was uploaded");
				app_log("No file was uploaded",'info');
				return null;
			}
			elseif ($uploadedFile['error'] == 6) {
				$this->error("Server error uploading file");
				app_log("Upload failed: Temporary folder unavailable",'error');
				return null;
			}
			elseif ($uploadedFile['error'] == 7) {
				$this->error("Server error uploading file");
				app_log("Upload failed: Failed to write upload to disk",'error');
				return null;
			}
			elseif ($uploadedFile['error'] == 8) {
				$this->error("Server error uploading file");
				app_log("Upload failed: Upload blocked by extension",'error');
				return null;
			}
			elseif (! file_exists($uploadedFile['tmp_name'])) {
				$this->error("Temp file '".$uploadedFile['tmp_name']."' not found");
				return null;
			}
			else {
			
				// Check for Conflict 
				$filelist = new \Storage\FileList();
				list($existing) = $filelist->find(
					array(
						'repository_id' => $this->id,
						'path'	=> $path,
						'name' => $uploadedFile['name'],
					)
				);
				
				if ($existing->id) {
					$this->error("File already exists with that name in repo ".$this->name);
				}
				else {
					// Add File to Library 
					$file = new \Storage\File();
					if ($file->error()) error("Error initializing file: ".$file->error());
					$file->add(
						array(
							'repository_id'     => $this->id,
							'name'              => $uploadedFile['name'],
							'path'				=> $path,
							'mime_type'         => $uploadedFile['type'],
							'size'              => $uploadedFile['size'],
						)
					);

					// Upload File Into Repository 
					if ($file->error()) $this->error("Error adding file: ".$file->error());
					elseif (! $this->addFile($file,$_FILES['uploadFile']['tmp_name'])) {
						$file->delete();
						$this->error('Unable to add file to repository: '.$this->error());
						return null;
					} else {
						app_log("Stored file ".$file->id." at ".$path."/".$file->code);
						return $file;
					}
				}
			}
		}

		# Add File to Database
		public function addFileToDb($parameters) {
			$file = new \Storage\File();
			$parameters['repository_id'] = $this->id;
			return $file->add($parameters);
		}

		# Drop File from Database
		public function deleteFileFromDb($file_id) {
			$file = new \Storage\File($file_id);
			if ($file->exists()) {
				if ($file->delete()) {
					return true;
				}
				else {
					$this->error($file->error());
					return false;
				}
			}
			else {
				$this->error("File not found");
				return false;
			}
		}

		# List Existing Directories
		public function directories($path = "/") {
			$directorylist = new DirectoryList();
			return $directorylist->find(array('repository_id' => $this->id,'path' => $path));
		}

		public function _updateMetadata($key, $value) {
			# Prepare Query
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
				$this->SQLError($GLOBALS['_database']->ErrorMsg());
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
				$this->SQLError($GLOBALS['_database']->ErrorMsg());
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
				$this->SQLError($GLOBALS['_database']->ErrorMsg());
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

		/************************************/
		/* Validation Functions             */
		/************************************/
		public function validName($string): bool {
			if (preg_match('/^\w[\w\-\_\.\s]*$/',$string)) return true;
			return false;
		}
		public function validStatus($string): bool {
			if (preg_match('/^(NEW|ACTIVE|DISABLED)$/i',$string)) return true;
			return false;
		}
		public function validType($string): bool {
			if (preg_match('/^(Local|s3|Drive|DropBox)$/i',$string)) return true;
			return false;
		}
		public function validPath($string) {
			// Only certain instances require path
			if (empty($string)) return true;
			else return false;
		}
		public function validAccessKey($string) {
			// Only certain instances require access key
			if (empty($string)) return true;
			else return false;
		}
		public function validSecretKey($string) {
			// Only certain instances require secret key
			if (empty($string)) return true;
			else return false;
		}
		public function validBucket($string) {
			// Only certain instances require bucket
			if (empty($string)) return true;
			else return false;
		}
		public function validRegion($string) {
			// Only certain instances require region
			if (empty($string)) return true;
			else return false;
		}
		public function validEndpoint($string) {
			return true;
		}
	}
