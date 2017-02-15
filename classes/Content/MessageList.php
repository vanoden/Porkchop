<?
	namespace Content;
	
	class MessageList {
		public function find($parameters = array()) {
			$this->error = NULL;
			$get_contents_query = "
				SELECT	id
				FROM	content_messages
				WHERE	id = id";

			if (isset($parameters['target']) && strlen($parameters['target']))
				$get_contents_query .= "
				AND		target = ".$GLOBALS['_database']->qstr($parameters['target'],get_magic_quotes_gpc());

			$rs = $GLOBALS['_database']->Execute($get_contents_query);
			if (! $rs) {
				$this->error = $GLOBALS['_database']->ErrorMsg();
				return 0;
			}

			$messages = array();
			while (list($id) = $rs->FetchRow()) {
				$message = new \Content\Message($id);
				array_push($messages,$message);
			}
			return $messages;
		}
	}
?>