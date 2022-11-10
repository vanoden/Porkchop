<?php
	namespace Content;
	
	class MessageList Extends \BaseListClass {

		public function find($parameters = array()) {
            $this->clearError();
            $this->resetCount();

			$get_contents_query = "
				SELECT	id
				FROM	content_messages
				WHERE	id = id";

			if (isset($parameters['target']) && strlen($parameters['target']))
				$get_contents_query .= "
				AND		target = ".$GLOBALS['_database']->qstr($parameters['target'],get_magic_quotes_gpc());

			$rs = $GLOBALS['_database']->Execute($get_contents_query);
			if (! $rs) {
				$this->SQLError($GLOBALS['_database']->ErrorMsg());
				return null;
			}

			$messages = array();
			while (list($id) = $rs->FetchRow()) {
				$message = new \Content\Message($id);
                $this->incrementCount();
				array_push($messages,$message);
			}
			return $messages;
		}
		
		public function search($parameters = array()) {
            $this->clearError();
            $this->resetCount();

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
				$this->SQLError($GLOBALS['_database']->ErrorMsg());
				return 0;
			}

			$messages = array();
			while (list($id) = $rs->FetchRow()) {
				$message = new \Content\Message($id);
				if (!isset($parameters['is_user_search']) || empty($parameters['is_user_search'])) {
    				unset($message->content);
				} else {
    				$message->content = substr(strip_tags($message->content), 0, 150) . '...';
				}
                $this->incrementCount();
				array_push($messages,$message);
			}
			return $messages;
		}
	}
