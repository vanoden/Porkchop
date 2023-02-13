<?php
	namespace Storage;

	class Directory Extends \BaseClass {

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
	}
