<?php
	namespace Shipping;
	
class Shipment extends \BaseModel {

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
		public $instructions;

		public function __construct($id = 0) {	
			$this->_tableName = 'shipping_shipments';
			$this->_addStatus(array('OPEN','CLOSED'));
			$this->_addFields(array('id','code','document_number','date_entered','date_shipped','status','send_contact_id','send_location_id','rec_contact_id','rec_location_id','vendor_id', 'instructions'));
			parent::__construct($id);
		}

        /**
         * add by params
         * 
         * @param array $parameters, name value pairs to add and populate new object by
         */
		public function add($parameters = []) {
			$this->clearError();

			if (! isset($parameters['code'])) $parameters['code'] = uniqid();
			if (! isset($parameters['status'])) $parameters['status'] = 'NEW';
			if (! isset($parameters['date_entered'])) $parameters['date_entered'] = date('Y-m-d H:i:s');

			if (! $this->validStatus($parameters['status'])) {
				$this->error("Invalid Status '".$parameters['status']."'");
				return false;
			}
			if (isset($parameters['send_customer_id'])) {
				$parameters['send_contact_id'] = $parameters['send_customer_id'];
			}
			else {
				$this->error("Sending contact required");
				return false;
			}
			if (! isset($parameters['send_location_id'])) {
				$this->error("Sending location required");
				return false;
			}
			if (isset($parameters['receive_customer_id'])) {
				$parameters['rec_contact_id'] = $parameters['receive_customer_id'];
			}
			else {
				$this->error("Receiving contact required");
				return false;
			}
			if (isset($parameters['receive_location_id'])) {
				$parameters['rec_location_id'] = $parameters['receive_location_id'];
			}
			else {
				$this->error("Receiving location required");
				return false;
			}
			if (! isset($parameters['document_number'])) {
				$this->error("Document number required");
				return false;
			}
			
		    return parent::add($parameters);
		}
		
        /**
         * update by params
         * 
         * @param array $parameters, name value pairs to update object by
         */
		public function update($parameters = []): bool {
			if (isset($parameters['type']) && isset($parameters['number'])) $parameters['document_number'] = sprintf("%s-%06d",$parameters['type'],$parameters['number']);
            return parent::update($parameters);
		}

		/**
		 * Set ship-from address for this shipment (single-column update; values inlined to avoid driver prepare issues).
		 * @param int $location_id register_locations.id
		 * @return bool
		 */
		public function setSendLocationId($location_id) {
			$this->clearError();
			if (!$this->id) {
				$this->error('Shipment id required');
				return false;
			}
			$loc = (int)$location_id;
			if ($loc < 1) return false;
			$id = (int)$this->id;
			$sql = "UPDATE shipping_shipments SET send_location_id = $loc WHERE id = $id";
			$rs = $GLOBALS['_database']->Execute($sql);
			if (!$rs) {
				$this->SQLError($GLOBALS['_database']->ErrorMsg());
				return false;
			}
			$this->send_location_id = $loc;
			return true;
		}

        /**
         * add package by parameters
         * 
         * @param array $parameters, name value pairs to add by
         */
		public function addPackage($parameters) {
			$parameters['shipment_id'] = $this->id;
			$package = new \Shipping\Package();
			if ($package->add($parameters)) {
				return $package;
			}
			else {
				$this->error("Error adding package: ".$package->error());
				return null;
			}
		}
        public function add_package($parameters) {
            return $this->addPackage($parameters);
        }
		
        /**
         * add item to shipment by parameters
         * 
         * @param array $parameters, name value pairs to add by
         */
		public function add_item($parameters) {
			if (! $this->id) {
				$this->error("Shipping id not set");
				return null;
			}
			$parameters['shipment_id'] = $this->id;
			$item = new \Shipping\Item();
			if ($item->add($parameters)) {
				return $item;
			}
			else {
				$this->error("Error adding item: ".$item->error());
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
				$this->error("Error getting items: ".$itemList->error());
				return null;
			}
			return $items;
		}
		public function items() {
			return $this->get_items();
		}
		public function packages() {
			if (empty($this->id)) return array();
			$packageList = new \Shipping\PackageList();
			return $packageList->find(array('shipment_id' => $this->id));
		}

