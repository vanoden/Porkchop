<?php

namespace Storage;

use Register\Customer;
use Storage\Repository\Local;
use Storage\Repository\S3;

class File extends \BaseModel {

	private $_repository_id;			// Repository ID that file belongs to
	public $code;						// Unique code for file
	public $display_name;				// Name to be displayed in UI
	public $description;				// Description of file
	public $date_created;				// Date file was uploaded/created
	public bool $success = true;
	public $uri;
	public $read_protect;
	public $write_protect;
	public $mime_type;
	public $size;
	public $timestamp;
	public $name;
	private $user_id;
	public $path;
	public $view_order;
	public $label;
	private $original_name;
	private $access_privilege_json;
	
	/**
	 * Class Constructor
	 * @param int $id - Optional ID of the file to load
	 * @return void
	 */
	public function __construct($id = 0) {
		$this->_tableName = 'storage_files';
		parent::__construct($id);
	}

	/**
	 * Add a new file to the storage_files table
	 * Supported parameters are code, repository_id, name, mime_type, size, path, display_name, description, access_privileges
	 * @param array $parameters - Array of parameters to add to the table
	 * @return bool - True if successful, False if not
	 */
	public function add($parameters = []): bool {
		// Clear any previous errors
		$this->clearError();

		// Generate unique code if no code provided
		if (! isset($parameters['code']) || ! strlen($parameters['code'])) $parameters['code'] = uniqid();

		// Validate the code
		if (! preg_match('/^[\w\-\.\_]+$/', $parameters['code'])) {
			$this->error("Invalid code '" . $parameters['code'] . "'");
			return false;
		}

		// Validate the repository ID
		if (! isset($parameters['repository_id']) || ! is_numeric($parameters['repository_id'])) {
			$this->error("Invalid repository_id '" . $parameters['repository_id'] . "'");
			return false;
		}
		$repository = new \Storage\Repository($parameters['repository_id']);
		if (! $repository->id) {
			app_log("Repository not found for file upload: " . $parameters['repository_id'], 'error');
			app_log(print_r(debug_backtrace(), true), 'notice');
			$this->error("Repository not found for file upload");
			return false;
		}

		// Validate the mime type
		$this->code = $parameters['code'];
		if (! $this->_valid_type($parameters['mime_type'])) {
			$this->error("Invalid mime_type '" . $parameters['mime_type'] . "'");
			return false;
		}

		// Default Original Name frmo Current Name
		if (! $parameters['original_name']) $parameters['original_name'] = $parameters['name'];
		// Default Path is '/'
		if (! $parameters['path']) $parameters['path'] = '/';

		// Prepare Query
		$database = new \Database\Service();
		$add_object_query = "
				INSERT
				INTO	storage_files
				(		code,repository_id,name,mime_type,size,date_created,user_id,path)
				VALUES
				(		?,?,?,?,?,sysdate(),?,?)
			";
		$database->AddParam($parameters['code']);
		$database->AddParam($parameters['repository_id']);
		$database->AddParam($parameters['name']);
		$database->AddParam($parameters['mime_type']);
		$database->AddParam($parameters['size']);
		$database->AddParam($GLOBALS['_SESSION_']->customer->id);
		$database->AddParam($parameters['path']);

		// Execute Query
		$database->trace(9);
		$database->Execute($add_object_query);

		// Check for Errors
		if ($database->ErrorMsg()) {
			$this->SQLError($database->ErrorMsg());
			return false;
		}

		// Fetch Autoincremented ID
		$this->id = $GLOBALS['_database']->Insert_ID();

		// audit the add event
		$auditLog = new \Site\AuditLog\Event();
		$auditLog->add(array(
			'instance_id' => $this->id,
			'description' => 'Added new ' . $this->_objectName(),
			'class_name' => get_class($this),
			'class_method' => 'add'
		));

		app_log("File '" . $this->id . "' uploaded");
		return $this->update($parameters);
	}

