<?php
	namespace Media;

	class ItemList {
		public $error;
		public $count;

		public function find($parameters = array()) {
			$find_object_query = "
				SELECT	distinct(m.item_id)
				FROM	media_metadata m,
						media_items i
				WHERE	m.item_id = i.id
				AND		i.deleted = 0
			";
			foreach ($parameters as $label => $value) {
				if (! preg_match('/^[\w\-\.\_]+$/',$label)) {
					$this->error = "Invalid parameter name in MediaItem::find: ".$GLOBALS['_database']->ErrorMsg();
					return null;
				}
				if ($label == "type")
					$find_object_query .= "
					AND	i.type = ".$GLOBALS['_database']->qstr($value,get_magic_quotes_gpc);
				else
					$find_object_query .= "
					AND (	m.label = '".$label."'
						AND m.value = ".$GLOBALS['_database']->qstr($value,get_magic_quotes_gpc)."
					)";
			}
			app_log("Query: $find_object_query",'debug',__FILE__,__LINE__);
			$rs = $GLOBALS['_database']->Execute($find_object_query);
			if ($GLOBALS['_database']->ErrorMsg()) {
				$this->error = "SQL Error in MediaItem::find: ".$GLOBALS['_database']->ErrorMsg();
				return null;
			}
			$objects = array();
			while (list($id) = $rs->FetchRow()) {
				$object = new \Media\Item($id);
				$privileges = $object->privileges($id);
				if ($privileges['read']) {
					app_log("Adding ".$object->id." to array",'debug',__FILE__,__LINE__);
					array_push($objects,$object);
				}
				else {
					app_log("Hiding ".$object->id." lacking privileges",'debug',__FILE__,__LINE__);
				}
			}
			return $objects;
		}
	}
