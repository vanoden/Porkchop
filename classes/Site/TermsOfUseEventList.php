<?php
	namespace Site;

	class TermsOfUseEventList Extends \BaseListClass {
		public function __construct() {
			$this->_tableName = 'site_terms_of_use_events';
		}

		public function find($parameters = array()) {
			$this->clearError();
			$this->resetCount();

			$database = new \Database\Service();

			$find_events_query = "
				SELECT	`$this->_tableIDColumn`
				FROM	`$this->_tableName`
				WHERE	`$this->_tableIDColumn` = `$this->_tableIDColumn`
			";

			$find_events_query .= "
				ORDER BY `date_event` DESC
			";

			$events = array();

			$rs = $database->Execute($find_events_query);
			if (! $rs) {
				$this->SQLError($database->ErrorMsg());
				$events;
			}

			while (list($id) = $rs->FetchRow()) {
				$object = new TermsOfUseEvent($id);
				array_push($events,$object);
				$this->incrementCount();
			}
			return $events;
		}
	}