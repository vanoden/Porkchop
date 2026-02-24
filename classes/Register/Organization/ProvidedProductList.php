<?php
	/** @class Register\Organization\ProvidedProductList
	 * Represents a list of products provided by an organization. This is used to link organizations to the products they provide.  Products are Products.
	 */
	namespace Register\Organization;

	class ProvidedProductList extends \Product\ProductList {
		public function add($product, $parameters = []): bool {
			$this->clearError();

			if ($product instanceof \Register\Organization\ProvidedProduct) {
				$providedProduct = $product;
			}
			elseif ($product instanceof \Product\Item) {
				$productId = $product->id;
				$providedProduct = new ProvidedProduct();
				if ($parameters) {
					$providedProduct->current_price = $parameters['current_price'] ?? null;
					$providedProduct->pack_quantity = $parameters['pack_quantity'] ?? null;
					$providedProduct->units = $parameters['units'] ?? null;
					$providedProduct->notes = $parameters['notes'] ?? null;
				}
			}
			elseif (is_numeric($product)) {
				$productId = $product;
				$product = new \Product\Item($productId);
				$providedProduct = new ProvidedProduct();
				if ($parameters) {
					$providedProduct->current_price = $parameters['current_price'] ?? null;
					$providedProduct->pack_quantity = $parameters['pack_quantity'] ?? null;
					$providedProduct->units = $parameters['units'] ?? null;
					$providedProduct->notes = $parameters['notes'] ?? null;
				}

			}
			else {
				$this->error("Invalid product provided");
				return false;
			}

			if (! $providedProduct->id) {
				$this->error("Invalid product provided");
				return false;
			}
			if (! $this->organization_id) {
				$this->error("Organization ID not set for this ProvidedProductList");
				return false;
			}

			// Initialize Database
			$database = new \Database\Service();

			// Prepare Query
			$add_provided_product_query = "
				INSERT
				INTO    organization_products_provided
				(       organization_id,
						product_item_id,
						current_price,
						pack_quantity,
						units,
						notes,
						date_added
				)
				VALUES
				(       ?,
						?,
						?,
						?,
						?,
						?,
						sysdate()
				)
			";

			// Add Parameters
			$database->AddParam($this->organization_id);
			$database->AddParam($providedProduct->product_item_id);
			$database->AddParam($providedProduct->current_price);
			$database->AddParam($providedProduct->pack_quantity);
			$database->AddParam($providedProduct->units);
			$database->AddParam($providedProduct->notes);
	
			// Execute Query
			if (! $database->Execute($add_provided_product_query)) {
				$this->SQLError("Error adding provided product: ".$database->ErrorMsg());
				return false;
			}

			$providedProduct->id = $database->Insert_ID();

			return $providedProduct->details();
		}
	}