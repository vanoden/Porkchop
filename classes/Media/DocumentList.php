<?php
namespace Media;

class DocumentList extends \BaseListClass {

	public function find($parameters = array()) {
		$parameters['type'] = 'document';
		$itemlist = new ItemList();
		return $itemlist->find($parameters);
	}
}
