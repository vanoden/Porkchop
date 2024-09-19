<?php
namespace Media;

class FileList extends \BaseListClass {

	public function find($parameters = array()) {
		# Get Code From Table
		$get_code_query = "
				SELECT	id
				FROM	media_files
				WHERE	id = id
			";
		if (preg_match('/^\d+$/', $parameters['item_id'])) {
			$get_code_query .= "
				AND		item_id = " . $parameters['item_id'];
		}
		if (array_key_exists('index', $parameters) and preg_match('/^\d+$/', $parameters['index'])) {
			$get_code_query .= "
				AND		`index` = " . $GLOBALS['_database']->qstr($parameters['index'], get_magic_quotes_gpc());
		}
		$rs = $GLOBALS['_database']->Execute(
			$get_code_query
		);
		if (! $rs) {
			$this->error = "SQL Error in MediaFile::load: " . $GLOBALS['_database']->ErrorMsg();
		}
		$objects = array();
		while (list($id) = $rs->FetchRow()) {
			$file = new \Media\File($id);
			array_push($objects, $file);
		}
		return $objects;
	}
}
