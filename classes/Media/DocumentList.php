<?php
namespace Media;

class DocumentList extends \BaseListClass {

	public function findAdvanced($parameters, $advanced, $controls): array {
		$parameters['type'] = 'document';
		$itemlist = new ItemList();
		return $itemlist->find($parameters, $advanced, $controls);
	}
}