		public function package($id) {
			$package = new \Shipping\Package();
            if ($package->getByPackageNumber($this->id,$id)) {
                return $package;
            }
            else {
                $this->error("Package not found");
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

		public function number() {
			return sprintf("%06d",$this->id);
		}

		public function ship($params = array()) {
			foreach ($this->packages() as $package) {
				$package->ship();
			}
			return $this->update(array('status' => 'SHIPPED','vendor_id' => $params['vendor_id'],'date_shipped' => date('Y-m-d H:i:s')));
		}

        public function shipped() {
            if ($this->status == 'CLOSED' || !empty($this->date_shipped)) return true;
            return false;
        }

        public function ok_to_close() {
            if ($this->status == 'CLOSED') return false;
            $packages = $this->packages();
            foreach ($packages as $package) {
                if ($package->status != 'RECEIVED' && $package->status != 'INCOMPLETE' && $package->status != 'LOST' && $package->status != 'RETURNED') {
                    $this->error("Package ".$package->number." has not been received");
                    return false;
                }
            }
            return true;
        }
        public function close() {
            return $this->update(array('status' => 'CLOSED'));
        }

        /**
         * Parse the document number and get the associated RMA object if applicable
         * 
         * @return \Support\Request\Item\RMA|null RMA object or null if document number is not for an RMA
         */
        public function getRma() {
            if (empty($this->document_number)) return null;
            
            if (preg_match('/^RMA(\d+)$/', $this->document_number, $matches)) {
                $rma_id = $matches[1] * 1;
                $rma = new \Support\Request\Item\RMA($rma_id);
                return $rma->exists() ? $rma : null;
            }
            elseif (preg_match('/^TCKT(\d+)$/', $this->document_number, $matches)) {
                $ticket_id = $matches[1] * 1;
                $ticket = new \Support\Request\Item($ticket_id);
                $rmas = $ticket->rmas();
                return !empty($rmas) ? $rmas[0] : null;
            }
            
            return null;
        }
        
        /**
         * Parse the document number and get the associated ticket object if applicable
         * 
         * @return \Support\Request\Item|null Ticket object or null if document number is not for a ticket or RMA
         */
        public function getTicket() {
            if (empty($this->document_number)) return null;
            
            if (preg_match('/^RMA(\d+)$/', $this->document_number, $matches)) {
                $rma_id = $matches[1] * 1;
                $rma = new \Support\Request\Item\RMA($rma_id);
                return $rma->exists() ? $rma->item() : null;
            }
            elseif (preg_match('/^TCKT(\d+)$/', $this->document_number, $matches)) {
                $ticket_id = $matches[1] * 1;
                $ticket = new \Support\Request\Item($ticket_id);
                return $ticket->exists() ? $ticket : null;
            }
            
            return null;
        }
        
        /**
         * Parse the document number and get the associated object details
         * 
         * @return array|null Associative array with type, id, and link for the document or null if document number is empty
         */
        public function getDocumentObject() {
            if (empty($this->document_number)) return null;
            
            $result = [
                'type' => null,
                'id' => null,
                'link' => null
            ];
            
            if (preg_match('/^RMA(\d+)$/', $this->document_number, $matches)) {
                $result['type'] = 'rma';
                $result['id'] = $matches[1] * 1;
                $result['link'] = "/_support/admin_rma?id={$result['id']}";
            }
            elseif (preg_match('/^TCKT(\d+)$/', $this->document_number, $matches)) {
                $result['type'] = 'ticket';
                $result['id'] = $matches[1] * 1;
                $result['link'] = "/_support/request_item?id={$result['id']}";
            }
            elseif (preg_match('/^PO(\d+)$/', $this->document_number, $matches)) {
                $result['type'] = 'purchase_order';
                $result['id'] = $matches[1] * 1;
                $result['link'] = "/_sales/purchase_order?id={$result['id']}";
            }
            
            return $result;
        }
	}
