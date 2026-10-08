<?php
	namespace Product;

	class RelationshipList Extends \BaseListClass {
		public function __construct() {
			$this->_modelName = '\Product\Relationship';
		}

		public function findAdvanced($parameters, $advanced, $controls): array {
			$this->clearError();
			$this->resetCount();

			// Initialize Database Service
			$database = new \Database\Service();

			// Build Query
			$find_objects_query = "
				SELECT	parent_id,
						product_id child_id
				FROM	product_relations
				WHERE	product_id = product_id
			";

			// Add Parameters
			$validationClass = new $this->_modelName();
			if (!empty($parameters['parent_id']) && is_numeric($parameters['parent_id'])) {
				$parent = new \Product\Item($parameters['parent_id']);
				if ($parent->exists()) {
					$find_objects_query .= "
					AND		parent_id = ?
					";
					$database->AddParam($parameters['parent_id']);
				}
				else {
					$this->error("Parent not found");
					return [];
				}
			}
			if (!empty($parameters['child_id']) && is_numeric($parameters['child_id'])) {
				$child = new \Product\Item($parameters['child_id']);
				if ($child->exists()) {
					$find_objects_query .= "
					AND		product_id = ?
					";
					$database->AddParam($parameters['child_id']);
				}
				else {
					$this->error("Child not found");
					return [];
				}
			}

			// Order Clause
			$find_objects_query .= "
				ORDER BY view_order
			";

			// Limit Clause
			$find_objects_query .= $this->limitClause($controls);

			// Execute Query
			$rs = $database->Execute($find_objects_query);
			if ($database->ErrorMsg()) {
				$this->SQLError($database->ErrorMsg());
				return [];
			}

			$objects = array();
			while(list($parent_id,$child_id) = $rs->FetchRow()) {
				$parent = new \Product\Item($parent_id);
				$child = new \Product\Item($child_id);
				if ($parent->error()) {
					$this->error($parent->error());
					return [];
				}
				if ($child->error()) {
					$this->error($child->error());
					return [];
				}
				$this->incrementCount();
				array_push($objects,array('parent' => $parent, 'child' => $child));
			}
			return $objects;
		}
	}
?>