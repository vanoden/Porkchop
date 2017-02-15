<?
	namespace HTTP;

	class Response {
		public $message;
		public $success;
		public $header;
		
		public function __construct() {
			$this->header = new Header();
		}
	}
?>
