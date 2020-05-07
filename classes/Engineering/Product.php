<?php
	namespace Engineering;

	class Product {
		private $_error;
		public $id;
		public $code;
		public $title;
		public $description;

		public function __construct($id = 0) {
			if (is_numeric($id) and $id > 0) {
				$this->id = $id;
				$this->details();
			}
		}

		public function add($parameters = array()) {
			if (isset($parameters['code']) && strlen($parameters['code'])) {
				if (preg_match('/^[\w\-\.\_\s]+$/',$parameters['code'])) {
					$code = $parameters['code'];
				}
				else {
					$this->_error = "Invalid code";
					return null;
				}
			}
			else {
				$code = uniqid();
			}

			$check_dups = new Product();
			if ($check_dups->get($code)) {
				$this->_error = "Duplicate code";
				return null;
			}

			$add_object_query = "
				INSERT
				INTO	engineering_products
				(		code,title)
				VALUES
				(		?,?)
			";

			$GLOBALS['_database']->Execute(
				$add_object_query,
				array($code,$_REQUEST['title'])
			);

			if ($GLOBALS['_database']->ErrorMsg()) {
				$this->_error = "SQL Error in Engineering::Product::add(): ".$GLOBALS['_database']->ErrorMsg();
				return null;
			}

			$this->id = $GLOBALS['_database']->Insert_ID();
			return $this->update($parameters);
		}

		public function update($parameters = array()) {
			// Bust Cache
			$cache_key = "engineering.product[".$this->id."]";
			$cache_item = new \Cache\Item($GLOBALS['_CACHE_'],$cache_key);
			$cache_item->delete();

			$update_object_query = "
				UPDATE	engineering_products
				SET		id = id
			";

			if (isset($parameters['title']))
				$update_object_query .= ",
						title = ".$GLOBALS['_database']->qstr($parameters['title'],get_magic_quotes_gpc());

			if (isset($parameters['description']))
				$update_object_query .= ",
						description = ".$GLOBALS['_database']->qstr($parameters['description'],get_magic_quotes_gpc());

			$update_object_query .= "
				WHERE	id = ?
			";

			$GLOBALS['_database']->Execute(
				$update_object_query,
				array($this->id)
			);

			if ($GLOBALS['_database']->ErrorMsg()) {
				$this->_error = "SQL Error in Engineering::Products::update(): ".$GLOBALS['_database']->ErrorMsg();
				return null;
			}

			return $this->details();
		}

		public function get($code) {
			$get_object_query = "
				SELECT	id
				FROM	engineering_products
				WHERE	code = ?
			";

			$rs = $GLOBALS['_database']->Execute(
				$get_object_query,
				array($code)
			);

			if (! $rs) {
				$this->_error = "SQL Error in Engineering::Product::get(): ".$GLOBALS['_database']->ErrorMsg();
				return false;
			}

			list($id) = $rs->FetchRow();
			$this->id = $id;
			return $this->details();
		}

		public function details() {
			$cache_key = "engineering.product[".$this->id."]";
			$cache = new \Cache\Item($GLOBALS['_CACHE_'],$cache_key);
			if ($cache->error) {
				app_log("Error in cache mechanism: ".$cache->error,'error',__FILE__,__LINE__);
			}

			# Cached Object, Yay!
			if ($object = $cache->get()) {
				app_log($cache_key." found in cache",'trace');
				$this->_cached = true;
			}
			else {
				$get_object_query = "
					SELECT	*
					FROM	engineering_products
					WHERE	id = ?
				";
	
				$rs = $GLOBALS['_database']->Execute(
					$get_object_query,
					array($this->id)
				);
	
				if (! $rs) {
					$this->_error = "SQL Error in Engineering::Product::details(): ".$GLOBALS['_database']->ErrorMsg();
					return null;
				};
	
				$object = $rs->FetchNextObject(false);
				$this->id = $object->id;
				$this->_cached = false;
			}

			$this->title = $object->title;
			$this->code = $object->code;
			$this->description = $object->description;

			if (! $this->_cached) {
				// Cache Object
				app_log("Setting cache key ".$cache_key,'debug',__FILE__,__LINE__);
				if ($object->id) $result = $cache->set($object);
				app_log("Cache result: ".$result,'trace',__FILE__,__LINE__);	
			}

			return true;
		}

		public function error() {
			return $this->_error;
		}
	}
