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
				$this->error = "SQL Error in Site::SiteMessagesList::find(): ".$GLOBALS['_database']->ErrorMsg();
				return null;
			}
			
			$siteMessages = array();
			while (list($id) = $rs->FetchRow()) {
			    $siteMessage = new \Site\SiteMessage($id);
			    $siteMessage->details();
			    $this->count ++;
			    array_push($siteMessages,$siteMessage);
			}
			
			return $siteMessages;
		}
		
		public function getUnreadForUserId ($userId) {
		
			app_log("Site::SiteMessagesList::getUnreadForUserId()",'trace',__FILE__,__LINE__);
			
			$this->error = null;
			$get_site_messages_query = "
				SELECT	count(sm.id) as total_messages
				FROM	site_messages sm
				WHERE	sm.id = sm.id
			";			

			$bind_params = array();
			if (isset($parameters['user_created'])) {
				$get_site_messages_query .= "
				AND user_created = ?";
				array_push($bind_params,$userId);
			}

			query_log($get_site_messages_query,$bind_params);
			$rs = $GLOBALS['_database']->Execute($get_site_messages_query,$bind_params);
			if (! $rs) {
				$this->error = "SQL Error in Site::SiteMessagesList::getUnreadForUserId(): ".$GLOBALS['_database']->ErrorMsg();
				return null;
			}
			
			$totalMessages = 0;
			while (list($row) = $rs->FetchRow()) {
			    $totalMessages = $row;
			};			
			$this->error = null;
			$get_site_messages_query = "
				SELECT	sm.id
				FROM	site_messages sm
				LEFT JOIN site_messages_metadata smm
				ON smm.item_id = sm.id
				WHERE	sm.id = sm.id
				AND smm.label = 'acknowledged'
			";			

			$bind_params = array();
			if (isset($parameters['user_created'])) {
				$get_site_messages_query .= "
				AND user_created = ?";
				array_push($bind_params,$userId);
			}

			query_log($get_site_messages_query,$bind_params);
			$rs = $GLOBALS['_database']->Execute($get_site_messages_query,$bind_params);
			if (! $rs) {
				$this->error = "SQL Error in Site::SiteMessagesList::getUnreadForUserId(): ".$GLOBALS['_database']->ErrorMsg();
				return null;
			}

			$acknowledgedMessages = 0;
			while (list($id) = $rs->FetchRow()) $acknowledgedMessages ++;			
			return $totalMessages - $acknowledgedMessages;		
		}
		
        
        public function error() {
            return $this->_error;
        }

        public function count() {
            return $this->_count;
        }
	}
