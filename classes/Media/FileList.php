<?php
namespace Media;

class FileList extends \BaseListClass {
	public function __construct() {
		$this->_modelName = 'Media\File';
	}

	public function findAdvanced($parameters, $advanced, $controls): array {
		$this->clearError();
		$this->resetCount();

		// Initialize Database Service
		$database = new \Database\Service();

		// Build the query
		# Get Code From Table
		$get_code_query = "
				SELECT	id
				FROM	media_files
				WHERE	id = id
			";

		// Add Parameters
		if (!empty($parameters['item_id']) && is_numeric($parameters['item_id'])) {
			$get_code_query .= "
				AND		item_id = ?";
			$database->AddParam($parameters['item_id']);
		}
		if (array_key_exists('index', $parameters) and preg_match('/^\d+$/', $parameters['index'])) {
			$get_code_query .= "
				AND		`index` = ?";
			$database->AddParam($parameters['index']);
		}
		$rs = $database->Execute($get_code_query);
		if (! $rs) {
			$this->SQLError($database->ErrorMsg());
		}
		$objects = array();
		while (list($id) = $rs->FetchRow()) {
			$file = new \Media\File($id);
			array_push($objects, $file);
			$this->incrementCount();
		}
		return $objects;
	}
}
