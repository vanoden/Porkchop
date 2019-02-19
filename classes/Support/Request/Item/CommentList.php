<?php
	namespace Support\Request\Item;
	
	class CommentList {
		private $_error;
		public $count = 0;

		public function find($parameters = array()) {
			$find_objects_query = "
				SELECT	id
				FROM	support_item_comments
				WHERE	id = id
			";
			$bind_params = array();

			if (isset($parameters['item_id'])) {
				$item = new \Support\Request\Item($parameters['item_id']);
				if ($item->error()) {
					$this->_error = $item->error();
					return false;
				}
				if (! $item->id) {
					$this->_error = "Item not found";
					return false;
				}
				$find_objects_query .= "
				AND		item_id = ?
				";
				array_push($bind_params,$item->id);
			}
			query_log($find_objects_query);
			$rs = $GLOBALS['_database']->Execute($find_objects_query,$bind_params);
			app_log("Query executed");
			if (! $rs) {
				$this->_error = "SQL Error in Support::Request::Item::CommentList::find(): ".$GLOBALS['_database']->ErrorMsg();
				return false;
			}
			$objects = array();
			while(list($id) = $rs->FetchRow()) {
				$comment = new \Support\Request\Item\Comment($id);
				if ($comment->error()) {
					$this->_error = $comment->error();
					return false;
				}
				array_push($objects,$comment);
				$this->count ++;
			}
			return $objects;
		}

		public function error() {
			return $this->_error;
		}
	}
