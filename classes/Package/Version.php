<?php
	namespace Package;

	class Version extends \Storage\File {
	
		public $error;
		public $major;
		public $minor;
		public $build;
		public $status;
		private $extension;

		public function __construct($id = 0) {
			if ($id > 0) {
				$this->id = $id;
				return $this->details();
			}
		}

		public function add($parameters = array()) {
            
			if (! $GLOBALS['_SESSION_']->customer->has_role('package manager')) {
				$this->error = "package manager role required";
				return false;
			}
			
			if (! preg_match('/^\d+$/',$parameters['package_id'])) {
				$this->error = "package_id required";
				return false;
			}
			
			$package = new Package($parameters['package_id']);
			if (! $package->id) {
				$this->error = "Package not found";
				return false;
			}
			
            if (! $package->repository->id) {
                $this->error = "No repository assigned to package";
                return false;
            }
            
			$this->package = $package;
			if (! preg_match('/^\d+$/',$parameters['major'])) {
				$this->error = "major sequence required";
				return false;
			}
			
			$this->major = $parameters['major'];
			if (! preg_match('/^\d+$/',$parameters['minor'])) {
				$this->error = "minor sequence required";
				return false;
			}
			
			$this->minor = $parameters['minor'];
			if (! preg_match('/^\d+\w?$/',$parameters['build'])) {
				$this->error = "build sequence required";
				return false;
			}
			
			$this->build = $parameters['build'];

			if (! file_exists($parameters['path'])) {
				$this->error = "Uploaded file not found";
				return false;
			}

			if (isset($parameters['extension']) && strlen($parameters['extension'])) {
				$this->extension = $parameters['extension'];
			} else {
				$parts = pathinfo($parameters['filename']);
				$this->extension = $parts['extension'];
			}
			
			if (! strlen($this->extension)) {
				$this->error = "No extension found for ".$parameters['path'];
				return false;
			}

			// Set name based on package and version
			$this->name($this->formatted_name());

			$parameters['repository_id'] = $package->repository->id;
			$parameters['name'] = $this->formatted_name();
			if (! isset($parameters['mime_type'])) $parameters['mime_type'] = guess_mime_type($parameters['filename']);
			if (! isset($parameters['size'])) $parameters['size'] = filesize($parameters['path']);

            // Open Repository for File Storage
			$factory = new \Storage\RepositoryFactory();
			$repository = $factory->load($package->repository->id);
			if ($factory->error) {
				$this->error = "Error finding repository: ".$factory->error;
				return false;
			}

            // Add File to Repository
            parent::add(array(
                'repository_id' => $parameters['repository_id'],
                'name'          => $parameters['name'],
                'size'          => $parameters['size'],
                'mime_type'     => $parameters['mime_type'],
            ));
			if (parent::error()) {
				return false;
			}
			if (! $repository->addFile($this,$parameters['path'])) {
				$this->error = "Error adding file to repository: ".$repository->error;
				return false;
			}

			$insert_object_query = "
				INSERT
				INTO	package_versions
				(		id,package_id,major,minor,build,status,user_id,date_created
				)
				VALUES
				(		?,?,?,?,?,'NEW',?,sysdate())
			";
			$GLOBALS['_database']->Execute(
				$insert_object_query,
				array(
					$this->id,
					$parameters['package_id'],
					$parameters['major'],
					$parameters['minor'],
					$parameters['build'],
					$GLOBALS['_SESSION_']->customer->id
				)
			);
			if ($GLOBALS['_database']->ErrorMsg()) {
				$this->error = "SQL Error in Package::Version::add(): ".$GLOBALS['_database']->ErrorMsg();
				$this->delete();
				return null;
			}

			return $this->update($parameters);
		}
		
		public function get($package_id,$major,$minor,$build) {
		
			$get_object_query = "
				SELECT	id
				FROM	package_versions
				WHERE	package_id = ?
				AND		major = ?
				AND		minor = ?
				AND		build = ?
			";
			$rs = $GLOBALS['_database']->Execute(
				$get_object_query,
				array(
					$package_id,
					$major,
					$minor,
					$build
				)
			);
			
			if (! $rs) {
				$this->error = "SQL Error in Package::Version::get(): ".$GLOBALS['_database']->ErrorMsg();
				return false;
			}
			
			list($this->id) = $rs->FetchRow();
			return $this->details();
		}

		public function latest($package_id) {
			$get_object_query = "
				SELECT	id
				FROM	package_versions
				WHERE	package_id = ?
				AND		status = 'PUBLISHED'
				ORDER BY major DESC, minor DESC, build DESC
			";
			$rs = $GLOBALS['_database']->Execute(
				$get_object_query,
				array($package_id)
			);
			
			if (! $rs) {
				$this->error = "SQL Error in Package::Version::latest(): ".$GLOBALS['_database']->ErrorMsg();
				return false;
			}
			
			list($this->id) = $rs->FetchRow();
			return $this->details();
		}

		public function formatted_name() {
			app_log("Formatting name with ".$this->package->code."-".$this->major.".".$this->minor.".".$this->build.".".$this->extension);
			return sprintf("%s-%d.%d.%d.%s",$this->package->code,$this->major,$this->minor,$this->build,$this->extension);
		}

		public function publish() {
		
			$publish_object_query = "
				UPDATE	package_versions
				SET		status = 'PUBLISHED',
						date_published = sysdate()
				WHERE	id = ?
			";

			$GLOBALS['_database']->Execute($publish_object_query,array($this->id));
			if ($GLOBALS['_database']->ErrorMsg()) {
				$this->error = "SQL Error in Package::Version::publish(): ".$GLOBALS['_database']->ErrorMsg();
				return false;
			}
			
			return $this->details();
		}

		public function hide() {
		
			$hide_object_query = "
				UPDATE	package_versions
				SET		status = 'HIDDEN'
				WHERE	id = ?
			";

			$GLOBALS['_database']->Execute($hide_object_query,array($this->id));
			if ($GLOBALS['_database']->ErrorMsg()) {
				$this->error = "SQL Error in Package::Version::hide(): ".$GLOBALS['_database']->ErrorMsg();
				return false;
			}
			
			return $this->details();
		}
		
		public function update($parameters = array()) {
		
			$update_object_query = "
				UPDATE	package_versions
				SET		package_id = package_id
			";
			
			if (isset($parameters['status']) && strlen($parameters['status'])) {
				$parameters['status'] = strtoupper($parameters['status']);
				if (preg_match('/^(NEW|PUBLISHED|HIDDEN)$/',$parameters['status'])) {
					$update_object_query .= ",
					status = ".$GLOBALS['_database']->qstr($parameters['status'],get_magic_quotes_gpc());
				} else {
					$this->error = "Invalid status";
					return false;
				}
			}

			$update_object_query .= "
				WHERE	package_id = ?
				AND		major = ?
				AND		minor = ?
				AND		build = ?
			";
			$GLOBALS['_database']->Execute(
				$update_object_query,
				array(
					$this->package->id,
					$this->major,
					$this->minor,
					$this->build
				)
			);
			
			if ($GLOBALS['_database']->ErrorMsg()) {
				$this->error = "SQL Error in Package::Version::update(): ".$GLOBALS['_database']->ErrorMsg();
				return false;
			}
			
			return $this->details();
		}

		public function details() {
			$get_object_query = "
				SELECT	*
				FROM	package_versions
				WHERE	id = ?
			";
			$rs = $GLOBALS['_database']->Execute(
				$get_object_query,
				array($this->id)
			);
			
			if (! $rs) {
				$this->error = "SQL Error in Package::Version::update(): ".$GLOBALS['_database']->ErrorMsg();
				return false;
			}
			
			$object = $rs->FetchNextObject(false);
			app_log("Calling for parent details");
			parent::details();

			$this->package = new Package($object->package_id);
			$this->major = $object->major;
			$this->minor = $object->minor;
			$this->build = $object->build;
			$this->status = $object->status;
			$this->owner = new \Register\Person($object->user_id);
			$this->date_created = $object->date_created;
			$this->date_published = $object->date_published;
			$factory = new \Storage\RepositoryFactory();
			$this->repository = $factory->load($this->repository->id);

			return true;
		}
		
		public function version() {
			return sprintf("%0d.%0d.%0d",$this->major,$this->minor,$this->build);
		}
	}
