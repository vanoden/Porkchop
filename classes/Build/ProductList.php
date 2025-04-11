<?php
	namespace Build;

	class ProductList {
		private $_error;
		private $_count = 0;

		public function find ($parameters = null) {
			$find_objects_query = "
				SELECT	id
				FROM	build_products
			";

			$rs = $GLOBALS['_database']->Execute($find_objects_query);
			if (! $rs) {
				$this->_error = "SQL Error in Build::ProductList::find(): ".$GLOBALS['_database']->ErrorMsg();
				return null;
			}

			$products = array();
			while(list($id) = $rs->FetchRow()) {
				$product = new Product($id);
				array_push($products,$product);
				$this->_count ++;
			}
			return $products;
		}
	}