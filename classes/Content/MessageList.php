<?php
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
				$this->error = "SQL Error in Content::MessageList::find(): ".$GLOBALS['_database']->ErrorMsg();
				return 0;
			}

			$messages = array();
			while (list($id) = $rs->FetchRow()) {
				$message = new \Content\Message($id);
				array_push($messages,$message);
			}
			return $messages;
		}
		
		public function search($parameters = array()) {
		
			$this->error = NULL;
			$get_contents_query = "
				SELECT	id
				FROM	content_messages
				WHERE	id = id";
				
			if (isset($parameters['string']) && strlen($parameters['string'])) {
    			$searchString = $GLOBALS['_database']->qstr($parameters['string'],get_magic_quotes_gpc());
    			$searchString = preg_replace("/'$/", "%'", $searchString);
                $searchString = preg_replace("/^'/", "'%", $searchString);

    			$get_contents_query .= " AND (`target` LIKE " . $searchString . " OR `title` LIKE " . $searchString . " OR `name` LIKE " . $searchString . " OR `content` LIKE " . $searchString . ")";
			} else {
			    $this->error = "Error: Search 'string' Parameter is Required.";
			    return 0;
			}
            
			$rs = $GLOBALS['_database']->Execute($get_contents_query);
			if (! $rs) {
				$this->error = "SQL Error in Content::MessageList::search(): ".$GLOBALS['_database']->ErrorMsg();
				return 0;
			}

			$messages = array();
			while (list($id) = $rs->FetchRow()) {
				$message = new \Content\Message($id);
				unset($message->content);	
				array_push($messages,$message);
			}
			return $messages;
		}
	}
