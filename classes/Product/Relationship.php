<?php
	namespace Product;

	class Relationship {
		public $error;
		public function __construct()
		{
			# Database Initialization
			$schema = new \Product\Schema();

			if ($schema->error) {
				$this->error = $schema->error;
				return null;
			}
		}
		public function add($parameters = array()) {
			$add_object_query = "
				INSERT
				INTO	product_relations
				(		parent_id,product_id)
				VALUES
				(		?,?)
			";
			$GLOBALS['_database']->Execute(
				$add_object_query,
				array(
					$parameters['parent_id'],
					$parameters['child_id']
				)
			);
			if ($GLOBALS['_database']->ErrorMsg()) {
				$this->error = "SQL Error in ProductRelationship::add: ".$GLOBALS['_database']->ErrorMsg();
				return null;
			}
			return $this->get($parameters['parent_id'],$parameters['child_id']);
		}
		public function get($parent_id,$child_id) {
			$get_object_query = "
				SELECT	parent_id,
						product_id child_id
				FROM	product_relations
				WHERE	parent_id = ?
				AND		product_id = ?
			";
			$rs = $GLOBALS['_database']->Execute(
				$get_object_query,
				array(
					$parent_id,
					$child_id
				)
			);
			if ($GLOBALS['_database']->ErrorMsg()) {
				$this->error = "SQL Error in ProductRelationship::get: ".$GLOBALS['_database']->ErrorMsg();
				return null;
			}
			$array = $rs->FetchRow();
			return (object) $array;
		}
		public function find($parameters) {
			$find_objects_query = "
				SELECT	parent_id,
						product_id child_id
				FROM	product_relations
				WHERE	product_id = product_id
			";
			if (preg_match('/^\d+$/',$parameters['parent_id']))
				$find_objects_query .= "
				AND		parent_id = ".$GLOBALS['_database']->qstr($parameters['parent_id'],get_magic_quotes_gpc);
			if ($parameters['child_id'])
				$find_objects_query .= "
				AND		child_id = ".$GLOBALS['_database']->qstr($parameters['child_id'],get_magic_quotes_gpc);

			$find_objects_query .= "
				ORDER BY view_order
			";

			$rs = $GLOBALS['_database']->Execute($find_objects_query);
			if ($GLOBALS['_database']->ErrorMsg())
			{
				$this->error = "SQL Error in ProductRelationship::find: ".$GLOBALS['_database']->ErrorMsg();
				return null;
			}
			$objects = array();
			while(list($parent_id,$child_id) = $rs->FetchRow())
			{
				$object = $this->get($parent_id,$child_id);
				if ($this->error) return null;
				array_push($objects,$object);
			}
			return $objects;
		}
	}
?>
