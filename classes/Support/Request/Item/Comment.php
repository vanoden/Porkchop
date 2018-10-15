<?
	namespace Support\Request\Item;

	class Comment {
		private $_error;
		public $author;
		public $date_comment;
		public $description;

		public function __construct($id = 0) {
			if (is_numeric($id) && $id > 0) {
				$this->id = $id;
				return $this->details();
			}
		}

		public function add($parameters) {
			if (isset($parameters['item_id'])) {
				$item = new \Support\Request\Item($parameters['item_id']);
				if ($item->error()) {
					$this->_error = $item->error();
					return false;
				}
				if (! $item->id) {
					$this->_error = "Request Item not found";
					return false;
				}
			}
			else {
				$this->_error = "Item id required";
				return false;
			}

			if (isset($parameters['author_id'])) {
				$author = new \Register\Customer($parameters['author_id']);
				if ($author->error) {
					$this->_error = $author->error;
					return false;
				}
				if (! $author->id) {
					$this->_error = "Author not found";
					return false;
				}
			}
			else {
				$this->_error = "Author id required";
				return false;
			}

			$add_object_query = "
				INSERT
				INTO	support_item_comments
				(		item_id,author_id,date_comment,content)
				VALUES
				(		?,?,sysdate(),?)
			";
			$GLOBALS['_database']->Execute(
				$add_object_query,
				array(
					$item->id,
					$author->id,
					$parameters['content']
				)
			);
			if ($GLOBALS['_database']->ErrorMsg()) {
				$this->_error = "SQL Error in Support::Request::Comment::add(): ".$GLOBALS['_database']->ErrorMsg();
				return false;
			}
			list($this->id) = $GLOBALS['_database']->Insert_ID();
			return $this->update($parameters);
		}

		public function update($parameters) {
			return $this->details();
		}

		public function details() {
			$get_object_query = "
				SELECT	*
				FROM	support_item_comments
				WHERE	id = ?
			";
			$rs = $GLOBALS['_database']->Execute(
				$get_object_query,
				array($this->id)
			);
			if (! $rs) {
				$this->_error = "SQL Error in Support::Request::Comment::details(): ".$GLOBALS['_database']->ErrorMsg();
				return false;
			}
			$object = $rs->FetchNextObject(false);
			$this->id = $object->id;
			$this->author = new \Register\Customer($object->author_id);
			$this->date_comment = $object->date_comment;
			$this->content = $object->content;
			return true;
		}

		public function error() {
			return $this->_error;
		}
	}
?>
