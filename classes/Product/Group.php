<?php
	namespace Product;

	class Group Extends Item {
		public function items() {
			app_log("items() method called");
			$find_items_query = "
				SELECT	DISTINCT(p.id)
				FROM	product_products p
				LEFT OUTER JOIN
						product_relations r
				ON		r.product_id = p.id
				WHERE	p.status != 'DELETED'
				AND		r.parent_id = ?";
			query_log($find_items_query,array($this->id),true);
			$rs = $GLOBALS['_database']->Execute($find_items_query,array($this->id));
			if (! $rs) {
				$this->_error = $GLOBALS['_database']->ErrorMsg();
				return null;
			}
			$items = array();
			while (list($id) = $rs->FetchRow()) {
				$item = new \Product\Item($id);
				array_push($items,$item);
			}
			return $items;
		}
		public function addItem($item) {
			if ($this->id == $item->id) {
				$this->_error = "Can't add item to self";
				return false;
			}
			$relationship = new \Product\Relationship();
			if ($relationship->add(array('parent_id' => $this->id,'child_id' => $item->id))) return true;
			$this->_error = $relationship->error();
			return false;
		}
	}