<?php
	namespace Shipping;
	class Package extends \ORM\BaseModel {
	
		public $id;
		public $shipment;
		public $number;
		public $tracking_code;
		public $status;
		public $condition;
		public $height;
		public $width;
		public $depth;
		public $weight;
		public $shipping_cost;
		public $date_received;
		public $user_received_id;
		public $tableName = 'shipping_packages';
        public $fields = array('id','shipment_id','number','tracking_code','status','condition','height','width','depth','weight','shipping_cost','date_received','user_received_id','vendor_id');
        
        /**
         * add by params
         * 
         * @param array $parameters, name value pairs to add and populate new object by
         */
        public function add($parameters = array()) {

            // shipment_id is required            
            if (empty($parameters['shipment_id'])) {
				$this->_error = "Shipment ID Required";
				return false;
            }

            // check shipment exists
        	$shipment = new \Shipping\Shipment($parameters['shipment_id']);
			if (! $shipment->id) {
				$this->_error = "Shipment Not Found";
				return false;
			}

        	// get next number for shipment
			$number = $this->get_next_number($shipment->id);
			if (! isset($number)) return false;			
			$parameters['number'] = $number;

		    // add entry	
            return parent::add($parameters);
		}

        /**
         * get object in question
         */
		public function getByShippingID($id=0) {
			$getObjectQuery = "SELECT * FROM $this->tableName WHERE	shipment_id = ?";
			$rs = $this->execute($getObjectQuery, array($id));
            $object = $rs->FetchNextObject(false);
			if (is_numeric($object->id)) {
    			foreach ($this->fields as $field) $this->$field = $object->$field;
			}
		}

        /**
         * update by params
         * 
         * @param array $parameters, name value pairs to update object by
         */
        public function update($parameters = array()) {
			if (isset($parameters['user_received']) && is_numeric($parameters['user_received'])) {
				$customer = new \Register\Customer($parameters['user_received']);
				if (! $customer->id) {
					$this->_error = "Customer not found";
					return false;
				}
			}

		    // update entry
            return parent::update($parameters);
        }
		
		/**
		 * get person who recieved the page
		 */
		public function user_received() {
			return new \Register\Customer($this->user_received_id);
		}

		/**
		 * get shipment this package is in
		 */
		public function shipment() {
			return new \Shipping\Shipment($this->shipment_id);
		}
		
		/**
		 * get the next highest shipping number
		 *
         * @param number $shipment_id
		 */
		private function get_next_number($shipment_id) {
			$rs = $this->execute("SELECT max(`number`) FROM shipping_packages WHERE shipment_id = ?", array($shipment_id));
			list($number) = $rs->FetchRow();
			if (is_numeric($number)) return $number + 1;
			return 1;
		}

		public function add_item($parameters = array()) {
			$product = new \Product\Item($parameters['product_id']);
			if (! $product->id) {
				$this->_error = "Product '".$parameters['product_id']."' not found";
				return false;
			}
			if (empty($parameters['description'])) {
				$parameters['description'] = $product->description;
			}
			if (! isset($parameters['quantity'])) $parameters['quantity'] = 1;

			$item = new \Shipping\Item();
			if ($item->add(array(
				'shipment_id'	=> $this->shipment_id,
				'package_id'	=> $this->id,
				'serial_number'	=> $parameters['serial_number'],
				'condition'		=> $parameters['condition'],
				'product_id'	=> $product->id,
				'quantity'		=> $parameters['quantity'],
				'description'	=> $parameters['description']
			))) return $item;
			else {
				$this->_error = "Error adding item to package: ".$item->error();
				return null;
			}
		}

		public function items() {
			if (empty($this->id)) return array();
			$itemList = new \Shipping\ItemList();
			return $itemList->find(array('package_id' => $this->id));
		}
	}
