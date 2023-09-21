<?php
	namespace Media;

	class ImageList extends \BaseListClass {

		public function find($parameters = array()) {
			$parameters['type'] = 'document';
			$itemlist = new ItemList();
			return $itemlist->find($parameters);
		}
	}
