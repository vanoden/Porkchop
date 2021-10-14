<?php
    namespace Contact;

    class EventList {
		public function find($parameters = array()) {
			$find_object_query = "
				SELECT	id
				FROM	contact_events
				WHERE	id = id
			";
			if (preg_match('/^\w+$/',$parameters['status']))
				$find_object_query = "
				AND		status = '".$parameters['status']."'";
			$find_object_query .= "
				ORDER BY date_event";
			$rs = $GLOBALS['_database']->Execute($find_object_query);
			if (! $rs) {
				$this->error = "SQL Error in Contact::Event::find(): ".$GLOBALS['_database']->ErrorMsg();
				return null;
			}
			$objects = array();
			while (list($id) = $rs->FetchRow()) {
                $object = new \Contact\Event($id);
				array_push($objects,$object);
			}
			return $objects;
		}
    }