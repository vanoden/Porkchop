<?
	namespace Media;

	class Repository {
		public $error;
		public $id;
		public $name;
		public $type;

		public function __construct($id = 0) {
			if ($id > 0) {
				$this->id = $id;
				$this->details();
			}
		}

		public function details() {
		}
	}
?>