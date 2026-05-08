<?php
	namespace Storage;

	abstract class Repository Extends \BaseModel {
		public string $name = "";						// Name of Repository
		public string $type = "";						// Type of Repository - S3, Local, etc.
		public ?string $code = null;					// Unique Code for Repository
		public string $status = 'NEW';					// Status of Repository - NEW, ACTIVE, DISABLED
		public string $endpoint = "";					// Endpoint for Repository
		public string $secretKey = "";					// Secret Key for AWS Repository
		public string $accessKey = "";					// Access Key for AWS Repository
		public string $default_privileges_json = "";	// JSON string representing default privileges
		public string $override_privileges_json = "";	// JSON string representing override privileges
		protected bool $_connected = false;				// Whether or not the repository has been connected to (for remote repositories)

		/**
		 * Class Constructor
		 * @param int Optional 
		 * @return void 
		 */
		public function __construct($id = 0) {
			$this->_tableName = 'storage_repositories';
			$this->_metaTableName = 'storage_repository_metadata';
			$this->_tableMetaFKColumn = 'repository_id';
			$this->_tableMetaKeyColumn = 'key';
			$this->_addTypes('s3','local','drive','dropBox');
			parent::__construct($id);
		}

		/** @method add($parameters)
		 * Create a new Storage Repository
		 * Parameters must include type
		 * Parameters may include code, name, status, default_privileges_json, override_privileges_json
		 * code will be randomly generated if not provided
		 * status will default to NEW if not provided
		 * @param array $parameters
		 * @return bool - True if successfully added
		 */
		public function add($parameters = []) {
			// Clear any previous errors
			$this->clearError();

			// Generate Unique Code if none provided
			$porkchop = new \Porkchop();
			if (! isset($parameters['code']) || ! strlen($parameters['code'])) $parameters['code'] = $porkchop->biguuid();

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

			// Bind Parameters for Query
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
			$this->id = $database->Insert_ID();

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

		/** @method update($parameters)
		 * Update Currently Selected Repository Record
		 * Parameters can include name, status, default_privileges_json, override_privileges_json
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

			if (isset($parameters['default_privileges_json']) && $parameters['default_privileges_json'] == "[]") {
				$parameters['default_privileges_json'] = "";
			}
			else if (isset($parameters['default_privileges_json']) && !empty($parameters['default_privileges_json'])) {
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

			// Instance specific metadata
			$keys = $this->getImpliedMetadataKeys();
			foreach ($keys as $key) {
				if (isset($parameters[$key]) && $parameters[$key] != $this->getMetadata($key)) {
					if ($this->validMetadata($key,$parameters[$key])) {
						app_log("Setting metadata key '$key' to value '" . $parameters[$key] . "' for repository " . $this->id, 'debug');
						$this->setMetadata($key,$parameters[$key]);
						$auditLog->add(array(
							'instance_id' => $this->id,
							'description' => 'Updated '.$this->_objectName(),
							'class_name' => get_class($this),
							'class_method' => 'update'
						));
					}
					else {
						$this->error("Invalid metadata value for $key");
						return false;
					}
				}
			}

			// Populate Object Variables with latest data
			return $this->details();
		}

		/** @method details()
		 * Fetch Repository Record and Populate Object Variables
		 * @return bool - True if successful, false if failed
		 */
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
			if ($object->id) {
				$this->name = strval($object->name);
				$this->type = strval($object->type);
				$this->code = $object->code;
				$this->status = strval($object->status);
				$this->default_privileges_json = strval($object->default_privileges);
				$this->override_privileges_json = strval($object->override_privileges);
			}
			else {
				$this->id = 0;
				$this->name = "";
				$this->type = "";
				$this->code = null;
				$this->status = "";
				$this->default_privileges_json = "";
				$this->override_privileges_json = "";
			}
			return true;
		}

		/** @method connect()
		 * Abstract method to connect to the repository (for remote repositories)
		 * For local repositories, this will simply check that the path exists and is writable
		 * For remote repositories, this will attempt to connect to the remote service and verify credentials
		 * If connection is successful, set $this->_connected to true
		 * @return bool - True if connection successful or not required, false if connection failed
		 */
		abstract public function connect();

		/** @method connected()
		 * Check if repository is connected (for remote repositories)
		 */
		public function connected() {
			return $this->_connected;
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

		/** @method getInstance()
		 * Get the current instance of the Repository
		 * @return \Storage\Repository - Current Repository instance
		 */
		public function getInstance(): \Storage\Repository {
			$repositoryFactory = new RepositoryFactory();
			if ($this->id) {
				if (strtolower($this->type) == 'local') {
					return $repositoryFactory->createWithID($this->id);
				}
				elseif (strtolower($this->type) == 's3') {
					return $repositoryFactory->createWithID($this->id);
				}
				else {
					$this->error("Invalid repository type: ". $this->type);
					return $repositoryFactory->create('Validation');
				}
			}
			else {
				$this->error("Repository not found");
				return $repositoryFactory->create('Validation');
			}
		}

		/** @method uploadFile($uploadedFile,$path)
		 * Add File to Repository
		 * @param PHP Uploaded File, single element of $_FILES array
		 * @param string $source
		 * @return Storage\File instance
		 */
		public function uploadFile($uploadedFile,$path = '/') {
			if (! $this->id) {
				$this->error("Repository not initialized");
				return null;
			}

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
				$existing_files = $filelist->find(
					array(
						'repository_id' => $this->id,
						'path'	=> $path,
						'name' => $uploadedFile['name'],
					)
				);

				$existing = null;
				if (!empty($existing_files)) {
					$existing = $existing_files[0];
				}

				if (isset($existing) && isset($existing->id) && $existing->id) {
					$this->error("File already exists with that name at path ".$path." in repo ".$this->name);
					return null;
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

		/** @method addFile($file, $path)
		 * Abstract method to add a file from the filesystem to the repository
		 * @param \Storage\File $file - File object representing the file to be added
		 * @param string $path - Path within the repository to add the file to
		 * @return bool - True on successful addition, false on failure
		*/
		abstract public function addFile($file, $path): bool;

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
				// Initialize Database Service
				$database = new \Database\Service();

				// First delete any metadata
				$delete_metadata_query = "DELETE FROM storage_file_metadata WHERE file_id = ?";
				$database->AddParam($file_id);
				$database->Execute($delete_metadata_query);

				// First delete any references in object_images table

				$delete_refs_query = "DELETE FROM object_images WHERE image_id = ?";
				$database->AddParam($file_id);
				$database->Execute($delete_refs_query);

				if ($database->ErrorMsg()) {
					$this->error("Error removing image references: " . $database->ErrorMsg());
					return false;
				}				
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

		/** @method directories(path)
		 * List Existing Directories
		 * @param string $path - Optional, defaults to '/'
		 * @return array - Array of Directory Objects
		 */
		public function directories($path = "/") {
			$directorylist = new DirectoryList();
			return $directorylist->find(array('repository_id' => $this->id,'path' => $path));
		}

		/** @method getFileFromPath(path)
		 * Get a file from the repository by its path.
		 * @param string $path The path of the file.
		 * @return \Storage\File The file object.
		 */
		public function getFileFromPath($path) {
			$file = new \Storage\File();
			return $file->fromPath($this->id,$path);
		}

		/** @method default_privileges()
		 * Get the default privileges for the repository.
		 * @return \Resource\PrivilegeList The default privileges.
		 */
		public function default_privileges() {
			$privileges = new \Resource\PrivilegeList();
			return $privileges->fromJSON($this->default_privileges_json);
		}

		/** @method override_privileges()
		 * Get the override privileges for the repository.
		 * @return \Resource\PrivilegeList The override privileges.
		 */
		public function override_privileges() {
			$privileges = new \Resource\PrivilegeList();
			return $privileges->fromJSON($this->override_privileges_json);
		}

		/****************************************/
		/** @section Access Control Functions	*/
		/****************************************/
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
			header("X-Storage-Admin: No");
			// Fetch Repository Privilege Settings
			$privileges = new \Resource\PrivilegeList();
			$privileges->fromJSON($this->default_privileges_json);

			// Access for All
			$privilege = $privileges->privilege('a');
			if ($privilege->read) return true;

			// Access for Authenticated User
			$privilege = $privileges->privilege('t',$user_id);
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

			// Access for Authenticated User
			$privilege = $privileges->privilege('t');
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

		/****************************************/
		/** @section Validation Functions 		*/
		/****************************************/
		public function validMetadata($key,$value) {
			if (! in_array($key,$this->_metadataKeys())) {
				$this->error("Invalid metadata key");
				return false;
			}
			if ($key == "path" && ! $this->validPath($value)) {
				$this->error("Invalid path");
				return false;
			}
			elseif ($key == "accessKey" && ! $this->validAccessKey($value)) {
				$this->error("Invalid access key");
				return false;
			}
			elseif ($key == "secretKey" && ! $this->validSecretKey($value)) {
				$this->error("Invalid secret key");
				return false;
			}
			elseif ($key == "bucket" && ! $this->validBucket($value)) {
				$this->error("Invalid bucket '$value'");
				return false;
			}
			elseif ($key == "region" && ! $this->validRegion($value)) {
				$this->error("Invalid region");
				return false;
			}
			elseif ($key == "endpoint" && ! $this->validEndpoint($value)) {
				$this->error("Invalid endpoint");
				return false;
			}
			return true;
		}

		public function validName($string): bool {
			if (preg_match('/^\w[\w\-\_\.\s]*$/',$string)) return true;
			return false;
		}
		public function validStatus($string): bool {
			if (preg_match('/^(NEW|ACTIVE|DISABLED)$/i',$string)) return true;
			return false;
		}
		public function validType($string): bool {
			if (preg_match('/^(Local|s3|google|Drive|DropBox)$/i',$string)) return true;
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
