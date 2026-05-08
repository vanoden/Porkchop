<?php
	namespace Package;

	class Package Extends \BaseModel {
		public $repository_id;
		public $code;
		public $name;
		public $license;
		public $description;
		public $status;
		public $date_created;
		public $timestamp;
		public $platform;
		public $package_version_id;

		public function __construct($id = 0) {
			$this->_tableName = 'package_packages';
			$this->_addStatus(array('TEST','ACTIVE','HIDDEN'));
    		parent::__construct($id);
		}

		public function add($parameters = []) {

			# Authorization Required
			if (! $GLOBALS['_SESSION_']->customer->can('manage packages')) {
				$this->error("Must be an authorized package manager to upload files");
				return false;
			}

			# Validation
			if (! $this->validName($parameters['name'])) {
				$this->error("Valid name required");
				return false;
			}

			# See If Name Already Used
			$packagelist = new PackageList();
			$packagelist->find(array('name' => $parameters['name']));
			if ($packagelist->count() > 0) {
				$this->error("A package already exists by that name");
				return false;
			}

			if (! isset($parameters['code'])) {
				$parameters['code'] = uniqid();
			}

			if (! $this->validCode($parameters['code'])) {
				$this->error("Invalid code");
				return false;
			}

			$factory = new \Storage\RepositoryFactory();
			$repository = $factory->createWithID($parameters['repository_id']);
            if (! $repository->exists()) {
                $this->error("Repository not found");
                return false;
            }

			$add_object_query = "
				INSERT
				INTO	package_packages
				(		code,
						name,
						owner_id,
						date_created,
						status,
						repository_id
				)
				VALUES
				(		?,?,?,sysdate(),'ACTIVE',?)
			";

			$bind_params = array(
				$parameters['code'],
				$parameters['name'],
				$GLOBALS['_SESSION_']->customer->id,
				$parameters['repository_id']
			);

			query_log($add_object_query,$bind_params);

			$GLOBALS['_database']->Execute($add_object_query,$bind_params);
			if ($GLOBALS['_database']->ErrorMsg()) {
				$this->SQLError($GLOBALS['_database']->ErrorMsg());
				return false;
			}

			$this->id = $GLOBALS['_database']->Insert_ID();

			// add audit log
			$auditLog = new \Site\AuditLog\Event();
			$auditLog->add(array(
				'instance_id' => $this->id,
				'description' => 'Added new '.$this->_objectName(),
				'class_name' => get_class($this),
				'class_method' => 'add'
			));

			return $this->update($parameters);
		}

		public function update($parameters = []): bool {
			if (! $this->id) {
				$this->error("Must identify package first");
				return false;
			}

			$bind_params = array();
			$update_object_query = "
				UPDATE	package_packages
				SET		id = id
			";

			if (isset($parameters['status']) && $this->validStatus($parameters['status'])) {
				$parameters['status'] = strtoupper($parameters['status']);
				if ($this->validStatus($parameters['status'])) {
					$update_object_query .= ",
						status = ?";
					array_push($bind_params,$parameters['status']);
				}
				else {
					$this->error("Invalid package status '".$parameters['status']."'");
					return false;
				}
			}
			if (isset($parameters['name']) && $this->validName($parameters['name'])) {
				$update_object_query .= ",
						name = ?";
				array_push($bind_params,$parameters['name']);
			}
			if (isset($parameters['license']) && strlen($parameters['license'])) {
				$update_object_query .= ",
						license = ?";
				array_push($bind_params,$parameters['license']);
			}
			if (isset($parameters['platform']) && strlen($parameters['platform'])) {
				$update_object_query .= ",
						platform = ?";
				array_push($bind_params,$parameters['platform']);
			}

			if (isset($parameters['description']) && strlen($parameters['description'])) {
				$update_object_query .= ",
						description = ?";
				array_push($bind_params,$parameters['description']);
			}
			if (isset($parameters['owner_id']) && is_numeric($parameters['owner_id'])) {
				$update_object_query .= ",
						owner_id = ?";
				array_push($bind_params,$parameters['owner_id']);
			}
			if (isset($parameters['package_version_id'])&& is_numeric($parameters['package_version_id'])) {
				$update_object_query .= ",
						package_version_id = ?";
				array_push($bind_params,$parameters['package_version_id']);
			}
			else {
				app_log("Package version not set");
			}
			$update_object_query .= "
				WHERE	id = ?
			";
			array_push($bind_params,$this->id);
			$GLOBALS['_database']->Execute($update_object_query,$bind_params);
			if ($GLOBALS['_database']->ErrorMsg()) {
				$this->SQLError($GLOBALS['_database']->ErrorMsg());
				return false;
			}

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

		public function details(): bool {
			$get_object_query = "
				SELECT	*,
						unix_timestamp(date_created) `timestamp`
				FROM	package_packages
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
			if (! $object) {
				$this->error("Package not found");
				return false;
			}
			$this->code = $object->code;
			$this->name = $object->name;
			$this->license = $object->license;
			$this->description = $object->description;
			$this->status = $object->status;
			$this->date_created = $object->date_created;
			$this->timestamp = $object->timestamp;
			$this->platform = $object->platform;
			$this->package_version_id = isset($object->package_version_id) ? $object->package_version_id : null;
			$this->repository_id = $object->repository_id;

			return true;
		}

		public function repository() {
			$factory = new \Storage\RepositoryFactory();
			return $factory->createWithID($this->repository_id);
		}

		public function latestVersion() {
			$get_object_query = "
				SELECT  id
				FROM    package_versions
				WHERE   package_id = ?
				AND     status = 'PUBLISHED'
				ORDER BY major DESC, minor DESC, build DESC
			";
			$rs = $GLOBALS['_database']->Execute(
				$get_object_query,
				array($this->id)
			);
			if (! $rs) {
				$this->SQLError($GLOBALS['_database']->ErrorMsg());
				return false;
			}
			list($this->id) = $rs->FetchRow();
			return new Version($this->id);
		}

		public function addVersion($parameters = array()) {
			$parameters['package_id'] = $this->id;
			$version = new Version();
			$result = $version->add($parameters);
            $this->error($version->error());
            return $result;
		}

		public function packageVersion() {
			return new \Package\Version($this->package_version_id);
		}

        public function validName($string): bool {
            if (preg_match('/\.\./',$string)) return false;
            if (preg_match('/^[\w\.\-\_\s]+$/',$string)) return true;
            else return false;
        }
	}
