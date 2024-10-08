<?php
namespace Media;

class ItemList extends \BaseListClass {

	public function find($parameters = array()) {
		$find_object_query = "
				SELECT	distinct(m.item_id)
				FROM	media_metadata m,
						media_items i
				WHERE	m.item_id = i.id
				AND		i.deleted = 0
			";
		$bind_params = array();
		foreach ($parameters as $label => $value) {
			if (! preg_match('/^[\w\-\.\_]+$/', $label)) {
				$this->error = "Invalid parameter name in Media::ItemList::find()";
				return null;
			}
			if ($label == "type") {
				$find_object_query .= "
					AND	i.type = ?";
				array_push($bind_params, $value);
			} else {
				$find_object_query .= "
					AND (	m.label = ?
						AND m.value = ?";
				array_push($bind_params, $label, $value);
			}
		}
		query_log($find_object_query, $bind_params);
		$rs = $GLOBALS['_database']->Execute($find_object_query, $bind_params);
		if ($GLOBALS['_database']->ErrorMsg()) {
			$this->error = "SQL Error in Media::ItemList::find(): " . $GLOBALS['_database']->ErrorMsg();
			return null;
		}
		$objects = array();
		while (list($id) = $rs->FetchRow()) {
			$object = new \Media\Item($id);
			$privileges = $object->privileges($id);
			if ($privileges['read']) {
				app_log("Adding " . $object->id . " to array", 'debug', __FILE__, __LINE__);
				array_push($objects, $object);
			} else {
				app_log("Hiding " . $object->id . " lacking privileges", 'debug', __FILE__, __LINE__);
			}
		}
		return $objects;
	}
}