	/**
	 * Update the current file's parameters
	 * Supported parameters are display_name, description, path, access_privileges
	 * @param array $parameters
	 * @return bool 
	 */
	public function update($parameters = []): bool {

		// Clear any previous errors
		$this->clearError();

		// Prepare Query
		$database = new \Database\Service();
		$update_object_query = "
				UPDATE	storage_files
				SET		id = id";

		if (isset($parameters['display_name'])) {
			$update_object_query .= ",
						display_name = ?";
			$database->AddParam($parameters['display_name']);
		}

		if (isset($parameters['description'])) {
			$update_object_query .= ",
						description = ?";
			$database->AddParam($parameters['description']);
		}

		if (isset($parameters['path'])) {
			$update_object_query .= ",
						path = ?";
			$database->AddParam($parameters['path']);
		}

		if (isset($parameters['access_privileges'])) {
			$update_object_query .= ",
						access_privileges = ?";
			$database->AddParam($parameters['access_privileges']);
		}

		$update_object_query .= "
				WHERE	id = ?
			";
		$database->AddParam($this->id);

		// Execute Query
		$database->Execute($update_object_query);

		// Check for Errors
		if ($database->ErrorMsg()) {
			$this->SQLError($database->ErrorMsg());
			return false;
		}

		// audit the update event
		$auditLog = new \Site\AuditLog\Event();
		$auditLog->add(array(
			'instance_id' => $this->id,
			'description' => 'Updated ' . $this->_objectName(),
			'class_name' => get_class($this),
			'class_method' => 'update'
		));

		return $this->details();
	}

	/**
	 * Get details of the current file
	 * @return bool
	 */
	public function details(): bool {

		// Clear any previous errors
		$this->clearError();

		// Prepare Query
		$database = new \Database\Service();
		$get_object_query = "
				SELECT	*
				FROM	storage_files
				WHERE	id = ?
			";
		$database->AddParam($this->id);

		// Execute Query
		$rs = $database->Execute($get_object_query);

		// Check for Errors
		if (! $rs) {
			$this->SQLError($database->ErrorMsg());
			return false;
		}

		// Fetch result row as object
		$object = $rs->FetchNextObject(false);
		if ($object->id) {
			$this->code = $object->code;
			$this->name = $object->name;
			$this->path = $object->path;
			if (!empty($object->display_name)) {
				$this->display_name = $object->display_name;
			}
			elseif (!empty($object->name)) {
				$this->display_name = $object->name;
			}
			else {
				$this->display_name = $object->code;
			}
			$this->description = $object->description;
			$this->mime_type = $object->mime_type;
			$this->size = $object->size;
			$this->user_id = $object->user_id;
			$this->date_created = $object->date_created;
			$this->timestamp = $this->timestamp ? strtotime($this->timestamp) : null;
			$this->_repository_id = $object->repository_id;
			$this->access_privilege_json = $object->access_privileges;
		}
		else {
			$this->id = 0;
			$this->code = null;
		}

		return true;
	}

	/**
	 * Return the files owner as object
	 * @return Customer 
	 */
	public function owner() {
		return new \Register\Customer($this->user_id);
	}

	/**
	 * Get/Set File Name
	 * @param string $name - Optional
	 * @return string|null
	 */
	public function name($name = '') {
		if (strlen($name)) {
			if (! preg_match('/^[\w\-\.\_\s]+$/', $name)) {
				$this->error("Invalid File Name");
				return null;
			} else {
				$this->name = $name;
			}
		}
		return $this->name;
	}

	/**
	 * Get the file's URI, combines repository endpoint and file name
	 * Used for downloading the file from static storage, ie AWS S3
	 * @return string 
	 */
	public function uri() {
		return $this->repository()->endpoint . "/" . $this->name;
	}

	/**
	 * Path of the file as designated by the database
	 * Not the real file path
	 * @return mixed 
	 */
	public function path() {
		return $this->path;
	}

	/**
	 * Get file from specified path
	 * @param mixed $repository_id 
	 * @param mixed $path 
	 * @return null|File
	 */
	public function fromPath($repository_id, $path) {
		// Clear any previous errors
		$this->clearError();

		// Parse Given Path
		if (preg_match('/^(.*)\/([^\/]+)/', $path, $matches)) {
			$path = $matches[1];
			$file = $matches[2];
		} elseif (!empty($path)) {
			$file = $path;
			$path = "";
		} else {
			$this->error("Path required");
			return null;
		}
		return $this->fromPathName($repository_id, $path, $file);
	}

