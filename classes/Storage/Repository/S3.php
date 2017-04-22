<?
	namespace Storage\Repository;

	class S3 extends \Storage\Repository {
		public function __construct($id = null) {
			if ($id > 0) {
				$this->id = $id;
				$this->details();
			}
		}

		private function _path($path = null) {
			if (isset($path)) {
				$this->setMetadata('path',$path);
			}
			return $this->getMetadata('path');
		}

		private function _bucket($bucket = null) {
			if (isset($bucket)) {
				$this->setMetadata('bucket',$bucket);
			}
			return $this->getMetadata('bucket');
		}


		public function details() {
			parent::details();
			$this->path = $this->metadata('path');
			$this->bucket = $this->metadata('bucket');
		}

		public function addFile($file) {
			# Write contents to filesystem
			return move_uploaded_file($file->tmp_path(),$path."/".$file->code());
		}

		public function retrieveFile() {
			# Load contents from filesystem
		}
	}
?>