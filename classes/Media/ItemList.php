<?php
namespace Media;

class ItemList extends \BaseListClass {
	public function __construct() {
		$this->_modelName = '\Media\Item';
	}

	public function findAdvanced($parameters, $advanced, $controls): array {
		$this->clearError();
		$this->resetCount();

		// Initialize Database Service
		$database = new \Database\Service();

		// Build the Query
		$find_object_query = "
				SELECT	distinct(m.item_id)
				FROM	media_metadata m,
						media_items i
				WHERE	m.item_id = i.id
				AND		i.deleted = 0
			";

		// Add Parameters
		foreach ($parameters as $label => $value) {
			if (! preg_match('/^[\w\-\.\_]+$/', $label)) {
				$this->error("Invalid parameter name in Media::ItemList::find()");
				return null;
			}
			if ($label == "type") {
				$find_object_query .= "
					AND	i.type = ?";
				$database->AddParam($value);
			}
			else {
				$find_object_query .= "
					AND (	m.label = ?
						AND m.value = ?";
				$database->AddParams($label, $value);
			}
		}

		// Execute the Query
		$rs = $database->Execute($find_object_query);
		if ($database->ErrorMsg()) {
			$this->SQLError($database->ErrorMsg());
			return null;
		}

		$objects = array();
		while (list($id) = $rs->FetchRow()) {
			$object = new \Media\Item($id);
			$privileges = $object->privileges($id);
			if ($privileges['read']) {
				app_log("Adding " . $object->id . " to array", 'debug', __FILE__, __LINE__);
				array_push($objects, $object);
				$this->incrementCount();
			}
			else {
				app_log("Hiding " . $object->id . " lacking privileges", 'debug', __FILE__, __LINE__);
			}
		}
		return $objects;
	}
}
