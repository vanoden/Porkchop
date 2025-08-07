<?php
namespace Media;

class ItemList extends \Storage\FileList {
	public function __construct() {
		$this->_modelName = '\Media\Item';
	}

	public function findAdvanced($parameters, $advanced, $controls): array {
		if (!empty($parameters['type'])) {
			if ($parameters['type'] == 'image')	$parameters['mime_type'] = 'image/%';
			else if ($parameters['type'] == 'video') $parameters['mime_type'] = 'video/%';
			else if ($parameters['type'] == 'audio') $parameters['mime_type'] = 'audio/%';
			else if ($parameters['type'] == 'document') $parameters['mime_type'] = 'application/%';
			else if ($parameters['type'] == 'text') $parameters['mime_type'] = 'text/%';
			else {
				$this->error("Invalid type");
				return [];
			}
		}
		return parent::findAdvanced($parameters, $advanced, $controls);
	}
}
