<?php
	namespace Sales\Order;

	class ItemList {
	
		private $_count = 0;
		private $_error;

		public function find($parameters = array()) {
			$bind_params = array();

			$find_item_query = "
				SELECT	id
				FROM	sales_order_items
				WHERE	order_id = order_id
			";

			if (!empty($parameters['order_id'])) {
				$find_item_query .= "
				AND		order_id = ?";
				array_push($bind_params,$parameters['order_id']);
			}
			if (!empty($parameters['status'])) {
				if (is_array($parameters['status'])) {
					$statii = "";
					foreach ($parameters['status'] as $status) {
						if (preg_match('/^\w+$/',$status)) {
							if (strlen($statii) > 0) $statii .= ",";
							$statii .= "'$status'";
						}
					}
					$find_item_query .= "
					AND	status in (".$statii.")";
				}
				else {
					$find_item_query .= "
					AND		status = ?";
					array_push($bind_params,$parameters['status']);
				}
			}

			$find_item_query .= "
				ORDER BY order_id, line_number
			";

			$rs = $GLOBALS['_database']->Execute($find_item_query,$bind_params);
			if (! $rs) {
				$this->error("SQL Error in Sales::Order::ItemList(): ".$GLOBALS['_database']->ErrorMsg());
				return null;
			}
			$items = array();
			while (list($id) = $rs->FetchRow()) {
				$item = new \Sales\Order\Item($id);
				array_push($items,$item);
				$this->_count ++;
			}
			return $items;
		}
		public function count() {
			return $this->_count;
		}
		public function error() {
			return $this->_error;
		}
	}
