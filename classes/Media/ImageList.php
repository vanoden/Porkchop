<?php
namespace Media;

class ImageList extends \Storage\FileList {
	public function findAdvanced($parameters, $advanced, $controls): array {
		$parameters['type'] = 'image';
		$itemlist = new ItemList();
		return $itemlist->find($parameters, $advanced, $controls);
	}
}
