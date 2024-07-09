<?php
	namespace Storage;

	class Repository Extends \BaseModel {
		public $name;						// Name of Repository
		public $type;						// Type of Repository - S3, Local, etc.
		public $code;						// Unique Code for Repository
		public $status;						// Status of Repository - NEW, ACTIVE, DISABLED
		public $endpoint;					// Endpoint for Repository
		public $secretKey;					// Secret Key for AWS Repository
		public $accessKey;					// Access Key for AWS Repository
		public $default_privileges_json;	// JSON string representing default privileges
		public $override_privileges_json;	// JSON string representing override privileges

		/**
		 * Class Constructor
		 * @param int Optional 
		 * @return void 
		 */
		public function __construct($id = 0) {
			$this->_tableName = 'storage_repositories';
			parent::__construct($id);
		}

		/** 
		 * Create a new Storage Repository
		 * @param array $parameters
		 * @return bool - True if successfully added
		 */
		public function add($parameters = []) {
			// Clear any previous errors
			$this->clearError();

			// Generate Unique Code if none provided
			if (! isset($parameters['code']) || ! strlen($parameters['code'])) $parameters['code'] = uniqid();

			// Make sure the specified type is valid
			if (! $this->validType($parameters['type'])) {
				$this->error("Invalid type");
				return false;
			}
			else {
				$this->type = $parameters['type'];
			}

			// Make sure the specified code is valid
			if (! $this->validCode($parameters['code'])) {
				$this->error("Invalid code");
				return false;
			}

			// Make sure the specified status is valid
			if (! isset($parameters['status']) || ! strlen($parameters['status'])) {
				$parameters['status'] = 'NEW';
			} else if (! $this->validStatus($parameters['status'])) {
				$this->error("Invalid status");
				return false;
			}

			// Make sure the specified name is valid
			if (! $this->validName($parameters['name'])) {
				$this->error("Invalid name");
				return false;
			}
	
			// Prepare Query
			$database = new \Database\Service();
			$add_object_query = "
				INSERT
				INTO	storage_repositories
				(		code,name,type,status)
				VALUES
				(		?,?,?,?)
			";

			$database->AddParam($parameters['code']);
			$database->AddParam($parameters['name']);
			$database->AddParam($this->type);
			$database->AddParam($parameters['status']);

			// Execute Query
			$database->Execute($add_object_query);

			// Check for errors
			if ($database->ErrorMsg()) {
				$this->SQLError($database->ErrorMsg());
				return false;
			}

			// Fetch ID of new record
			$this->id = $GLOBALS['_database']->Insert_ID();

			// Audit the add event
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

		/**
		 * Update Currently Selected Repository Record
		 * @param array $parameters
		 * @return bool - True if successfully updated
		 */
		public function update($parameters = []): bool {
			// Clear any previous errors
			$this->clearError();

			# Prepare Query
			$database = new \Database\Service();
			$update_object_query = "
				UPDATE	storage_repositories
				SET		id = id
			";

			if (isset($parameters['name'])) {
				if ($this->validName($parameters['name'])) {
					$update_object_query .= ",
					name = ?";
					$database->AddParam($parameters['name']);
				} else {
					$this->error("Invalid name '".$parameters['name']."'");
					return false;
				}
			}

			if (isset($parameters['status'])) {
				if ($this->validStatus($parameters['status'])) {
					$update_object_query .= ",
					status = ?";
					$database->AddParam($parameters['status']);
				} else {
					$this->error("Invalid status");
					return false;
				}
			}

			if (isset($parameters['default_privileges_json'])) {
				if (!json_decode($parameters['default_privileges_json'])) {
					$this->error("Invalid default privileges JSON");
					return false;
				}
				$update_object_query .= ",
					default_privileges = ?";
				$database->AddParam($parameters['default_privileges_json']);
			}
			
			$update_object_query .= "
				WHERE	id = ?
			";
			
			$database->AddParam($this->id);

			// Execute Query
			$database->Execute($update_object_query);

			// Check for errors
			if ($database->ErrorMsg()) {
				$this->SQLError($database->ErrorMsg());
				return false;
			}
			if (isset($parameters['path'])) $this->_setMetadata('path',$parameters['path']);
			app_log("Repo ".$this->id." updated, getting details");
			
			// Audit the update event
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
			// Clear any previous errors
			$this->clearError();

			// Prepare Query
			$database = new \Database\Service();
			$get_object_query = "
				SELECT	*
				FROM	storage_repositories
				WHERE	id = ?
			";
			$database->AddParam($this->id);

			// Execute Query
			$rs = $database->Execute($get_object_query);

			// Check for errors
			if (! $rs) {
				$this->SQLError($database->ErrorMsg());
				return false;
			}

			// Fetch Record From Database as Object
			$object = $rs->FetchNextObject(false);
			$this->name = $object->name;
			$this->type = $object->type;
			$this->code = $object->code;
			$this->status = $object->status;
			$this->default_privileges_json = $object->default_privileges;
			$this->override_privileges_json = $object->override_privileges;;

			return true;
		}

		/**
		 * Get Files in Repository
		 * @param string $path
		 * @return array - Array of File Objects - Optional, defaults to '/'
		 */
		public function files($path = "/") {
			$filelist = new FileList();
			return $filelist->find(array('repository_id' => $this->id,'path' => $path));
		}

		/**
		 * Add File to Repository
		 * @param PHP Uploaded File, single element of $_FILES array
		 * @param string $source
		 * @return Storage\File instance
		 */
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
						// Remove the file from the database so they can try again
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

		/**
		 * Add File to Database
		 * @param array $parameters
		 * @return bool - True if successfully added
		 */
		public function addFileToDb($parameters) {
			$file = new \Storage\File();
			$parameters['repository_id'] = $this->id;
			return $file->add($parameters);
		}

		/**
		 * Drop File from Database
		 * @param int $file_id
		 * @return bool - True if successfully deleted
		 */
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

		/**
		 * List Existing Directories
		 * @param string $path - Optional, defaults to '/'
		 * @return array - Array of Directory Objects
		 */
		public function directories($path = "/") {
			$directorylist = new DirectoryList();
			return $directorylist->find(array('repository_id' => $this->id,'path' => $path));
		}

		/**
		 * Update Repository Metadata - Wrapper for _setMetadata as it does the same thing
		 * @param string key - Identifer
		 * @param string value - Content
		 */
		public function _updateMetadata($key, $value) {
			return $this->_setMetadata($key,$value);
		}

		/**
		 * Set Repository Metadata
		 * @param string key - Identifer
		 * @param string value - Content
		 */
		public function _setMetadata($key,$value) {
			// Clear Existing Errors
			$this->clearError();

			// Prepare Query
			$database = new \Database\Service();
			$set_object_query = "
				INSERT
				INTO	storage_repository_metadata
				(repository_id,`key`,value)
				VALUES	(?,?,?)
				ON DUPLICATE KEY UPDATE
				value = ?
			";
			$database->AddParam($this->id);
			$database->AddParam($key);
			$database->AddParam($value);
			$database->AddParam($value);

			// Execute Query
			$database->Execute($set_object_query);

			// Check for errors
			if ($database->ErrorMsg()) {
				$this->SQLError($database->ErrorMsg());
				return false;
			}
			return true;
		}

		/**
		 * Get value associated with specified key from repository metadata
		 * @param mixed $key 
		 * @return mixed
		 */
		public function _metadata($key) {
			// Clear Existing Errors
			$this->clearError();

			// Prepare Query
			$database = new \Database\Service();
			$get_value_query = "
				SELECT	value
				FROM	storage_repository_metadata
				WHERE	repository_id = ?
				AND		`key` = ?
			";
			$database->AddParam($this->id);
			$database->AddParam($key);

			// Execute Query
			$rs = $database->Execute($get_value_query);

			// Check for errors
			if (! $rs) {
				$this->SQLError($database->ErrorMsg());
				return null;
			}
			list($value) = $rs->FetchRow();
			return $value;
		}

		/**
		 * Get value associated with specified key from repository metadata
		 * Deprecated - Use _metadata instead
		 * @param mixed $key 
		 * @return mixed
		 */
		public function getMetadata($key) {
			return $this->_metadata($key);
		}

		public function getFileFromPath($path) {
			$file = new \Storage\File();
			return $file->fromPath($this->id,$path);
		}

		public function default_privileges() {
			$privileges = new \Resource\PrivilegeList();
			return $privileges->fromJSON($this->default_privileges_json);
		}

		public function override_privileges() {
			$privileges = new \Resource\PrivilegeList();
			return $privileges->fromJSON($this->override_privileges_json);
		}

		/************************************/
		/* Repository Privileges			*/
		/************************************/
		/**
		 * Check if user has read access to repository
		 * @param mixed $user_id 
		 * @return bool 
		 */
		public function readable($user_id) {
			// Default User to current session
			if (!isset($user_id)) $user_id = $GLOBALS['_SESSION_']->customer->id;

			// Fetch User and Organization Records
			$user = new \Register\Customer($user_id);
			$organization_id = $user->organization_id;

			// File Admins Always have Read Access
			if ($user->can("manage storage files")) return true;

			// Fetch Repository Privilege Settings
			$privileges = new \Resource\PrivilegeList();
			$privileges->fromJSON($this->default_privileges_json);

			// Access for All
			$privilege = $privileges->privilege('a');
			if ($privilege->read) return true;

			// Access for User
			$privilege = $privileges->privilege('u',$user_id);
			if ($privilege->read) return true;

			// Access for Organization
			$privilege = $privileges->privilege('o',$organization_id);
			if ($privilege->read) return true;
	
			// Access for Roles
			$roles = $user->roles();
			foreach ($roles as $role) {
				$role_id = $role->id;
				$privilege = $privileges->privilege('r',$role_id);
				if ($privilege->read) return true;
			}
			return false;
		}
		/**
		 * Check if user has write access to repository
		 * @param mixed $user_id 
		 * @return bool 
		 */
		public function writable($user_id = null) {
			// Default User to current session
			if (!isset($user_id)) $user_id = $GLOBALS['_SESSION_']->customer->id;

			// Fetch User and Organization Records
			$user = new \Register\Customer($user_id);
			$organization_id = $user->organization_id;

			// File Admins Always have Write Access
			if ($user->can("manage storage files")) return true;

			// Fetch Repository Privilege Settings
			$privileges = new \Resource\PrivilegeList();
			$privileges->fromJSON($this->default_privileges_json);

			// Access for All
			$privilege = $privileges->privilege('a');
			if ($privilege->write) return true;

			// Access for User
			$privilege = $privileges->privilege('u',$user_id);
			if ($privilege->write) return true;

			// Access for Organization
			$privilege = $privileges->privilege('o',$organization_id);
			if ($privilege->write) return true;
	
			// Access for Roles
			$roles = $user->roles();
			foreach ($roles as $role) {
				$role_id = $role->id;
				$privilege = $privileges->privilege('r',$role_id);
				if ($privilege->write) return true;
			}
			return false;
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
