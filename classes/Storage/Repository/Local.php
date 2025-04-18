<?php
	namespace Storage\Repository;

	class Local extends \Storage\Repository {
		public $path;

		public function __construct($id = null) {
			$this->type = 'Local';
			$this->_addMetadataKeys(array("path","endpoint"));
			parent::__construct($id);
		}

		public function add($parameters = []): bool {
			app_log("Creating local repository ".$parameters['name']." in ".$parameters['path'],'notice');
			if (! isset($parameters['path'])) {
				$this->error("Path required");
				return false;
			}
			elseif (! is_dir($parameters['path'])) {
				$this->error("Path '".$parameters['path']."' doesn't exist");
				return false;
			}
			elseif (! is_writable($parameters['path'])) {
				$this->error("Path not writable");
				return false;
			}

			app_log("Path is ".$parameters['path'],'notice');
			parent::add($parameters);
			if ($this->id) {
				app_log("Storage repository created, adding path",'notice');
				if ($this->_setMetadata('path',$parameters['path'])) {
					app_log("Path set to ".$parameters['path'],'notice');
					$this->path = $this->_path();
					return true;
				}
				else {
					app_log("Failed to set path: ".$this->error(),'error');
					$this->error("Failed to add path to metadata: ".parent::error());
					return false;
				}
			}
			else {
				app_log("Parent add returned false: ".$this->error(),'error');
				return false;
			}
		}

		public function connect() {
			$path = $this->getMetadata('path');
			if (is_dir($path)) return true;
			$this->error("Path '$path' doesn't exist or is not a directory");
			return false;
		}

		private function _path($path = null) {
			if (isset($path)) {
			
				if (! is_dir($path)) {
					$this->error("Path doesn't exist on server");
					return false;
				}
				
				if (! is_writable($path)) {
					$this->error("Path not writable");
					return false;
				}
				
				$this->_setMetadata('path',$path);
			}
			return $this->getMetadata('path');
		}

		public function _endpoint($string = null) {
			if (isset($string)) $this->_setMetadata('endpoint',$string);
			return $this->getMetadata('endpoint');
		}

		public function details(): bool {
			parent::details();
			$this->path = $this->_path();
			$this->endpoint = strval($this->_endpoint());
			return true;
		}

        /**
         * Write contents to filesystem
         *
         * @param $file
         * @param $path
         */
		public function addFile($file, $path) {
			return move_uploaded_file($path, $this->_path() . "/" . $file->code());
		}

		public function retrieveFile($file) {
			if (!$this->validPath($this->_path())) {
				$this->error("Invalid path for repository");
				return false;
			}

			if (! file_exists($this->_path()."/".$file->code)) {
				$this->error("File not found");
				return false;
			}

			// Load contents from filesystem 
			$fh = fopen($this->_path()."/".$file->code,'rb');
			if (FALSE === $fh) {
				$this->error("Failed to open file");
				return false;
			}

			header("Content-Type: ".$file->mime_type);
			header("Content-Length: ".filesize($this->_path()."/".$file->code));
			header('Content-Disposition: filename="'.$file->name().'"');
			while (!feof($fh)) {
				$buffer = fread($fh,8192);
				print $buffer;
				flush();
				ob_flush();
			}
			fclose($fh);
			exit;
		}

		public function checkFile($file) {
			if (!$this->validPath($this->_path())) {
				$this->error("Invalid path for repository");
				return false;
			}

			if (! file_exists($this->_path()."/".$file->code)) {
				$this->error("File not found");
				return false;
			}
			return true;
		}

		public function eraseFile($file) {
		
			if (! file_exists($this->_path()."/".$file->code)) {
                $this->error("File not found");
                return false;
            }
            
            if (! unlink($this->_path()."/".$file->code)) {
                $this->error("Failed to delete file");
                return false;
            }
            
			return true;
		}

		public function validPath ($path) {
			# No Uplevel paths
			if (preg_match('/\.\./',$path)) return false;

			# No funky chars
			if (preg_match('/^\/?\w[\/\w\_\.]*$/',$path)) return true;
			else return false;
		}

		public function validEndpoint ($endpoint) {
			# Not Required
			if (empty($endpoint)) return true;

			# No Uplevel paths
			if (preg_match('/\.\./',$endpoint)) return false;

			# No funky chars
			if (preg_match('/^https?\:\/\/\w[\/\w\_\.]*$/',$endpoint)) return true;
			else return false;
		}
	}
