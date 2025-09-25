<?php
	namespace Sales;

	enum DocumentType: string {
		case PURCHASE = 'PURCHASE_ORDER';			// Buying product from a vendor
		case SALES = 'SALES_ORDER';					// Selling product to a customer
		case INVENTORY = 'INVENTORY_CORRECTION';	// Inventory Correction
		case RETURN = 'RETURN_ORDER';				// Return Document
	}

	enum DocumentStatus: string {
		case NEW = 'NEW';					// Document Created but not released
		case QUOTE = 'QUOTE';				// Quote provided to customer waiting for approval
		case CANCELLED = 'CANCELLED';		// Document Cancelled by Customer or Vendor
		case APPROVED = 'APPROVED';			// Document Approved by Customer
		case ACCEPTED = 'ACCEPTED';			// Document Accepted by Vendor
		case COMPLETED = 'COMPLETED';		// Billing, Fulfillment and Receipt All Closed
	}

	/** @class Document
	 * Models Sales and Purchase Documents as well as Inventory Corrections
	 */
	abstract class Document extends \BaseModel {
		public $code;						// Unique Code for Document
		protected DocumentStatus $status;	// Document Status
		protected DocumentType $type;		// Document Type
		public $customer_id;				// ID of \Register\Customer purchasing product
		public $customer_organization_id;	// ID of \Register\Organization to which the customer belongs
		public $seller_id;					// ID of \Register\Customer selling product
		public $seller_organization_id;		// ID of \Register\Organization to which the seller belongs
		public $local_document_number;		// Our Order Number For Sales Orders, Our Purchase Order Number for Purchase Orders
		public $remote_document_number;		// Their Order Number For Sales Orders, Their Purchase Order Number for Purchase Orders
		public $billing_location_id;		// ID of \Register\Location for billing
		public $shipping_location_id;		// ID of \Register\Location to ship product to

		public function __construct($id = 0) {
			$this->_tableName = "sales_documents";
			$this->_tableNumberColumn = 'local_document_number';
			$this->_addTypes(array('SALE','PURCHASE','INVENTORY','RETURN'));
			$this->_addStatus(array('NEW','QUOTE','CANCELLED','APPROVED','ACCEPTED','COMPLETE'));
			parent::__construct($id);
		}

		public function add($parameters = []) {
			// Clear Previous Errors
			$this->clearError();

			// Initialize Database Service
			$database = new \Database\Service();

			// Validate Inputs
			$customer = new \Register\Customer($parameters['customer_id']);
			if (! $customer->id) {
				$this->error("Customer not found");
				return false;
			}

            if (isset($parameters['seller_id'])) {
    			$seller = new \Register\Admin($parameters['seller_id']);
	    		if (! $seller->id) {
		    		$this->error("Seller not found");
			    	return false;
			    }
            }

			if (!empty($parameters['type'])) {
				if ($this->validType($parameters['type'])) $type = $parameters['type'];
				else {
					$this->addError("Invalid type");
					return false;
				}
			}
			else {
				$this->error("Document Type Required");
				return false;
			}

			if (!empty($parameters['status'])) {
				if ($this->validStatus($parameters['status'])) $status = $parameters['status'];
				else {
					$this->addError("Invalid status");
					return false;
				}
			}
			else $status = 'NEW';
			if (!empty($parameters['code'])) {
				if ($this->validCode($parameters['code'])) $code = $parameters['code'];
				else {
					$this->addError("Invalid code");
					return false;
				}
			}
			else $code = uniqid();

			// Prepare Query
			$add_object_query = "
				INSERT
				INTO	`".$this->_tableName."`
				(		id,code,type,status,customer_id,customer_organization_id,seller_id,seller_organization_id)
				VALUES
				(		null,?,?,?,?,?,?,?)
			";

			// Bind Parameters
			$database->AddParam($code);
			$database->AddParam($type);
			$database->AddParam($status);
			$database->AddParam($customer->id);
			$database->AddParam($customer->organization_id);
			$database->AddParam($seller->id);
			$database->AddParam($seller->organization_id);

			$database->Execute($add_object_query);
			if ($database->ErrorMsg()) {
				$this->SQLError($database->ErrorMsg());
				return false;
			}
			$this->id = $database->Insert_ID();
			
            // audit the add event
            $auditLog = new \Site\AuditLog\Event();
            $auditLog->add(array(
                'instance_id' => $this->id,
                'description' => 'Added new '.$this->_objectName(),
                'class_name' => get_class($this),
                'class_method' => 'add'
            ));

			$this->addEvent(array('new_status' => $status,'user_id' => $GLOBALS['_SESSION_']->customer->id,'type' => "CREATE"));
			return $this->update($parameters);
		}

		public function update($parameters = []): bool {
			// Clear Previous Errors
			$this->clearError();

			// Initialize Database Service
			$database = new \Database\Service();

			// Prepare Query
			$update_object_query = "
				UPDATE	`".$this->_tableName."`
				SET		id = id";

			if (isset($parameters['status'])) {
				if (! $this->validStatus($parameters['status'])) {
					$this->error("Invalid Status");
					return false;
				}
				$update_object_query .= ", status = ?";
				$database->AddParam($parameters['status']);
			}
			if (isset($parameters['customer_document_number'])) {
				if (! $this->validDocumentNumber($parameters['customer_document_number'])) {
					$this->error("Invalid Document Number");
					return false;
				}
				$update_object_query .= ", customer_document_number = ?";
				$database->AddParam($parameters['customer_document_number']);
			}

			if (isset($parameters['organization_id'])) {
				$organization = new \Register\Organization($parameters['organization_id']);
				if (! $organization->exists()) {
					$this->error("Organization not found");
					return false;
				}
				$update_object_query .= ", organization_id = ?";
				$database->AddParam($parameters['organization_id']);
			}

			if (isset($parameters['customer_id'])) {
				$customer = new \Register\Customer($parameters['customer_id']);
				if (! $customer->exists()) {
					$this->error("Customer not found");
					return false;
				}
				$update_object_query .= ", customer_id = ?";
				$database->AddParam($parameters['customer_id']);
			}

			if (isset($parameters['salesperson_id'])) {
				$admin = new \Register\Admin($parameters['salesperson_id']);
				if (! $admin->exists()) {
					$this->error("Salesperson not found");
					return false;
				}
				$update_object_query .= ", salesperson_id = ?";
				$database->AddParam($parameters['salesperson_id']);
			}
			
			if (isset($parameters['billing_location_id'])) {
				$location = new \Register\Location($parameters['billing_location_id']);
				if (! $location->exists()) {
					$this->error("Billing Location not found");
					return false;
				}
				$update_object_query .= ", billing_location_id = ?";
				$database->AddParam($parameters['billing_location_id']);
			}
			
			if (isset($parameters['shipping_location_id'])) {
				$location = new \Register\Location($parameters['shipping_location_id']);
				if (! $location->exists()) {
					$this->error("Shipping Location not found");
					return false;
				}
				$update_object_query .= ", shipping_location_id = ?";
				$database->AddParam($parameters['shipping_location_id']);
			}
			
			if (isset($parameters['shipping_vendor_id'])) {
				$shipping_vendor = new \Shipping\Vendor($parameters['shipping_vendor_id']);
				if (! $shipping_vendor->exists()) {
					$this->error("Shipping vendor not found");
					return false;
				}
				$update_object_query .= ", shipping_vendor_id = ?";
				$database->AddParam($parameters['shipping_vendor_id']);
			}

			$update_object_query .= "
				WHERE	id = ?";
			$database->AddParam($this->id);

			$database->Execute($update_object_query);
			if ($database->ErrorMsg()) {
				$this->SQLError($database->ErrorMsg());
				return false;
			}
			
			// audit the update event
			$auditLog = new \Site\AuditLog\Event();
			$auditLog->add(array(
				'instance_id' => $this->id,
				'description' => 'Updated '.$this->_objectName(),
				'class_name' => get_class($this),
				'class_method' => 'update'
			));	

            $this->addEvent(array(
                'new_status' => $parameters['status'] ?? $this->status,
                'user_id'    => $GLOBALS['_SESSION_']->customer->id,
                'type'       => "UPDATE"
            ));
			return $this->details();
		}

		/** @method public getByCustomerDocumentNumber(customer id, number)
		 * Query the database for an order by customer and customer order number
		 * @param int $customer_id ID of the customer
		 * @param string $number Customer Document or Purchase Number
		 * @return bool True if the order was found and loaded, false if not
		 */
		public function getByCustomerDocumentNumber($customer_id,$number): bool {
			// Clear Prevoius Error
			$this->clearError();

			// Initialize Database Service
			$database = new \Database\Service();

			// Prepare Query
			$get_order_query = "
				SELECT	id
				FROM	`".$this->_tableName."`
				WHERE	customer_id = ?
				AND		customer_order_number = ?
			";

			// Bind Parameters
			$database->AddParam($customer_id);
			$database->AddParam($number);

			$rs = $database->Execute($get_order_query);
			if (! $rs) {
				$this->SQLError($database->ErrorMsg());
				return false;
			}
			list($id) = $rs->FetchRow();
			$this->id = $id;
			return $this->details();
		}

		/** @method public seller()
		 *  Get the \Register\Admin who is the seller for this order
		 *  @return \Register\Admin
		 */
		public function seller() {
			return new \Register\Admin($this->seller_id);
		}

		/** @method public customer()
		 *  Get the \Register\Customer who is the buyer for this order
		 *  @return \Register\Customer
		 */
		public function customer() {
			return new \Register\Customer($this->customer_id);
		}

		/** @method public billing_location()
		 *  Get the \Register\Location for billing
		 *  @return \Register\Location
		 */
		public function billing_location() {
			return new \Register\Location($this->billing_location_id);
		}

		/** @method public shipping_location()
		 *  Get the \Register\Location to ship product to
		 *  @return \Register\Location
		 */
		public function shipping_location() {
			return new \Register\Location($this->shipping_location_id);
		}

		/** @method public quote()
		 *  Set the order status to QUOTE
		 *  @return bool True if successful, false if not
		 */
		public function quote() {
			if (! $this->update(array('status' => 'QUOTE'))) return false;
			if (! $this->addEvent(
				array(
					'order_id'	=> $this->id,
					'user_id'	=> $GLOBALS['_SESSION_']->customer->id,
					'new_status'	=> 'QUOTE'
				)
			)) {
				$this->error("Unable to add event: ".$this->error());
				return false;
			}
			return true;
		}

		/** @method public approve()
		 *  Set the order status to APPROVED
		 *  @return bool True if successful, false if not
		 */
        public function approve() {
            if ($this->update(array('status' => 'APPROVED'))) {
                $this->addEvent(array('order_id' => $this->id, 'new_status' => 'APPROVED'));
                $customer = new \Register\Customer($this->customer_id);
                $service_request = new \Support\Request();
                if ($service_request->add(array(
                    'customer_id' => $customer->id,
                    'organization_id' => $customer->organization()->id,
                    'type'          => 'ORDER',
                    'code'          => 'SO_'.$this->id
                ))) {
					app_log("Created request ".$service_request->code);
					$line = 0;
                    foreach ($this->items() as $item) {
						app_log("Adding product ".$item->product_id);
						$line ++;
                        if ($ticket = $service_request->addItem(array(
                            'product_id'    => $item->product_id,
                            'quantity'      => $item->quantity,
                            'status'        => 'NEW',
                            'description'   => $item->description,
							'line'			=> $line
                        ))) {
							app_log("Added item ".$item->product_id);
						}
						else {
							$this->error($service_request->error());
							return false;
						}
                    }
                }
				return true;
			}
			else {
				return false;
			}
        }

		/** @method public cancel($reason = '')
		 *  Set the order status to CANCELLED
		 *  @param string $reason Reason for cancellation
		 *  @return bool True if successful, false if not
		 */
		public function cancel($reason = '') {
			if (! $this->update(array('status' => 'CANCELLED','message' => $reason))) return false;
			if (! $this->addEvent(
				array(
					'order_id' => $this->id,
					'user_id' => $GLOBALS['_SESSION_']->customer->id,
					'new_status' => 'CANCELLED',
					'message'	=> $reason
				)
			)) return false;
			return true;
		}

		/** @method public addItem(parameters)
		 * Add an item to the order
		 * @param array $parameters Parameters for the item
		 * [unit_price] => float Price per unit
		 * [quantity] => int Quantity of the item
		 * [description] => string Description of the item
		 * [product_id] => int ID of the product
		 * [line_number] => int Line number for the item
		 * @return \Sales\Document\Item|null The added item or null on error
		*/
		public function addItem($parameters): \Sales\Document\Item|null {
			if (!isset($parameters['unit_price'])) {
				$this->error("Price required for line item");
				return null;
			}
			$parameters['order_id'] = $this->id;
			$orderItem = new \Sales\Document\Item();
			if ($orderItem->add($parameters)) {
				return $orderItem;
			}
			else {
				$this->error($orderItem->error());
				return null;
			}
		}

		/** @method public getItem(line_number)
		 * Get an item from the order by line number
		 * @param int $line_number Line number of the item
		 * @return \Sales\Document\Item The item object
		 */
		public function getItem($line_number) {
			return new \Sales\Document\Item($line_number);
		}

		/** @method public dropItem(line_id)
		 * Drop an item from the order by line ID
		 * @param int $line_id Line ID of the item to drop
		 * @return bool True if successful, false if not
		 */
		public function dropItem($line_id) {
			$item = new \Sales\Document\Item($line_id);
			return $item->update(array('status' => 'VOID'));
		}

		/** @method public items(parameters)
		 * Get a list of items in the order
		 * @param array $parameters Optional parameters for filtering items
		 * @return \Sales\Document\ItemList|null List of items or null on error
		 */
		public function items($parameters = array()): \Sales\Document\ItemList|null {
			$parameters['order_id'] = $this->id;
			$itemList = new \Sales\Document\ItemList();
			$items = $itemList->find($parameters);
			if ($itemList->error()) {
				$this->error($itemList->error());
				return null;
			}
			else {
				return $items;
			}
		}

		/** @method public addEvent(parameters)
		 * Add an event to the order
		 * @param array $parameters Parameters for the event
		 * @return \Sales\Document\Event|false The added event or false on error
		 */
		private function addEvent($parameters = array()) {
			$event = new \Sales\Document\Event();
			$parameters['order_id'] = $this->id;
			if (! $event->add($parameters)) {
				$this->error($event->error());
				return false;
			}
			return $event;
		}

		/** @method public lastItemID()
		 * Get the last item ID added to the order
		 * @return int Last item ID
		 */
		public function lastItemID(): int {
			return $this->maxLineNumber();
		}

		/** @method public number()
		 * Get the customer order number for the order
		 * @return string Customer order number
		 */
		public function number(): string {
			return $this->local_document_number;
		}

		/** @method public total()
		 * Get the total amount for the order
		 * @return float Total amount
		 */
		public function total(): float {
			$items = $this->items(array('status' => 'OPEN'));
			$total = 0;
			foreach ($items as $item) {
				$total += $item->total();
			}
			return $total;
		}

		/** @method public date_created()
		 * Get the date the order was created
		 * @return string Date the order was created
		 */
		public function date_created() {
			$eventlist = new \Sales\Document\EventList();
			$first =  $eventlist->first(array('order_id' => $this->id, 'new_status' => 'NEW'));
			if (empty($first)) return "Unknown";
			else return $first->date_event;
		}

        /** @method public maxLineNumber(salesDocumentId)
         * get max value from a sales order line number based on the order it's in
		 * @param int $salesDocumentId The sales order ID
		 * @return int The maximum line number in the order
         */
		public function maxLineNumber() {
			// Clear Previous Errors
			$this->clearError();

			// Initialize Database Service
			$database = new \Database\Service();

			// Prepare Query
			$get_object_query = "
				SELECT	MAX(`line_number`)
				FROM	`$this->_tableName`
				WHERE	`order_id` = ?
			";

			// Bind Parameters
			$database->AddParam($this->id);

			// Execute Query
			$rs = $database->Execute($get_object_query);
			if (!$rs) {
				$this->SQLError($database->ErrorMsg());
				return false;
			}
			list($value) = $rs->FetchRow();
			return $value;
		}
	}
