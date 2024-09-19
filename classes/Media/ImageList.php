<?php
namespace Media;

class ImageList extends \Storage\FileList {

	public function find($parameters = array()) {
		$parameters['type'] = 'document';
		$itemlist = new ItemList();
		return $itemlist->find($parameters);
	}
}
