<?php
	namespace Package;

	class Package {
		public $error;
		public $id;
		public $code;
		public $name;
		public $description;
		public $license;
		public $owner;
		public $status;
		public $date_created;
		public $timestamp;

		public function __construct($id = 0) {
			if ($id > 0) {
				$this->id = $id;
				$this->details();
			}
		}

		public function add($parameters = array()) {
			# Authorization Required
			if (! $GLOBALS['_SESSION_']->customer->can('manage packages')) {
				$this->error = "Must be an authorized package manager to upload files";
				return null;
			}

			# Validation
			if (! preg_match('/^[\w\-\_\.\s]+$/',$parameters['name'])) {
				$this->error = "Valid name required";
				return null;
			}

			# See If Name Already Used
			$packagelist = new PackageList();
			$packagelist->find(array('name' => $parameters['name']));
			if ($packagelist->count > 0) {
				$this->error = "A package already exists by that name";
				return null;
			}

			if (! isset($parameters['code'])) {
				$parameters['code'] = uniqid();
			}

			if (! preg_match('/^[\w\-\_\.]+$/',$parameters['code'])) {
				$this->error = "Invalid code";
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
				$this->error = "SQL Error in Package::Package::add(): ".$GLOBALS['_database']->ErrorMsg();
				return null;
			}

			$this->id = $GLOBALS['_database']->Insert_ID();

			return $this->update($parameters);
		}

		public function update($parameters = array()) {
			if (! $this->id) {
				$this->error = "Must identify package first";
				return null;
			}

			$bind_params = array();
			$update_object_query = "
				UPDATE	package_packages
				SET		id = id
			";

			if (isset($parameters['status']) && strlen($parameters['status'])) {
				$parameters['status'] = strtoupper($parameters['status']);
				if (preg_match('/^(NEW|ACTIVE|HIDDEN)$/',$parameters['status'])) {
					$update_object_query .= ",
						status = ?";
					array_push($bind_params,$parameters['status']);
				}
				else {
					$this->error = "Invalid package status '".$parameters['status']."'";
					return null;
				}
			}
			if (isset($parameters['name']) && strlen($parameters['name'])) {
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
				$this->error = "SQL Error in Package::Package::update(): ".$GLOBALS['_database']->ErrorMsg()."=>$update_object_query";
				return false;
			}
			return $this->details();
		}

		public function get($code) {
			$get_object_query = "
				SELECT	id
				FROM	package_packages
				WHERE	code = ?
			";
			$rs = $GLOBALS['_database']->Execute($get_object_query,array($code));
			if (! $rs) {
				$this->error = "SQL Error in Package::Package::get(): ".$GLOBALS['_database']->ErrorMsg();
				return false;
			}
			list($this->id) = $rs->FetchRow();
			if (! $this->id) {
				$this->error = "Package not found";
				return false;
			}
			return $this->details();
		}

		public function details() {
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
				$this->error = "SQL Error in Package::Package::details(): ".$GLOBALS['_database']->ErrorMsg();
				return false;
			}
			$object = $rs->FetchNextObject(false);
			$this->code = $object->code;
			$this->name = $object->name;
			$this->owner = new \Register\Person($object->owner_id);
			$this->license = $object->license;
			$this->description = $object->description;
			$this->status = $object->status;
			$this->date_created = $object->date_created;
			$this->timestamp = $object->timestamp;
			$this->platform = $object->platform;
			$factory = new \Storage\RepositoryFactory();
			$this->repository = $factory->load($object->repository_id);
			$this->package_version_id = $object->package_version_id;

			return true;
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
				$this->error = "SQL Error in Package::Version::latest(): ".$GLOBALS['_database']->ErrorMsg();
				return false;
			}
			list($this->id) = $rs->FetchRow();
			return new Version($this->id);
		}

		public function addVersion($parameters = array()) {
			$parameters['package_id'] = $this->id;
			$version = new Version();
			return $version->add($parameters);
		}

		public function packageVersion() {
			return new \Package\Version($this->package_version_id);
		}
	}
