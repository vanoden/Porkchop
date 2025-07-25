<?php
	namespace Storage\Repository;

	class Local extends \Storage\Repository {
		public $path;

		/** @constructor */
		public function __construct($id = null) {
			$this->type = 'Local';
			$this->_addMetadataKeys(array("path","endpoint"));
			parent::__construct($id);
		}

		/** @method add(array $parameters)
		 * Add a new local repository
		 *
		 * @param array $parameters
		 * @return bool
		 */
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

		/** @method connect()
		 * Connect to the repository
		 * Test to make sure the path exists and is a directory
		 * @return bool
		 */
		public function connect() {
			$path = $this->getMetadata('path');
			if (is_dir($path)) return true;
			$this->error("Path '$path' doesn't exist or is not a directory");
			return false;
		}

		/** @method _path($path = null)
		 * Set or get the path for this repository
		 *
		 * @param string $path
		 * @return string
		 */
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

		/** @method _endpoint($string = null)
		 * Set or get the endpoint for this repository
		 *
		 * @param string $string
		 * @return string
		 */
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

        /** @method addFile($file, $path)
         * Write contents to filesystem
         *
         * @param $file
         * @param $path
         */
		public function addFile($file, $path) {
			return move_uploaded_file($path, $this->_path() . "/" . $file->code());
		}

		/** @method retrieveFile($file)
		 * Retrieve file from filesystem
		 *
		 * @param $file
		 * @return bool
		 */
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

		/** @method content()
		 * Get the content of specified file
		 * @return string
		 */
		public function content($file) {
			if (!$this->validPath($this->_path())) {
				$this->error("Invalid path for repository");
				return false;
			}

			if (! file_exists($this->_path()."/".$file->code)) {
				$this->error("File not found at ".$this->_path()."/".$file->code);
				return false;
			}

			// Load contents from filesystem
			$data = file_get_contents($this->_path()."/".$file->code);
			if ($data === false) {
				$this->error("Failed to read file content");
				return false;
			}
			return $data;
		}

		/** @method checkFile($file)
		 * Check if file exists in repository
		 *
		 * @param $file
		 * @return bool
		 */
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

		/** @method eraseFile($file)
		 * Delete file from filesystem
		 *
		 * @param $file
		 * @return bool
		 */
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

		/** @method validPath($path)
		 * Validate a path
		 *
		 * @param $path
		 * @return bool
		 */
		public function validPath ($path) {
			# No Uplevel paths
			if (preg_match('/\.\./',$path)) return false;

			# No funky chars
			if (preg_match('/^\/?\w[\/\w\_\.]*$/',$path)) return true;
			elseif (preg_match('/\//',$path)) return true;
			else return false;
		}

		/** @method validEndpoint($endpoint)
		 * Validate an endpoint
		 *
		 * @param $endpoint
		 * @return bool
		 */
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