	/**
	 * Get File Using Repository id, Path and Name
	 * @param int $repository_id
	 * @param mixed $path
	 * @param mixed $name
	 * @return null|File
	 */
	public function fromPathName($repository_id, $path, $name) {
		$this->clearError();

		// Prepare Query
		$database = new \Database\Service();
		$get_file_query = "
				SELECT	id
				FROM	storage_files
				WHERE	repository_id = ?
				AND		name = ?
			";
		$database->AddParam($repository_id);
		$database->AddParam($name);
		if (!empty($path)) {
			$get_file_query .= "
				AND		path = ?";
			$database->AddParam($path);
		}
		$database->trace(9);
		// Execute Query
		$rs = $database->Execute($get_file_query);

		// Check for Errors
		if (! $rs) {
			$this->SQLError($database->ErrorMsg());
			return null;
		}

		// Fetch first column of result row as file id
		list($file_id) = $rs->FetchRow();
		if (!empty($file_id)) {
			$this->id = $file_id;
			// Get Details from Database
			$this->details();

			// Return Storage\File instance with returned id
			return new \Storage\File($file_id);
		} else {
			return null;
		}
	}

	/**
	 * Was the requested file found
	 * @return bool
	 */
	public function exists($exists = null): bool {
		if ($this->id) return true;
		return false;
	}

	/**
	 * Getter for Repository ID
	 * @param int $id - ID of file's repository
	 * @return mixed 
	 */
	public function repository_id($id = 0) {
		if ($id > 0) {

			// Get Repository 
			$repository = new Repository($id);
			if (! $repository->id) {
				$this->error("Repository not found?");
				return false;
			} else {
				$this->_repository_id = $id;
			}
		}
		return $this->_repository_id;
	}

	/**
	 * Getter for Repository
	 * @return instance of file's Storage/Repository 
	 */
	public function repository() {
		$repository = new \Storage\Repository($this->_repository_id);
		return $repository->getInstance();
	}

	/**
	 * DEPRECATED - Use validMimeType()
	 * Check if the file type is valid and supported
	 * @param string mime type of file
	 * @return bool
	 */
	private function _valid_type($name) {
		return $this->validMimeType($name);
	}

	/**
	 * Validate the MIME type of the file
	 * @param string $mime_type - MIME type of the file
	 * @return bool - True if valid, False if not
	 */
	public function validMimeType($mime_type) {
		if (preg_match('/^(image|application|text|video)\/(png|jpg|jpeg|gif|tif|tiff|plain|html|csv|cs|js|xml|json|gzip|tar\+gzip|pdf|octet\-stream|mp4|vcard)$/', $mime_type)) return true;
		return false;
	}

	/**
	 * DEPRECATED!!! User Resource\Privilege grant instead
	 * Grant an entity access to the resource
	 * @param mixed $type - Type of Entity
	 * 		a = All
	 * 		o = Organization
	 * 		u = User
	 * 		r = Role
	 * @param mixed $id - ID of the entity
	 * @param mixed $level - Level of Access
	 * 		r = Read
	 * 		w = Write
	 * @return bool 
	 */
	public function grant($type, $id, $level) {
		// We only care about the first character
		$type = substr($type, 0, 1);

		// Validate the Entity Type
		if (!in_array($type, array('a', 'o', 'u', 'r'))) {
			$this->error("Invalid level");
			return false;
		}

		// We only care about the first character
		$level = substr($level, 0, 1);

		// Validate the Access Level
		if (!in_array($level, array('r', 'w'))) {
			$this->error("Invalid type $level");
			return false;
		}

		// Get Existing Privileges
		$privileges = $this->getPrivileges();
		if ($type == 'a') {
			// Entity Type is All, so we only have one entity
			if (!preg_match("/$level/", $privileges->a)) $privileges->a .= $level;
		} else {
			// Entity Type is not All, so we have to specify the entity type and id
			if (!preg_match("/$level/", $privileges->$type->$id)) $privileges->$type->$id .= $level;
		}
		return $this->update(array('access_privileges' => json_encode($privileges)));
	}

