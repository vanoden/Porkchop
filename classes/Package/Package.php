<?
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
			if (! $GLOBALS['_SESSION_']->customer->has_role('package manager')) {
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

			if (! preg_match('/^\w+$/',$parameters['code'])) {
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
				(		?,?,?,sysdate(),'NEW',?)
			";
			$GLOBALS['_database']->Execute(
				$add_object_query,
				array($parameters['code'],$parameters['name'],$GLOBALS['_SESSION_']->customer->id,$parameters['repository_id'])
			);
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

			$update_object_query = "
				UPDATE	package_packages
				SET		id = id
			";

			if (isset($parameters['status']) && strlen($parameters['status'])) {
				if (preg_match('/^(NEW|ACTIVE|HIDDEN)$/',$parameters['status']))
					$update_object_query .= ",
						status = ".$GLOBALS['_database']->qstr($parameters['status'],get_magic_quotes_gpc());
				else {
					$this->error = "Invalid package status '".$parameters['status']."'";
					return null;
				}
			}
			if (isset($parameters['license']) && strlen($parameters['license']))
				$update_object_query .= ",
						license = ".$GLOBALS['_database']->qstr($parameters['license'],get_magic_quotes_gpc());
			if (isset($parameters['platform']) && strlen($parameters['platform']))
				$update_object_query .= ",
						platform = ".$GLOBALS['_database']->qstr($parameters['platform'],get_magic_quotes_gpc());


			if (isset($parameters['description']) && strlen($parameters['description']))
				$update_object_query .= ",
						description = ".$GLOBALS['_database']->qstr($parameters['description'],get_magic_quotes_gpc());

			$update_object_query .= "
				WHERE	id = ?
			";
			$GLOBALS['_database']->Execute(
				$update_object_query,
				array($this->id)
			);
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
				return null;
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

			return true;
		}

		public function addVersion($parameters = array()) {
			$parameters['package_id'] = $this->id;
			$version = new Version();
			return $version->add($parameters);
		}
	}
?>
