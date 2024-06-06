<?php
	namespace Sales;

	class OrderList Extends \BaseListClass {

		public function __construct() {
			$this->_modelName = '\Sales\Order';
			$this->_tableDefaultSortBy = 'date_event';
		}

        public function count($parameters = []) {
            if (!empty($this->_count)) return $this->_count;
            $this->_count = count($this->find($parameters));
            return $this->_count;
        }

		public function find($parameters = [],$controls = []) {

			$bind_params = array();
			$find_order_query = "
				SELECT id
				FROM sales_orders
				WHERE id = id
			";
			
			if (!empty($parameters['id'])) {
				$find_order_query .= "
					AND id = ?";
				array_push($bind_params, $parameters['id']);
			}
			
			if (!empty($parameters['customer_id'])) {
				$find_order_query .= "
					AND customer_id = ?";
				array_push($bind_params, $parameters['customer_id']);
			}
			
			if (isset($parameters['status'])) {
				if (is_array($parameters['status'])) {
					if (count($parameters['status']) > 0) {
						$statii = "";
						foreach ($parameters['status'] as $status) {
							if (preg_match('/^\w+$/',$status)) {
								if (strlen($statii) > 0) $statii .= ",";
								$statii .= "'$status'";
							}
						}
						$find_order_query .= "
							AND status in (".$statii.")";
					}
					else {
						$find_order_query .= "
							AND id != id";
					}
				}
				elseif (!empty($parameters['status'])) {
					$find_order_query .= "
						AND status = ?";
					array_push($bind_params, $parameters['status']);
				}
			}
			
			// apply the order and sort direction
			if (!empty($parameters['order_by']) && !empty($parameters['sort_direction'])) {
				$order_by_clause = " ORDER BY ";
				$sort_direction_clause = " `" . $parameters['sort_direction'] . "` " . strtoupper($parameters['order_by']);
				$find_order_query .= $order_by_clause . $sort_direction_clause;
			}

            if (isset($controls['limit']) && is_numeric($controls['limit'])) {
                $find_order_query .= "
                LIMIT   ".$controls['limit'];
                if (isset($controls['offset']) && is_numeric($controls['offset'])) {
                    $find_order_query .= "
                    OFFSET  ".$controls['offset'];
                }
            }

			query_log($find_order_query,$bind_params,true);
			$rs = $GLOBALS['_database']->Execute($find_order_query,$bind_params);
			if (! $rs) {
				$this->error("SQL Error in Sales::OrderList(): ".$GLOBALS['_database']->ErrorMsg());
				return null;
			}
			$orders = array();
			while (list($id) = $rs->FetchRow()) {
				app_log("Adding order $id");
				$order = new \Sales\Order($id);
				array_push($orders,$order);
				$this->_count ++;
			}

			return $orders;
		}

	}
