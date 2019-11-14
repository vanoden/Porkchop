<?php
	namespace Sales\Order;

	class Item extends \ORM\BaseModel {
		public $id;
		public $name;
		public $abbreviation;

		public function add($parameters) {
			$product = new \Product\Product($parameters['product_id']);
			if (! $product->id) {
				$this->_error = "Product not found";
				return false;
			}
			$line_number = $this->_next_line();

			$add_object_query = "
				INSERT
				INTO	sales_order_items
				(		id,line_number,product_id)
				VALUES
				(		null,?,?)
			";
			$GLOBALS['_database']->Execute($add_object_query,array($line_number,$product->id));
			if ($GLOBALS['_database']->ErrorMsg()) {
				$this->_error = "SQL Error in Sales::Item::add(): ".$GLOBALS['_database']->ErrorMsg();
				return false;
			}
			$this->id = $GLOBALS['_database']->Insert_ID();
			return $this->update($parameters);
		}

		public function update($parameters = array()) {
			$update_object_query = "
				UPDATE	sales_order_items
				SET		id = id";

			$bind_params = array();
			if (isset($parameters['product_id'])) {
				$product = new \Product\Product($parameters['product_id']);
				if (! $product->id) {
					$this->_error = "Product not found";
					return false;
				}
				$update_object_query .= ", product_id = ?";
				array_push($bind_params,$product->id);
			}
			if (isset($parameters['quantity'])) {
				$update_object_query .= ", quantity = ?";
				array_push($bind_params,$parameters['quantity']);
			}
			if (isset($parameters['serial_number'])) {
				$update_object_query .= ", serial_number = ?";
				array_push($bind_params,$parameters['serial_number']);
			}
			if (isset($parameters['description'])) {
				$update_object_query .= ", description ?";
				array_push($bind_params,$parameters['description']);
			}
			if (isset($parameters['unit_price'])) {
				$update_object_query .= ", unit_price = ?";
				array_push($bind_params,$paramters['unit_price']);
			}

			$update_object_query .= "
				WHERE	id = ?";
			array_push($bind_params,$this->id);

			$GLOBALS['_database']->Execute($update_object_query,$bind_params);
			if ($GLOBALS['_database']->ErrorMsg()) {
				$this->_error = "SQL Error in Sales::Item::update(): ".$GLOBALS['_database']->ErrorMsg();
				return false;
			}
			return $this->details();
		}

		public function get($order_id,$line_number) {
			$get_object_query = "
				SELECT	id
				FROM	sales_order_items
				WHERE	order_id = ?
				AND		line_number = ?
			";

			$rs = $GLOBALS['_database']->Execute($get_object_query,array($order_id,$line_number));
			if (! $rs) {
				$this->_error = "SQL Error in Sales::Item::get(): ".$GLOBALS['_database']->ErrorMsg();
				return false;
			}
			list($this->id) = $rs->FetchRow();
			if ($this->id) {
				app_log("Found country ".$this->id);
				return $this->details();
			}
			else {
				return false;
			}
		}

		public function details() {
			$get_details_query = "
				SELECT	*
				FROM	sales_order_items
				WHERE	id = ?
			";
			$rs = $GLOBALS['_database']->Execute($get_details_query,array($this->id));
			if (! $rs) {
				$this->_error = "SQL Error in Sales::Item::details(): ".$GLOBALS['_database']->ErrorMsg();
				return false;
			}
			$object = $rs->FetchNextObject(false);
			if ($this->id) {
				app_log("Got details for ".$this->id);
				$this->id = $object->id;
				$this->line_number = $object->line_number;
				$this->product_id = $object->product_id;
				$this->serial_number = $object->serial_number;
				$this->description = $object->description;
				$this->quantity = $object->quantity;
				$this->unit_price = $object->unit_price;
				return true;
			}
			else {
				return false;
			}
		}

		public function product() {
			return new \Product\Product($this->product_id);
		}

		public function error() {
			return $this->_error;
		}
	}
