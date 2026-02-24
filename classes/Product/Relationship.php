<?php
	namespace Product;

	class Relationship Extends \BaseXREF {
		public $variant_type = 'none';
		public $child_id;
		public $parent_id;

		/** @constructor */
		public function __construct() {
			$this->_tableName = "product_relations";
			$this->_tableUKColumns = ['parent_id', 'child_id'];	
		}

		/** @method __call(name, parameters)
		 * Polymorphism
		 */
		public function __call($name,$parameters) {
			if ($name == "get") return $this->getRelationShip($parameters[0],$parameters[1]);
		}

		/** @method add()
		 * Add a Product Item RelationShip, Parent to Child
		 * @param array $parameters
		 * @return bool
		 */
		public function add($parameters = []): bool {
			// Clear Previous Errors
			$this->clearError();

			// Initialize Database Service
			$database = new \Database\Service();

			// Validate Parameters
			$parent = new \Product\Group($parameters['parent_id']);
			if (!$parent->exists()) {
				$this->error("Parent group not found");
				return false;
			}
			$child = new \Product\Item($parameters['child_id']);
			if (!$child->exists()) {
				$this->error("Child item not found");
				return false;
			}
			if (empty($parameters['variant_type'])) $parameters['variant_type'] = 'none';
			if (! $this->validVariantType($parameters['variant_type'])) {
				$this->error("Invalid variant type");
				return false;
			}

			// Prepare Query to Add RelationShip
			$add_object_query = "
				INSERT
				INTO	product_relations
				(		parent_id,product_id,variant_type)
				VALUES
				(		?,?,?)
			";

			// Bind Parameters
			$database->AddParam($parameters['parent_id']);
			$database->AddParam($parameters['child_id']);
			$database->AddParam($parameters['variant_type']);

			// Execute Query
			$database->Execute($add_object_query);
			if ($database->ErrorMsg()) {
				$this->SQLError($database->ErrorMsg());
				return false;
			}
			
			// add audit log
			$auditLog = new \Site\AuditLog\Event();
			$auditLog->add(array(
				'instance_id' => $this->id,
				'description' => 'Added new '.$this->_objectName(),
				'class_name' => get_class($this),
				'class_method' => 'add'
			));

			return true;
		}

		/** @method update(parameters)
		 * Update a Product Item RelationShip
		 * @param array $parameters
		 * @return bool
		*/
		public function update($parameters = []) {
			// Clear Previous Errors
			$this->clearError();

			// Initialize Database Service
			$database = new \Database\Service();

			// Valid Parameters
			if (empty($this->id)) {
				$this->error("No relationship identified");
				return false;
			}
			if (empty($parameters['variant_type'])) {
				$parameters['variant_type'] = 'none';
			}
			if (! $this->validVariantType($parameters['variant_type'])) {
				$this->error("Invalid variant type");
				return false;
			}

			if ($this->variant_type == $parameters['variant_type']) {
				// Nothing to update
				return true;
			}

			// Prepare Query
			$update_object_query = "
				UPDATE	product_relations
				SET		variant_type = ?
				WHERE	id = ?
			";

			// Bind Parameters
			$database->AddParam($parameters['variant_type']);
			$database->AddParam($this->id);

			// Execute Query
			$database->Execute($update_object_query);
			if ($database->ErrorMsg()) {
				$this->SQLError($database->ErrorMsg());
				return false;
			}

			return true;
		}

		/** @method getRelationship(parent id, child id)
		 * Get a Product Item RelationShip
		 * @param int $parent_id
		 * @param int $child_id
		 * @return object|null
		 */
		public function getRelationship($parent_id,$child_id) {
			// Clear Previous Errors
			$this->clearError();

			// Initialize Database Service
			$database = new \Database\Service();

			// Validate Inputs
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

			// Prepare Query to Get Relationship
			$get_object_query = "
				SELECT	parent_id,
						product_id child_id,
						variant_type
				FROM	product_relations
				WHERE	parent_id = ?
				AND		product_id = ?
			";

			// Bind Parameters
			$database->AddParam($parent_id);
			$database->AddParam($child_id);

			// Execute Query
			$rs = $database->Execute($get_object_query);
			if ($database->ErrorMsg()) {
				$this->SQLError($database->ErrorMsg());
				return null;
			}
			$array = $rs->FetchRow();
			return (object) $array;
		}

		/** @method validVariantType(string)
		 * Validate the variant type
		 * @param string $type
		 * @return bool
		 */
		public function validVariantType($type) {
			$validationClass = new \Product\Item();
			return $validationClass->validVariantType($type);
		}

	}
