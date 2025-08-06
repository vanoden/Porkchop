<?php

namespace Product\Item;

class PartList Extends \BaseListClass {
	public function findAdvanced(array $parameters, array $advanced, array $controls): array {
		// Clear previous errors
		$this->clearError();

		// Reset Count
		$this->resetCount();

		// Initialize Database Service
		$database = new \Database\Service();

		// Prepare Query to find Parts
		$find_objects_query = "
			SELECT	id
			FROM	product_parts
			WHERE	id = id
		";

		if (!empty($parameters['product_id'])) {
			$product = new \Product\Item($parameters['product_id']);
			if (! $product->exists()) {
				$this->error("Product not found");
				return [];
			}
			$find_objects_query .= "
				AND	product_id = ?";
			$database->AddParam($product->id);
		}

		if (!empty($parameters['part_id'])) {
			$part = new \Product\Item($parameters['part_id']);
			if (! $part->exists()) {
				$this->error("Part not found");
				return [];
			}
			$find_objects_query .= "
				AND	part_number_id = ?";
			$database->AddParam($part->id);
		}

		if (!empty($parameters['part_number'])) {
			if (!is_numeric($parameters['part_number'])) {
				$this->error("Invalid part number");
				return [];
			}
			$find_objects_query .= "
				AND	part_number_id = ?";
			$database->AddParam($part->id);
		}

		$rs = $database->Execute($find_objects_query);
		if (! $rs) {
			$this->SQLError($database->error());
			return [];
		}

		$objects = [];
		while (list($id) = $rs->FetchRow()) {
			$object = new \Product\Item\Part($id);
			$this->incrementCount();
			$objects[] = $object;
		}
		return $objects;
	}
}