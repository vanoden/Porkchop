<?php
	namespace Media;

	class DocumentList {
		public $error;
		public $count;

		public function find($parameters = array()) {
			$parameters['type'] = 'document';
			$itemlist = new ItemList();
			return itemlist::find($parameters);
		}
	}
