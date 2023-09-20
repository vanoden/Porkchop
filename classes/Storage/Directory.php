<?php
	namespace Storage;

	class Directory Extends \BaseModel {

		public $path;
		public $repository_id;
		public $name;

		public function files() {
			$filelist = new FileList();
			$files = $filelist->find(array('path' => $this->path));
			if ($filelist->error()) {
				$this->error($filelist->error());
				return null;
			}
			return $files;
		}

		public function display($name) {
			$this->name = $name;
			return $name;
		}

		public function name() {
			return $this->name;
		}

        public function getInPath($repository_id,$path) {
            $this->repository_id = $repository_id;
            $this->path = $path;
            $this->name = basename($path);
            return true;
        }
	}
