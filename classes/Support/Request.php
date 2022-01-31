<?php
	namespace Support;

	class Request {
	
		private $_error;
		public $id;
		public $customer;
		public $tech_id;
		public $status;
		public $date_created;
		public $validStatus = array('NEW','CANCELLED','OPEN','CLOSED');

		public function __construct($id = 0) {
			if ($id > 0) {
				$this->id = $id;
				$this->details();
			}
		}

		public function nextLine() {
			$get_line_query = "
				SELECT	max(line)
				FROM	support_request_items
				WHERE	request_id = ?
			";
			
            $rs = executeSQLByParams($get_line_query, array($this->id));
			if (! $rs) {
				$this->_error = "SQL Error in Support::Request::nextLine(): ".$GLOBALS['_database']->ErrorMsg();
				return false;
			}

			list($line) = $rs->FetchRow();
			return $line + 1;
		}
		
		public function get($code) {
			$get_object_query = "
				SELECT	id
				FROM	support_requests
				WHERE	code = ?
			";
			
			$rs = executeSQLByParams($get_object_query, array($code));
			if (! $rs) {
				$this->_error = "SQL Error in Support::Request::get(): ".$GLOBALS['_database']->ErrorMsg();
				return null;
			}
			
			list($id) = $rs->FetchRow();
			$this->id = $id;
			return $this->details();
		}
		
		public function add($parameters) {
		
			if (! $parameters['code']) $parameters['code'] = $this->nextCode();

			$customer = new \Register\Customer($parameters['customer_id']);
			if (! $customer->id) {
				$this->_error = "Customer required";
				return false;
			}
			
			if (! $parameters['status']) $parameters['status'] = 'NEW';
			$parameters['status'] = strtoupper($parameters['status']);
			
			if (! $this->valid_status($parameters['status'])) {
				$this->_error = "Invalid status";
				return false;
			}

			$add_object_query = "
				INSERT
				INTO	support_requests
				(		code,
						customer_id,
						organization_id,
						date_request,
						status,
						type
				)
				VALUES
				(		?,?,?,sysdate(),?,?)
			";

			$bind_params = array(
				$parameters['code'],
				$customer->id,
				$customer->organization->id,
				$parameters['status'],
				$parameters['type']
			);			
            $rs = executeSQLByParams($add_object_query,$bind_params);
			
			if ($GLOBALS['_database']->ErrorMsg()) {
				$this->_error = "SQL Error in Support::Request::add(): ".$GLOBALS['_database']->ErrorMsg();
				query_log($add_object_query);
				app_log(print_r($bind_params,true),'info');
				return null;
			}

			$this->id = $GLOBALS['_database']->Insert_ID();
			return $this->update($parameters);
		}

		public function update($parameters = array()) {
			// Bust Cache
			$cache_key = "support.request[".$this->id."]";
			$cache_item = new \Cache\Item($GLOBALS['_CACHE_'],$cache_key);
			$cache_item->delete();

			$update_object_query = "
				UPDATE	support_requests
				SET		id = id";

			if ($parameters['status']) {
				if ($this->valid_status($parameters['status'])) {
					$update_object_query .= ",
					status	= ".$GLOBALS['_database']->qstr($parameters['status'],get_magic_quotes_gpc());
				} else {
					$this->_error = "Invalid status";
					return false;
				}
			}

			$update_object_query .= "
				WHERE	id = ?";			
            $rs = executeSQLByParams($update_object_query, array($this->id));
			
			if ($GLOBALS['_database']->ErrorMsg()) {
				$this->_error = "SQL Error in Support::Request::update(): ".$GLOBALS['_database']->ErrorMsg();
				return null;
			}
			
			return $this->details();
		}

		private function details() {
			$cache_key = "support.request[".$this->id."]";
			$cache = new \Cache\Item($GLOBALS['_CACHE_'],$cache_key);
			if ($cache->error) app_log("Error in cache mechanism: ".$cache->error,'error',__FILE__,__LINE__);

			# Cached Object, Yay!
			if ($object = $cache->get()) {
				app_log($cache_key." found in cache",'trace');
				$this->_cached = true;
			}
			else {
				// Get Request Details
				$get_request_query = "
					SELECT	id,
							code,
							status,
							customer_id,
							date_request,
							type
					FROM	support_requests
					WHERE	id = ?
				";
        	    $rs = executeSQLByParams($get_request_query, array($this->id));
	
				if (! $rs) {
					$this->_error = "SQL Error in SupportRequest::details: ".$GLOBALS['_database']->ErrorMsg();
					return null;
				}
	
				$object = $rs->FetchNextObject(false);
				$this->id = $object->id;
				$this->_cache = false;
			}
			$this->code = $object->code;
			$this->status = $object->status;
			$this->customer = new \Register\Customer($object->customer_id);
			$this->date_request = $object->date_request;
			$this->type = $object->type;

			if (! $this->_cached) {
				// Cache Object
				app_log("Setting cache key ".$cache_key,'debug',__FILE__,__LINE__);
				if ($object->id) $result = $cache->set($object);
				app_log("Cache result: ".$result,'trace',__FILE__,__LINE__);	
			}

			return true;
		}

		public function valid_status($status) {
			$statuses = array(
					"NEW",
					"CANCELLED",
					"ASSIGNED",
					"OPEN",
					"PENDING CUSTOMER",
					"PENDING VENDOR",
					"COMPLETE",
					"CLOSED"
			);
			if (in_array($status,$statuses)) return true;
			return false;
		}

		public function error() {
			return $this->_error;
		}

		public function addItem($parameters) {
			// Add Ticket (item) To Request
			$parameters['request_id'] = $this->id;
			$item = new \Support\Request\Item();
			$item->add($parameters);

			if ($item->error()) {
				$this->_error = "Error adding item: ".$item->error();
				return false;
			}

			return $item;
		}

		public function notifyOwner($message) {
			if ($this->owner()) {
				$this->owner()->notify($message);
			}
		}
		public function owner() {
			return new \Register\Customer($this->customer_id);
		}
		public function openItems() {
			app_log("Counting open items");
			$itemlist = new \Support\Request\ItemList();
			$items = $itemlist->find(array('request_id' => $this->id));
			$count = 0;
			foreach ($items as $item) {
				app_log("Item ".$item->line." is ".$item->status);
				if (! in_array($item->status,array('COMPLETE','CLOSED','CANCELLED'))) $count ++;
			}
			return $count;
		}
		
		public function items() {
			$itemlist = new \Support\Request\ItemList();
			$items = $itemlist->find(array('request_id' => $this->id));
			return $items;
		}

		public function nextCode() {
			$prefix = "SI-";
			$get_max_query = "
				SELECT	max(code)
				FROM	support_requests
				WHERE	code LIKE 'SI-_____'
			";			
            $rs = executeSQLByParams($get_max_query, array());
			
			if (! $rs) {
				$this->_error = "SQL Error in Support::Request::nextCode(): ".$GLOBALS['_database']->ErrorMsg();
				return false;
			}
			list($code) = $rs->FetchRow();
			if (preg_match('/^SI\-(\d+)$/',$code,$matches)) {
				$id = $matches[1] + 1;
				$code = sprintf("SI-%05d",$id);
				return $code;
			}
			else {
				return 'SI-00001';
			}
		}
	}
