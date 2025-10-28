<?php
	namespace Calendar;

	class Calendar Extends \BaseModel {
		protected string $_name = "";
		protected string $_description = "";
		protected int $_owner_id = 0;
		protected int $_timestamp_created = 0;

		public function __construct($id = null) {
			$this->_tableName = 'calendar_calendars';
			$this->_metaTableName = 'calendar_metadata';
			$this->_tableMetaFKColumn = 'calendar_id';
			$this->_addTypes();
            parent::__construct($id);
		}

		public function upcomingEvents() {
			$eventList = new \Calendar\EventList();

			$parameters = {
				"calendar_id" => $this->calendar_id,
				"timestamp_start" => now()
			};
			$controls = {
				'limit' => 20,
				'sort' => 'timestamp_start',
				'order' => 'asc'
			};
			return $eventList->find($parameters,,$controls);
		}
	}
