<?php
	/** @class Register\OwnedProduct
	 * Represents a product owned by an organization, including quantity and expiration information
	*/
	namespace Register\Organization;

	class OwnedProduct Extends \BaseClass {
		public int $organization_id;
		public int $product_id;
		public int $quantity = 0;
		public ?string $date_expires;

		/** @method __construct()
		 * Constructor
		 * @param int $org_id 
		 * @param int $product_id 
		 */
		public function __construct(int $org_id, int $product_id) {
			$this->organization_id = $org_id;
			$this->product_id = $product_id;
			if ($this->organization_id > 0 && $this->product_id > 0) $this->details();
		}

		/** @method add(parameters)
		 * Add a quantity of the product to the organization's inventory
		 * @param array $parameters 
		 * @return bool 
		 */
		public function add($parameters = []): bool {

			$this->clearError();
			$organization = new \Register\Organization($this->organization_id);
			if ($organization->id < 1) {
				$this->error("Organization not found");
				return false;
			}
			$product = new \Product\Item($this->product_id);
			if ($product->id < 1) {
				$this->error("Product not found");
				return false;
			}

			if (empty($parameters['quantity']) || $parameters['quantity'] <= 0) {
				$this->error("Quantity must be greater than 0");
				return false;
			}

			$database = new \Database\Service();

			$add_product_query = "
				INSERT
				INTO    register_organization_products
				(       organization_id,
						product_id,
						quantity
				)
				VALUES
				(       ?,
						?,
						?
				)
				ON DUPLICATE KEY
				UPDATE
						quantity = quantity + ?
			";
			//print_r("Adding ".$parameters["quantity"]." of product ".$this->product_id." for organization ".$this->organization_id."<br>\n");
			app_log("Adding ".$parameters["quantity"]." of product ".$this->product_id." for organization ".$this->organization_id,'notice',__FILE__,__LINE__);
			//$database->trace(9);
			$database->AddParam($organization->id);
			$database->AddParam($product->id);
			$database->AddParam($parameters['quantity']);
			$database->AddParam($parameters['quantity']);

			$database->Execute($add_product_query);
			if ($database->ErrorMsg()) {
				$this->SQLError($database->ErrorMsg());
				return false;
			}

			// audit the add event
			$auditLog = new \Site\AuditLog\Event();
			$auditLog->add(array(
				'instance_id' => $organization->id,
				'description' => 'Added quantity ' . $parameters['quantity'] . ' of product ID ' . $product->code.', expires: '.($parameters['date_expires'] ?? 'N/A'),
				'class_name' => get_class($this),
				'class_method' => 'add'
			),true);

			return $this->details();
		}

		/** @method public update($parameters)
		 * Update the owned product with new information such as quantity or expiration date
		 * @param array $parameters
		 */
		public function update($parameters = []): bool {
			// Clear any existing errors
			$this->clearError();

			// Initialize Database Service
			$database = new \Database\Service();

			$organization = new \Register\Organization($this->organization_id);
			if ($organization->id < 1) {
				$this->error("Organization not found");
				return false;
			}

			$product = new \Product\Item($this->product_id);
			if ($product->id < 1) {
				$this->error("Product not found");
				return false;
			}

			// Prepare Query
			$update_query = "
				UPDATE  register_organization_products
				SET     id = id
			";

			$audit_events = [];

			// Add Parameters
			if (isset($parameters['quantity']) && $parameters['quantity'] >= 0) {
				$update_query .= ",
						quantity = ?";
				$database->AddParam($parameters['quantity']);
				$audit_events[] = "Updated quantity from " . $this->quantity . " to " . $parameters['quantity'];
			}
			if (isset($parameters['date_expires']) && !empty($parameters['date_expires']) && $parameters['date_expires'] != $this->date_expires) {
				$update_query .= ",
						date_expires = ?";
				$database->AddParam($parameters['date_expires']);
				$audit_events[] = "Updated expiration date from " . $this->date_expires . " to " . $parameters['date_expires'];
			}

			if (empty($audit_events)) {
				$this->error("No valid fields to update");
				return false;
			}

			// Prepare Query
			$update_query .= "
				WHERE   organization_id = ?
				AND     product_id = ?
			";
			$database->AddParam($this->organization_id);
			$database->AddParam($this->product_id);

			$rs = $database->Execute($update_query);
			if (! $rs) {
				$this->SQLError($database->ErrorMsg());
				return false;
			}

			$audit_record = new \Site\AuditLog\Event();
			$audit_record->add(array(
				'instance_id' => $organization->id,
				'description' => "Product ".$product->code." updated: ".implode("; ", $audit_events),
				'class_name' => get_class($this),
				'class_method' => 'update'
			),true);

			return $this->details();
		}

		/**
		 * Consume a quantity of the owned product
		 * @param int $quantity 
		 * @return bool 
		 */
		public function consume(int $quantity = 1): bool {
			$this->clearError();

			// Initialize Database Service
			$database = new \Database\Service();

			$organization = new \Register\Organization($this->organization_id);
			if ($organization->id < 1) {
				$this->error("Organization not found");
				return false;
			}

			$product = new \Product\Item($this->product_id);
			if ($product->id < 1) {
				$this->error("Product not found");
				return false;
			}

			// Validate Quantity
			$on_hand = $this->count();
			if ($quantity > $on_hand) {
				$this->error("Less than $quantity available");
				return false;
			}

			// Build Query
			$use_product_query = "
				UPDATE  register_organization_products
				SET     quantity = quantity - ?
				WHERE   organization_id = ?
				AND     product_id = ?
			";

			// Add Parameters
			$database->AddParam($quantity);
			$database->AddParam($this->organization_id);
			$database->AddParam($this->product_id);

			// Execute Query
			$database->Execute($use_product_query);
			if ($database->ErrorMsg()) {
				$this->SQLError($database->ErrorMsg());
				return false;
			}

			$audit_events = ["Consumed $quantity of product ID " . $this->product_id];

			$audit_record = new \Site\AuditLog\Event();
			$audit_record->add(array(
				'instance_id' => $organization->id,
				'description' => "Product ".$product->code." consumed: ".implode("; ", $audit_events),
				'class_name' => get_class($this),
				'class_method' => 'consume'
			),true);

			return $this->details();
		}

		/**
		 * Get the quantity of the owned product
		 * @return int 
		 */
		public function count(): int {
			$this->details();
			return $this->quantity;
		}

		/**
		 * Get the details of the owned product
		 * @return bool 
		 */
		public function details(): bool {
			$this->clearError();

			// Initialize Database Service
			$database = new \Database\Service();

			// Build Query
			$get_details_query = "
				SELECT  organization_id,
						product_id,
						quantity,
						date_expires
				FROM    register_organization_products
				WHERE   organization_id = ?
				AND		product_id = ?
			";

			// Add Parameters
			$database->AddParam($this->organization_id);
			$database->AddParam($this->product_id);

			// Execute Query
			$rs = $database->Execute($get_details_query);
			if (! $rs) {
				$this->SQLError($database->ErrorMsg());
				return false;
			}
			$object = $rs->FetchNextObject(false);

			if (!empty($object->organization_id)) {
				$this->organization_id = $object->organization_id;
				$this->product_id = $object->product_id;
				$this->quantity = $object->quantity;
				$this->date_expires = $object->date_expires;
			}
			else {
				$this->quantity = 0;
			}
			return true;
		}

		/**
		 * Get the organization that owns this product
		 * @return Organization 
		 */
		public function organization(): \Register\Organization {
			return new \Register\Organization($this->organization_id);
		}

		/**
		 * Get the product that is owned by this organization
		 * @return \Product\Item
		 */
		public function product(): \Product\Item {
			return new \Product\Item($this->product_id);
		}

		/** @method expired()
		 * Determine if the owned product is expired based on the date_expires field
		 * @return bool True if expired, false if not expired or no expiration date set
		 */
		public function expired(): bool {
			if (empty($this->date_expires)) return false;
			$current_date = new \DateTime();
			$expiration_date = new \DateTime($this->date_expires);
			return $current_date > $expiration_date;
		}
	}
