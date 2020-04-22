<?php
	namespace Storage\Repository;

	class Local extends \Storage\Repository {
	
		public function __construct($id = null) {
			if ($id > 0) {
				$this->id = $id;
				$this->details();
			}
			$this->type = 'Local';
		}

		public function add($parameters) {
			app_log("Creating local repository ".$parameters['name'],'notice');
			if (! isset($parameters['path'])) {
				$this->error = "Path required";
				return false;
			} elseif (! is_dir($parameters['path'])) {
				$this->error = "Path doesn't exist";
				return false;
			} elseif (! is_writable($parameters['path'])) {
				$this->error = "Path not writable";
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
				} else {
					app_log("Failed to set path: ".$this->error,'error');
					$this->error = "Failed to add path to metadata: ".parent::error;
					return false;
				}
			} else {
				app_log("Parent add returned false: ".$this->error,'error');
				return false;
			}
		}

		private function _path($path = null) {
			if (isset($path)) {
			
				if (! is_dir($path)) {
					$this->error = "Path doesn't exist on server";
					return false;
				}
				
				if (! is_writable($path)) {
					$this->error = "Path not writable";
					return false;
				}
				
				$this->_setMetadata('path',$path);
			}
			return $this->_metadata('path');
		}

		public function _endpoint($string = null) {
			if (isset($string)) $this->_setMetadata('endpoint',$string);
			return $this->_metadata('endpoint');
		}

		public function setMetadata($key,$value) {
			if ($key == 'path') {
				return $this->_path($value);
			} else if ($key == 'endpoint') {
				return $this->_endpoint($value);
			} else {
				$this->error = "Invalid key";
				return false;
			}
		}

		public function metadata($key) {
			if ($key == 'path') {
				return $this->_path();
			} else if ($key == 'endpoint') {
				return $this->_endpoint();
			} else {
				$this->error = "Invalid key";
				return false;
			}
		}

		public function details() {
			parent::details();
			$this->path = $this->_path();
			$this->endpoint = $this->_endpoint();
		}

        /**
         * Write contents to filesystem
         *
         * @param $file
         * @param $path
         */
		public function addFile($file, $path) {
			return move_uploaded_file($path,$this->_path()."/".$file->code());
		}

		public function retrieveFile($file) {
		
			if (! file_exists($this->_path()."/".$file->code)) {
				$this->error = "File not found";
				return false;
			}

			// Load contents from filesystem 
			$fh = fopen($this->_path()."/".$file->code,'rb');
			if (FALSE === $fh) {
				$this->error = "Failed to open file";
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

		public function eraseFile($file) {
		
			if (! file_exists($this->_path()."/".$file->code)) {
                $this->error = "File not found";
                return false;
            }
            
            if (! unlink($this->_path()."/".$file->code)) {
                $this->error = "Failed to delete file";
                return false;
            }
            
			return true;
		}
	}
