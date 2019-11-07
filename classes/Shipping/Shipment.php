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
        public $fields = array('id','code','document_number','date_entered','date_shipped','status','send_contact_id','send_location_id','rec_contact_id','rec_location_id','vendor_id');
        
        /**
         * add by params
         * 
         * @param array $parameters, name value pairs to add and populate new object by
         */
		public function add($parameters = array()) {
			if (! isset($parameters['code'])) $parameters['code'] = uniqid();
			if (! isset($parameters['status'])) $parameters['status'] = 'NEW';
			
			
			if (! isset($parameters['send_contact_id'])) {
				$this->_error = "Sending contact required";
				return false;
			}
			if (! isset($parameters['send_location_id'])) {
				$this->_error = "Sending location required";
				return false;
			}
			if (! isset($parameters['rec_contact_id'])) {
				$this->_error = "Receiving contact required";
				return false;
			}
			if (! isset($parameters['rec_location_id'])) {
				$this->_error = "Receiving location required";
				return false;
			}
			
		    parent::add($parameters);
		}
		
        /**
         * update by params
         * 
         * @param array $parameters, name value pairs to update object by
         */
		public function update($parameters = array()) {
			if (isset($parameters['type']) && isset($parameters['number'])) $parameters['document_number'] = sprintf("%s-%06d",$parameters['type'],$parameters['number']);
            parent::update($parameters);
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
			$parameters['shipment_id'] = $this->id;
			$item = new \Shipping\Item();
			if ($item->add($parameters)) {
				return $item;
			} else {
				$this->_error = "Error adding item: ".$item->error();
				return null;
			}
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
