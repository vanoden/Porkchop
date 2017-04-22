<?
	namespace Storage\Repository;

	class Local extends \Storage\Repository {
		public function __construct($id = null) {
			if ($id > 0) {
				$this->id = $id;
				$this->details();
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
			if (isset($string)) {
				$this->_setMetadata('endpoint',$string);
			}
			return $this->_metadata('endpoint');
		}

		public function setMetadata($key,$value) {
			if ($key == 'path') {
				return $this->_path($value);
			}
			else if ($key == 'endpoint') {
				return $this->_endpoint($value);
			}
			else {
				$this->error = "Invalid key";
				return false;
			}
		}

		public function metadata($key) {
			if ($key == 'path') {
				return $this->_path();
			}
			else if ($key == 'endpoint') {
				return $this->_endpoint();
			}
			else {
				$this->error = "Invalid key";
				return false;
			}
		}

		public function details() {
			parent::details();
			$this->path = $this->_path();
			$this->endpoint = $this->_endpoint();
		}

		public function addFile($file,$path) {
			# Write contents to filesystem
			return move_uploaded_file($path,$this->_path()."/".$file->name);
		}

		public function retrieveFile($file) {
			if (! file_exists($this->_path()."/".$file->name)) {
				$this->error = "File not found";
				return false;
			}

			# Load contents from filesystem
			$fh = fopen($this->_path()."/".$file->name,'rb');
			if (FALSE === $fh) {
				$this->error = "Failed to open file";
				return false;
			}

			header("Content-Type: ".$file->mime_type);
			while (!feof($fh)) {
				$buffer = fread($fh,8192);
				#$stream_meta_data = stream_get_meta_data($fh);
				#if($stream_meta_data['unread_bytes'] <= 0) break;
				print $buffer;
				flush();
				ob_flush();
			}
			fclose($fh);
			exit;
		}

		public function eraseFile($file) {
			if (! file_exists($this->_path()."/".$file->name))
			return true;
		}
	}
?>