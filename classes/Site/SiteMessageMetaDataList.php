<?php
	namespace Site;

	class SiteMessageMetaDataList {
	
		private $_count = 0;
		private $_error;

		public function find($parameters = array()) {
	
			app_log("Site::SiteMessageMetaDataList::find()",'trace',__FILE__,__LINE__);
			
			$this->error = null;
			$get_site_messages_metadata_query = "
				SELECT	`item_id`, `label`
				FROM	`site_messages_metadata`
				WHERE	`item_id` = `item_id`
			";			

			$bind_params = array();
			if (isset($parameters['label'])) {
				$get_site_messages_metadata_query .= "
				AND `label` = ?";
				array_push($bind_params,$parameters['label']);
			}

			if (isset($parameters['item_id'])) {
				$get_site_messages_metadata_query .= "
				AND `item_id` = ?";
				array_push($bind_params,$parameters['item_id']);
			}

			query_log($get_site_messages_metadata_query,$bind_params);
			$rs = $GLOBALS['_database']->Execute($get_site_messages_metadata_query,$bind_params);
			if (! $rs) {
				$this->error = "SQL Error in Site::SiteMessageMetaDataList::find: ".$GLOBALS['_database']->ErrorMsg();
				return null;
			}
			
			$siteMessages = array();
			while (list($id, $label) = $rs->FetchRow()) {
			    if (!isset($siteMessages[$label])) $siteMessages[$label] = array();
			    $siteMessage = new \Site\SiteMessageMetaData($id, $label);
			    $siteMessage->details();			    
			    $this->count ++;
			    array_push($siteMessages[$label],$siteMessage);
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
