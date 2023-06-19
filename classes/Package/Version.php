<?php
	namespace Package;

	class Version Extends \Storage\File {
	
		public $error;
		public $major;
		public $minor;
		public $build;
		public $status;
		public $package_id;
		public $owner_id;
		public $date_published;
		private $_file;
		private $extension;

		public function __construct($id = 0) {
			if ($id > 0) {
				$this->id = $id;
				return $this->details();
			}
		}

		public function add($parameters = []) {
			if (! $GLOBALS['_SESSION_']->customer->can('manage packages')) {
				$this->error = "Not permitted to manage packages";
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
			
            if (! $package->repository_id) {
                $this->error = "No repository assigned to package";
                return false;
            }

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
			$file = new \Storage\File();
			$file->name($this->formatted_name());

			$parameters['repository_id'] = $package->repository_id;
			$parameters['name'] = $this->formatted_name();
			if (! isset($parameters['mime_type'])) $parameters['mime_type'] = guess_mime_type($parameters['filename']);
			if (! isset($parameters['size'])) $parameters['size'] = filesize($parameters['path']);

            // Open Repository for File Storage
			app_log("Getting Repository");
			$factory = new \Storage\RepositoryFactory();
			$repository = $factory->load($package->repository_id);
			if ($factory->error()) {
				$this->error = "Error finding repository: ".$factory->error();
				app_log($this->error,'error');
				return false;
			}

			if (! isset($parameters['status'])) {
				$parameters['status'] = 'NEW';
			}
			elseif ($parameters['status'] == 'PUBLISHED' && ! isset($parameters['date_published'])) {
				$parameters['date_published'] = get_mysql_date(time());
			}

            // Add File to Repository
			app_log("Adding file to repository");
            $file->add(array(
                'repository_id' => $parameters['repository_id'],
                'name'          => $parameters['name'],
                'size'          => $parameters['size'],
                'mime_type'     => $parameters['mime_type'],
            ));
			if ($file->error()) {
				return false;
			}
			app_log(print_r($file,true));
			$this->id = $file->id;
			if (! $repository->addFile($file,$parameters['path'])) {
				$this->error = "Error adding file to repository: ".$repository->error();
				return false;
			}

			app_log("Adding version ".$file->id." to package");
			$insert_object_query = "
				INSERT
				INTO	package_versions
				(		id,package_id,major,minor,build,status,user_id,date_created
				)
				VALUES
				(		?,?,?,?,?,?,?,sysdate())
			";

			$bind_params = array(
					$this->id,
					$parameters['package_id'],
					$parameters['major'],
					$parameters['minor'],
					$parameters['build'],
					$parameters['status'],
					$GLOBALS['_SESSION_']->customer->id
			);

			query_log($insert_object_query,$bind_params);
			$GLOBALS['_database']->Execute($insert_object_query,$bind_params);
			if ($GLOBALS['_database']->ErrorMsg()) {
				$this->error = "SQL Error in Package::Version::add(): ".$GLOBALS['_database']->ErrorMsg();
				$file->delete();
				return false;
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

		public function file() {
			return new \Storage\File($this->id);
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
			app_log("Formatting name with ".$this->package()->code."-".$this->major.".".$this->minor.".".$this->build.".".$this->extension,'trace');
			return sprintf("%s-%d.%d.%d.%s",$this->package()->code,$this->major,$this->minor,$this->build,$this->extension);
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
		
		public function update($parameters = array()): bool {
		
			$update_object_query = "
				UPDATE	package_versions
				SET		package_id = package_id
			";

			$bind_params = array();

			if (isset($parameters['status']) && strlen($parameters['status'])) {
				$parameters['status'] = strtoupper($parameters['status']);
				if (preg_match('/^(NEW|PUBLISHED|HIDDEN)$/',$parameters['status'])) {
					$update_object_query .= ",
					status = ?";
					array_push($bind_params,$parameters['status']);
					if ($parameters['status'] == 'PUBLISHED') {
						$update_object_query .= ",
						date_published = ?";
						array_push($bind_params,get_mysql_date(time()));
					}
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
			array_push($bind_params,$this->package_id,$this->major,$this->minor,$this->build);

			query_log($update_object_query,$bind_params);

			$GLOBALS['_database']->Execute($update_object_query,$bind_params);
			if ($GLOBALS['_database']->ErrorMsg()) {
				$this->error = "SQL Error in Package::Version::update(): ".$GLOBALS['_database']->ErrorMsg();
				return false;
			}
			
			return $this->details();
		}

		public function details(): bool {
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
			$this->id = $object->id;
			$this->package_id = $object->package_id;
			$this->major = $object->major;
			$this->minor = $object->minor;
			$this->build = $object->build;
			$this->status = $object->status;
			$this->owner_id = $object->user_id;
			$this->date_created = $object->date_created;
			$this->date_published = $object->date_published;

			return true;
		}
		public function owner() {
			return new \Register\Person($this->owner_id);
		}
		public function repository() {
			return $this->file()->repository();
			
		}
		
		public function version() {
			return sprintf("%0d.%0d.%0d",$this->major,$this->minor,$this->build);
		}
	}
