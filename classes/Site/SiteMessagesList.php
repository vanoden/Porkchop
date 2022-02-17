<?php
	namespace Site;

	class SiteMessagesList {
	
		private $_count = 0;
		private $_error;

		public function find($parameters = array()) {
	
			app_log("Site::SiteMessagesList::find()",'trace',__FILE__,__LINE__);
			
			$this->error = null;
			$get_site_messages_query = "
				SELECT	id
				FROM	site_messages
				WHERE	id = id
			";			

			$bind_params = array();
			if (isset($parameters['user_created'])) {
				$get_site_messages_query .= "
				AND user_created = ?";
				array_push($bind_params,$parameters['user_created']);
			}

			query_log($get_site_messages_query,$bind_params);
			$rs = $GLOBALS['_database']->Execute($get_site_messages_query,$bind_params);
			if (! $rs) {
				$this->error = "SQL Error in Site::SiteMessagesList::find: ".$GLOBALS['_database']->ErrorMsg();
				return null;
			}
			
			$siteMessages = array();
			while (list($id) = $rs->FetchRow()) {
			    $siteMessage = new \Site\SiteMessage();
			    $siteMessage->details();
			    $this->count ++;
			    array_push($siteMessages,$siteMessage);
			}
			
			return $siteMessages;
		}
        
        public function error() {
            return $this->_error;
        }

        public function count() {
            return $this->_count;
        }
	}
