<?php

namespace Product\Item;

class Part Extends \BaseXREF {
	public int $id = 0;
	protected $product_id = 0;		// ID of the product this part belongs to
	protected $part_product_id = 0;	// ID of the part in this assembly
	public $quantity = 0;			// Number of parts in this assembly
	protected $part_number = 0;		// Group Alternative Parts
	protected $_exists = false;		// True if record found

	public function __construct($id = null) {
		if (isset($id) && is_numeric($id)) {
			$this->id = $id;
			$this->details();
		}
	}

	public function add($parameters = array()) {
		// Clear Errors
		$this->clearError();

		// Prepare Database Service
		$database = new \Database\Service();

		// Validate parameters
		if (!isset($parameters['product_id']) || !is_numeric($parameters['product_id'])) {
			$this->error("Invalid product ID");
			return false;
		}
		if (!isset($parameters['part_product_id']) || !is_numeric($parameters['part_product_id'])) {
			$this->error("Invalid part product ID");
			return false;
		}
		if (!isset($parameters['quantity']) || !is_numeric($parameters['quantity'])) {
			$this->error("Invalid quantity");
			return false;
		}
		if (empty($parameters['part_number']) || !is_numeric($parameters['part_number'])) {
			// Get next part number for this product
			$part = new \Product\Item\Part();
			$part->product_id = $parameters['product_id'];
			$parameters['part_number'] = $part->nextPartNumber();
			if (!$parameters['part_number']) {
				$this->error("Error determining next part number: " . $part->error());
				return false;
			}
		}

		// Prepare Query to Add Part
		$add_part_query = "
			INSERT
			INTO	product_parts
			(		product_id,
					part_product_id,
					quantity,
					part_number
			)
			VALUES
			(		?, ?, ?, ?
			)
		";

		// Add parameters and execute query
		$database->AddParam($parameters['product_id']);
		$database->AddParam($parameters['part_product_id']);
		$database->AddParam($parameters['quantity']);
		$database->AddParam($parameters['part_number']);

		$database->Execute($add_part_query);
		if ($database->ErrorMsg()) {
			$this->SQLError($database->ErrorMsg());
			return false;
		}
		return true;
	}

	public function update($parameters = array()) {
		// Clear Errors
		$this->clearError();

		// Prepare Database Service
		$database = new \Database\Service();

		// Validate parameters
		if (!isset($this->id) || !is_numeric($this->id)) {
			$this->error("Invalid part ID");
			return false;
		}
		if (!isset($parameters['quantity']) || !is_numeric($parameters['quantity'])) {
			$this->error("Invalid quantity");
			return false;
		}

		// Prepare Query to Update Part
		$update_part_query = "
			UPDATE	product_parts
			SET		quantity = ?
			WHERE	id = ?
		";

		// Add parameters and execute query
		$database->AddParam($parameters['quantity']);
		$database->AddParam($this->id);

		$database->Execute($update_part_query);
		if ($database->ErrorMsg()) {
			$this->SQLError($database->ErrorMsg());
			return false;
		}
		return true;
	}

	public function delete() {
		// Clear Errors
		$this->clearError();

		// Prepare Database Service
		$database = new \Database\Service();

		if (!is_numeric($this->id)) {
			$this->error("Invalid part ID");
			return false;
		}

		// Prepare Query to Delete Part
		$delete_part_query = "
			DELETE FROM product_parts
			WHERE id = ?
		";

		// Add parameter and execute query
		$database->AddParam($this->id);

		if (!$database->Execute($delete_part_query)) {
			$this->SQLError($database->ErrorMsg());
			return false;
		}
		
		return true;
	}

	public function get($product_id, $part_id) {
		// Clear Errors
		$this->clearError();

		// Initialize Database Service
		$database = new \Database\Service();

		$product = new \Product\Item($product_id);
		if (! $product->exists()) {
			$this->error("Product not found");
			return false;
		}
		$part = new \Product\Item($part_id);
		if (! $part->exists()) {
			$this->error("Part Product not found");
			return false;
		}

		// Prepare Query to Get Part
		$get_object_query = "
			SELECT	id,
					product_id,
					part_product_id,
					part_number,
					quantity
			FROM	product_parts
			WHERE	product_id = ?
			AND		part_id";

		// Bind Parameters
		$database->AddParam($product->id);
		$database->AddParam($part->id);

		// Execute Query
		$rs = $database->Execute($get_object_query);
		if (! $rs) {
			$this->SQLError($database->error());
			return false;
		}
	}

	public function details() {
		// Clear Errors
		$this->clearError();

		// Prepare Database Service
		$database = new \Database\Service();

		// Prepare Query
		$get_details_query = "
			SELECT	*
			FROM	product_parts
			WHERE	id = ?";

		$database->AddParam($this->id);

		$rs = $database->Execute($get_details_query);
		if (! $rs) {
			$this->SQLError($database->error());
			return false;
		}

		$object = $rs->FetchNextObject(false);
		if ($object->id) {
			$this->id = $object->id;
			$this->product_id = $object->product_id;
			$this->part_product_id = $object->part_product_id;
			$this->quantity = $object->quantity;
			$this->part_number = $object->part_number;
			$this->_exists = true;
			return true;
		}
		else {
			$this->id = -1;
			$this->_exists = false;
			return false;
		}
	}

	public function product() {
		if ($this->product_id) {
			return new \Product\Item($this->product_id);
		}
		return null;
	}

	public function part() {
		if ($this->part_product_id) {
			return new \Product\Item($this->part_product_id);
		}
		return null;
	}

	public function nextPartNumber() {
		// Clear Errors
		$this->clearError();

		// Prepare Database Service
		$database = new \Database\Service();

		// Prepare Query to find next part number
		$next_part_number_query = "
			SELECT	COALESCE(MAX(part_number),0) + 1 AS next_part_number
			FROM	product_parts
			WHERE	product_id = ?
		";

		$database->AddParam($this->product_id);

		$rs = $database->Execute($next_part_number_query);
		if (! $rs) {
			$this->SQLError($database->error());
			return false;
		}

		if ($object = $rs->FetchNextObject(false)) {
			return $object->next_part_number;
		}
		return 1;
	}

	public function exists(): bool {
		return $this->_exists;
	}
};