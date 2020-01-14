<?php
	namespace Shipping;
	
	class Shipment extends \ORM\BaseModel {
	
		public $id;
		public $code;
		public $document_number;
		public $date_entered;
		public $date_shipped;
		public $status;
		public $send_contact_id;
		public $send_location_id;
		public $rec_contact_id;
		public $rec_location_id;
		public $vendor_id;
		public $tableName = 'shipping_shipments';
        public $fields = array('id','code','document_number','date_entered','date_shipped','status','send_contact_id','send_location_id','rec_contact_id','rec_location_id','vendor_id', 'instructions');
        
        /**
         * add by params
         * 
         * @param array $parameters, name value pairs to add and populate new object by
         */
		public function add($parameters = array()) {
			$this->_error = null;
		
			if (! isset($parameters['code'])) $parameters['code'] = uniqid();
			if (! isset($parameters['status'])) $parameters['status'] = 'NEW';
			if (! isset($parameters['date_entered'])) $parameters['date_entered'] = date('Y-m-d H:i:s');
			
			if (isset($parameters['send_customer_id'])) {
				$parameters['send_contact_id'] = $parameters['send_customer_id'];
			}
			else {
				$this->_error = "Sending contact required";
				return false;
			}
			if (! isset($parameters['send_location_id'])) {
				$this->_error = "Sending location required";
				return false;
			}
			if (isset($parameters['receive_customer_id'])) {
				$parameters['rec_contact_id'] = $parameters['receive_customer_id'];
			}
			else {
				$this->_error = "Receiving contact required";
				return false;
			}
			if (isset($parameters['receive_location_id'])) {
				$parameters['rec_location_id'] = $parameters['receive_location_id'];
			}
			else {
				$this->_error = "Receiving location required";
				return false;
			}
			if (! isset($parameters['document_number'])) {
				$this->_error = "Document number required";
				return false;
			}
			
		    return parent::add($parameters);
		}
		
        /**
         * update by params
         * 
         * @param array $parameters, name value pairs to update object by
         */
		public function update($parameters = array()) {
			if (isset($parameters['type']) && isset($parameters['number'])) $parameters['document_number'] = sprintf("%s-%06d",$parameters['type'],$parameters['number']);
            return parent::update($parameters);
		}

        /**
         * add package by parameters
         * 
         * @param array $parameters, name value pairs to add by
         */
		public function add_package($parameters) {
			$parameters['shipment_id'] = $this->id;
			$package = new \Shipping\Package();
			if ($package->add($parameters)) {
				return $package;
			} else {
				$this->_error = "Error adding package: ".$package->error();
				return null;
			}
		}
		
        /**
         * add item to shipment by parameters
         * 
         * @param array $parameters, name value pairs to add by
         */
		public function add_item($parameters) {
			if (! $this->id) {
				$this->_error = "Shipping id not set";
				return null;
			}
			$parameters['shipment_id'] = $this->id;
			$item = new \Shipping\Item();
			if ($item->add($parameters)) {
				return $item;
			} else {
				$this->_error = "Error adding item: ".$item->error();
				return null;
			}
		}
		
		/**
		 * for current shipment, get the items included
		 */
		public function get_items() {
			if (! $this->id) return array();
			$itemList = new ItemList();
			$items = $itemList->find(array('shipment_id' => $this->id));
			if ($itemList->error()) {
				$this->_error = "Error getting items: ".$itemList->error();
				return null;
			}
			return $items;
		}
		
		public function vendor() {
			return new \Shipping\Vendor($this->vendor_id);
		}

		public function send_contact() {
			return new \Register\Customer($this->send_contact_id);
		}

		public function send_location() {
			return new \Register\Location($this->send_location_id);
		}

		public function rec_contact() {
			return new \Register\Customer($this->rec_contact_id);
		}

		public function rec_location() {
			return new \Register\Location($this->rec_location_id);
		}
	}
