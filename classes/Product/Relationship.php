<?php
	namespace Product;

	class Relationship Extends \BaseModel {

		public function __construct() {
			$this->_tableName = "product_relations";
			$this->_tableUKColumn = null;
    		parent::__construct();			
		}

		public function add($parameters = []) {

			$parent = new \Product\Group($parameters['parent_id']);
			if (!$parent->exists()) {
				$this->error("Parent group not found");
				return null;
			}
			$child = new \Product\Item($parameters['child_id']);
			if (!$child->exists()) {
				$this->error("Child item not found");
				return null;
			}

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
				$this->SQLError($GLOBALS['_database']->ErrorMsg());
				return null;
			}
			
			// add audit log
			$auditLog = new \Site\AuditLog\Event();
			$auditLog->add(array(
				'instance_id' => $this->id,
				'description' => 'Added new '.$this->_objectName(),
				'class_name' => get_class($this),
				'class_method' => 'add'
			));

			return $this->get($parameters['parent_id'],$parameters['child_id']);
		}

		public function __call($name,$parameters) {
			if ($name == "get") return $this->getRelationShip($parameters[0],$parameters[1]);
		}

		public function getRelationship($parent_id,$child_id) {
			$parent = new \Product\Group($parent_id);
			if (!$parent->exists()) {
				$this->error("Parent group not found");
				return null;
			}
			$child = new \Product\Item($child_id);
			if (!$child->exists()) {
				$this->error("Child item not found");
				return null;
			}

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
				$this->SQLError($GLOBALS['_database']->ErrorMsg());
				return null;
			}
			$array = $rs->FetchRow();
			return (object) $array;
		}
	}
