<?php
	namespace Engineering;

	class ProductList {

		private $_error;

		public function find($parameters = array()) {

			$find_objects_query = "
				SELECT	id
				FROM	engineering_products
				WHERE	id = id
				ORDER BY title ASC
			";

            // if search term, then constrain by that
            if ($parameters['searchTerm']) {            
                $find_objects_query = "
                SELECT	`id`
                FROM	`engineering_products`
                WHERE	`code` LIKE '%".$parameters['searchTerm']."%' 
                        OR `title` LIKE '%".$parameters['searchTerm']."%' 
                        OR `description` LIKE '%".$parameters['searchTerm']."%'
                ORDER BY title ASC";
            }

			$rs = $GLOBALS['_database']->Execute(
				$find_objects_query
			);

			if (! $rs) {
				$this->_error = "SQL Error in Engineering::ProductList::find(): ".$GLOBALS['_database']->ErrorMsg();
				return null;
			}

			$products = array();
			while (list($id) = $rs->FetchRow()) {
				$product = new Product($id);
				array_push($products,$product);
			}

			return $products;
		}

		public function error() {
			return $this->_error;
		}
	}
?>