	/**
	 * DEPRECATED!!! Use Resource\Privilege grant() instead
	 * Revoke an entity's access to the resource
	 * @param mixed $type - Type of Entity
	 * 		a = All
	 * 		o = Organization
	 * 		u = User
	 * 		r = Role
	 * @param mixed $id - ID of the entity
	 * @param mixed $level - Level of Access
	 * 		r = Read
	 * 		w = Write
	 * @return bool 
	 */
	public function revoke($type, $id, $level) {
		$type = substr($type, 0, 1);
		if (!in_array($type, array('a', 'o', 'u', 'r'))) {
			$this->error("Invalid level");
			return false;
		}
		$level = substr($level, 0, 1);
		if (!in_array($level, array('r', 'w'))) {
			$this->error("Invalid type $level");
			return false;
		}

		// Get Existing Privileges
		$privileges = $this->getPrivileges();
		if ($type == 'a') {
			$privileges->$type = preg_replace("/$level/", "", $privileges->$type);
		} else {
			$privileges->$type->$id = preg_replace("/$level/", "", $privileges->$level->$id);
		}
		return $this->update(array('access_privileges' => json_encode($privileges)));
	}

	/**
	 * Check if the current user has read access to the file
	 * @param mixed $user_id - ID of the user to check
	 * @return bool - True if user has read access, False if not
	 */
	public function readable($user_id = null) {
		if (!isset($user_id)) $user_id = $GLOBALS['_SESSION_']->customer->id;
		$user = new \Register\Customer($user_id);
		$organization_id = $user->organization_id;

		// File Managers always have access
		if ($user->can('manage storage files')) return true;
		header("X-File-Admin: No");

		// Owners always have access
		if ($this->user_id == $user_id) return true;
		header("X-File-Owner: No");

		// Get Repo Privileges
		$repository = $this->repository();
		if ($repository->readable($user_id)) return true;

		// Get File Privileges
		$file_privileges = $this->getPrivileges();

		foreach ($file_privileges as $privilege) {
			// Check 'All' Privileges
			if ($privilege->entity_type == 'a' && $privilege->read == 1) return true;

			// Check 'Authenticated' Privileges
			if ($privilege->entity_type == 't' && $user_id > 0 && $privilege->read == 1) return true;

			// Check 'User' Privileges
			if ($privilege->entity_type == 'u' && $privilege->entity_id == $user_id && $privilege->read == 1) return true;

			// Check 'Organization' Privileges
			if ($privilege->entity_type == 'o' && $privilege->entity_id == $organization_id && $privilege->read == 1) return true;

			// Check 'Role' Privileges
			$roles = $user->roles();
			foreach ($roles as $role) {
				$role_id = $role->id;
				if ($privilege->entity_type == 'r' && $privilege->entity_id == $role_id && $privilege->read == 1) return true;
			}
		}

		return false;
	}

	/**
	 * Check if the current user has write access to the file
	 * @param mixed $user_id - ID of the user to check
	 * @return bool - True if user has write access, False if not
	 */
	public function writable($user_id = null) {
		if (!isset($user_id)) $user_id = $GLOBALS['_SESSION_']->customer->id;
		$user = new \Register\Customer($user_id);
		$organization_id = $user->organization_id;

		// File Managers always have access
		if ($user->can('manage storage files')) return true;

		// Owners always have access
		if ($this->user_id == $user_id) return true;

		// Get Repo Privileges
		$repository = $this->repository();
		if ($repository->writable($user_id)) return true;

		// Get File Privileges
		$file_privileges = $this->getPrivileges();

		foreach ($file_privileges as $privilege) {
			// Check 'All' Privileges
			if ($privilege->entity_type == 'a' && $privilege->write == 1) return true;

			// Check 'Authenticated' Privileges
			if ($privilege->entity_type == 't' && $user_id > 0 && $privilege->write == 1) return true;

			// Check 'User' Privileges
			if ($privilege->entity_type == 'u' && $privilege->entity_id == $user_id && $privilege->write == 1) return true;

			// Check 'Organization' Privileges
			if ($privilege->entity_type == 'o' && $privilege->entity_id == $organization_id && $privilege->write == 1) return true;

			// Check 'Role' Privileges
			$roles = $user->roles();
			foreach ($roles as $role) {
				$role_id = $role->id;
				if ($privilege->entity_type == 'r' && $privilege->entity_id == $role_id && $privilege->write == 1) return true;
			}
		}

		// Default no access
		return false;
	}

