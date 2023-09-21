<?php
	namespace Product;

	class RelationshipList Extends \BaseListClass {
		public function find($parameters) {
			$bind_params = array();
			$find_objects_query = "
				SELECT	parent_id,
						product_id child_id
				FROM	product_relations
				WHERE	product_id = product_id
			";
			if (preg_match('/^\d+$/',$parameters['parent_id'])) {
				$find_objects_query .= "
				AND		parent_id = ?";
				array_push($bind_params,$parameters['parent_id']);
			}
			if ($parameters['child_id']) {
				$find_objects_query .= "
				AND		child_id = ?";
				array_push($bind_params,$parameters['child_id']);
			}
			$find_objects_query .= "
				ORDER BY view_order
			";

			$rs = $GLOBALS['_database']->Execute($find_objects_query,$bind_params);
			if ($GLOBALS['_database']->ErrorMsg()) {
				$this->SQLError($GLOBALS['_database']->ErrorMsg());
				return null;
			}
			$objects = array();
			while(list($parent_id,$child_id) = $rs->FetchRow()) {
				$object = $this->get($parent_id,$child_id);
				if ($this->error) return null;
				$this->incrementCount();
				array_push($objects,$object);
			}
			return $objects;
		}
	}
?>