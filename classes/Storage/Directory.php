<?php
	namespace Storage;

	class Directory Extends \BaseModel {

		public $path;
		public $repository_id;
		public $name;

		/**
		 * Class Constructor
		 * @param int $id - Optional ID of the file to load
		 * @return void
		 */
		public function __construct($id = 0) {
			$this->_tableName = 'storage_directories';
			parent::__construct($id);
		}

		/** @method files()
		 * Returns a list of files in this directory.
		 * @return FileList|null
		 */
		public function files() {
			$filelist = new FileList();
			$files = $filelist->find(array('path' => $this->path));
			if ($filelist->error()) {
				$this->error($filelist->error());
				return null;
			}
			return $files;
		}

		/** @method display()
		 * Sets the name of the directory and returns it.
		 * @param string $name The name to display for this directory.
		 * @return string The name of the directory.
		 */
		public function display($name) {
			$this->name = $name;
			return $name;
		}

		/** @method name()
		 * Returns the name of the directory.
		 * @return string The name of the directory.
		 */
		public function name() {
			return $this->name;
		}

		/** @method get()
		 * Loads the directory by its path.
		 * @param string $path The path of the directory to load.
		 * @return bool True on success, false on failure.
		 */
		public function get($path) {
			if (!$this->validPath($path)) {
				$this->error("Invalid path");
				return false;
			}
			$this->path = $path;
			$this->name = basename($path);
			$database = new \Database\Service();
			$find_query = "
				SELECT path
				FROM storage_files
				WHERE path = ?
				GROUP BY path
				LIMIT 1
			";
			$database->AddParam($path);
			$rs = $database->Execute($find_query);
			if (!$rs) {
				$this->SQLError($database->ErrorMsg());
				return false;
			}
			if ($rs->RecordCount() == 0) {
				$this->error("Directory not found");
				return false;
			}
			$row = $rs->FetchRow();
			$this->id = $row['id'];
			$this->repository_id = $row['repository_id'];
			$this->path = $row['path'];
			$this->name = $row['name'];
			return true;
		}

		/** @method getInPath()
		 * Sets the repository ID and path for this directory.
		 * @param int $repository_id The ID of the repository this directory belongs to.
		 * @param string $path The path of the directory.
		 * @return bool True on success, false on failure.
		 */
        public function getInPath($repository_id,$path) {
            $this->repository_id = $repository_id;
            $this->path = $path;
            $this->name = basename($path);
            return true;
        }

		/** @method validPath(string)
		 * Validates the path of this directory.
		 * @param string $path The path to validate.
		 * @return bool True if the path is valid, false otherwise.
		 */
		public function validPath($path) {
			if (empty($path)) {
				$this->error("Path cannot be empty");
				return false;
			}
			if (strpos($path, '..') !== false) {
					$this->error("Path cannot contain '..'");
				return false;
			}
			if (strpos($path, '/') !== false && strpos($path, '/') != 0) {
				$this->error("Path must start with '/'");
				return false;
			}
			if (preg_match('/[<>:"\\\|\?\*]/', $path)) {
				$this->error("Path contains invalid characters");
				return false;
			}
			if (strlen($path) > 255) {
				$this->error("Path is too long");
				return false;
			}
			if (strlen($path) < 1) {
				$this->error("Path must be at least 1 character long");
				return false;
			}
			return true;
		}
	}
