<?
	namespace Media;

	class DocumentList {
		public $error;
		public $count;

		public function find($parameters = array()) {
			$parameters['type'] = 'document';
			return parent::find($parameters);
		}
	}
?>