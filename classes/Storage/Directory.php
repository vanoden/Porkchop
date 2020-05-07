<?php
	namespace Storage;

	class Directory {
		private $error;
		public $path;

		public function get($repository_id,$path) {
			$this->repository_id = $repository_id;
			$this->path = $path;
			$this->name = preg_replace('/^\//','',$this->path);
		}

		public function files() {
			$filelist = new FileList();
			$file = $filelist->find(array('path' => $this->path));
			if ($filelist->error()) {
				$this->error = $filelist->error();
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