	/**
	 * Load the file from its repository and send to browser with appropriate headers
	 * to allow local storage
	 * @return void 
	 */
	public function download() {
		$repository = $this->repository();
		$repository->retrieveFile($this);
		$this->error($repository->error());
	}

	/** @method content()
	 * Get the content of the file
	 * @return string - Content of the file
	 */
	public function content() {
		$repository = $this->repository();
		$repository->content($this);
		$this->error($repository->error());
		return $repository->content();
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
		return '/_storage/file/' . $this->code . '/download';
	}

	public function inodeExists() {
		if ($this->id && file_exists($this->path())) return true;
		return false;
	}

	/**
	 * Upload a file to the repository
	 * @param array $parameters - Array of parameters to upload the file
	 * @return bool - True if successful, False if not
	 */
	public function upload($parameters): bool {
		// make sure we have a file present in the request to upload it
		if (empty($_FILES)) $this->addError("No file was selected to upload");

		// make sure all the required parameters are set for upload to continue
		if (! preg_match('/^\//', $parameters['path'])) $parameters['path'] = '/' . $parameters['path'];

		// Load the repository based on the parameters provided
		if (!empty($parameters['repository_id'])) {
			$repository = new \Storage\Repository($parameters['repository_id']);
		}
		elseif (!empty($parameters['repository_code'])) {
			$repository = new \Storage\Repository();
			$repository->get($parameters['repository_code']);
		}
		elseif (!empty($parameters['repository_name'])) {
			$factory = new \Storage\RepositoryFactory();
			$repository = $factory->find($parameters['repository_name']);
			if ($factory->error()) {
				$this->addError("Error loading repository: " . $factory->error());
				return false;
			}
		}
		else {
			$this->addError("Repository not specified");
			return false;
		}

		if ($repository->error()) {
			$this->addError("Error loading repository: " . $repository->error());
			return false;
		}
		elseif (!$repository->exists()) {
			$this->addError("Repository not found");
		}
		else {
			app_log("Identified repo '" . $repository->name . "'");

			if (! file_exists($_FILES['uploadFile']['tmp_name'])) {
				if (empty($_FILES['uploadFile']['tmp_name']) && empty($_FILES['uploadFile']['name'])) {
					$this->addError("No file was selected to upload");
				} else if (empty($_FILES['uploadFile']['tmp_name']) && !empty($_FILES['uploadFile']['name'])) {
					$this->addError("File chosen was file too large: php.ini upload_max_filesize=" . ini_get('upload_max_filesize'));
				} else {
					$this->addError("Temp file '" . $_FILES['uploadFile']['tmp_name'] . "' not found");
				}
			}
			else {

				// Check for Conflict 
				$filelist = new \Storage\FileList();
				list($existing) = $filelist->find(
					array(
						'repository_id' => $repository->id,
						'path'	=> $parameters['path'],
						'name' => $_FILES['uploadFile']['name'],
					)
				);

				if ($existing->id) {
					$this->addError("File already exists with that name in repository: " . $repository->name);
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
							$this->addError('Unable to add file to repository: ' . $repository->error());
						} else {
							app_log("Stored file " . $this->id . " at " . $repository->path() . "/" . $this->code);
							$this->success = "File uploaded";

							// add file type refrence for this file to be a part of the support/engineering ticket
							if (isset($parameters['type']) && isset($parameters['ref_id'])) {
								$this->addMetadata($parameters['type'], $parameters['ref_id']);
							}
						}
					} catch (\Exception $e) {
						$this->delete();
						$this->addError('System Exception has occured, unable to add file to repository: ' . $repository->error());
						app_log("repository->addFile(): Exception" . $e->getMessage(), 'notice');
						return false;
					}
				}
			}
		}
		return true;
	}

	/** @method writePermitted(user id)
	 * Check if the current user has write access to the file
	 * @param mixed $user_id - ID of the user to check
	 */
	public function writePermitted($user_id = null): bool {
		return $this->writable($user_id);
	}

	/** @method readPermitted(user id)
	 * Check if the current user has read access to the file
	 * @param mixed $user_id - ID of the user to check
	 * @return bool - True if user has read access, False if not
	 */
	public function readPermitted($user_id = null): bool {
		return $this->readable($user_id);
	}

	/** @method getPrivileges()
	 * Get the privileges for the current file
	 * Privileges are stored as a JSON object
	 * @return mixed - Associative Array of Entities and Privilege Levels
	 */
	public function getPrivileges() {
		$privilegeList = new \Resource\PrivilegeList();
		return $privilegeList->fromJSON($this->access_privilege_json);
	}

	/** @method privilegeList()
	 * Return multi-dimensional array of privileges for the current file
	 * @return object[][] 
	 */
	public function privilegeList() {
		// Get the privileges for the file
		return $this->getPrivileges();
	}

	public function updatePrivilegesDontUse($object_type, $object_id, $mask) {
		app_log("Updating privileges on " . $this->code, 'info');
		$privileges = new \stdClass();
		if (!empty($this->access_privilege_json)) {
			$privileges = json_decode($this->access_privilege_json);
		}
		if (preg_match('/^a/i', $object_type)) $object_type = 'a'; // All/Everyone
		elseif (preg_match('/^o/i', $object_type)) $object_type = 'o'; // Organization
		elseif (preg_match('/^r/i', $object_type)) $object_type = 'r'; // Role
		elseif (preg_match('/^u/i', $object_type)) $object_type = 'u'; // User/Customer
		else {
			$this->error("Invalid permision object type");
			return false;
		}

		if (!is_object($privileges->$object_type)) $privileges->$object_type = new \stdClass();
		if (!is_array($privileges->$object_type->$object_id)) $privileges->$object_type->$object_id = array();
		foreach (array('w', 'r', 'g') as $priv) {
			app_log($priv, 'info');
			if (in_array('+' . $priv, $mask) && !in_array($priv, $privileges->$object_type->$object_id)) {
				app_log("Adding $priv to " . $this->name, 'info');
				array_push($privileges->$object_type->$object_id, $priv);
			} elseif (in_array('-' . $priv, $mask)) {
				$idx = array_search($priv, $privileges->$object_type->$object_id, true);
				if (is_numeric($idx)) {
					app_log("IDX = $idx", 'info');
					app_log("Dropping $priv from " . $this->name, 'info');
					unset($privileges->$object_type->$object_id[$idx]);
					\array_values($privileges->$object_type->$object_id);
				}
			} elseif (in_array('-' . $priv, $mask)) {
				app_log(" => " . print_r($privileges->$object_type->$object_id, false), 'info');
				app_log("Nothing for $priv");
			}
		}
		app_log("Writing " . json_encode($privileges) . " to " . $this->name, 'info');
		return $this->update(array('access_privileges' => json_encode($privileges)));
	}

	public function validPath($string): bool {
		// Only a virtual path!
		if (preg_match('/\.\./', $string)) return false;
		if (preg_match('/\/\//', $string)) return false;
		if ($string == '/') return true;
		if (preg_match('/^\/[\w\-\.\_\/]*$/', $string)) return true;
		else return false;
	}

	/**
	 * Validate the file extension - Only extensions listed here are supported
	 * @param string $string - File extension to validate
	 * @return bool - True if valid, False if not
	 */
	public function validExtension($string) {
		// Images
		if (preg_match('/^(png|jpg|jpeg|gif|tif)$/', $string)) return true;
		// Documents
		if (preg_match('/^(doc|docx|pdf|xls|xlsx|ppt|pptx|txt|rtf|html|htm|css|xml|json|csv|odt|ods)$/', $string)) return true;
		// Media
		if (preg_match('/^(mp3|mp4|mov|avi|wmv)$/', $string)) return true;
		// Archives
		if (preg_match('/^(zip|tar|gz|tgz|bz2|rar)$/', $string)) return true;

		return false;
	}
}
