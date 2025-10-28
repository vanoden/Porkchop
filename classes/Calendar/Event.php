<?php
	namespace Calendar;

	class Event Extends \BaseModel {
		protected int $_timestamp_start = 0;
		protected int $_timestamp_end = 0;
		protected int $_timestamp_created = 0;
		protected int $_calendar_id = 0;
		protected string $_name = "";
		protected string $_description = "";
		protested string $_location_id = "";
		protected int $_user_created = 0;

		public function __construct($id = null) {
			$this->_tableName = 'calendar_events';
			$this->_metaTableName = 'calendar_event_metadata';
			$this->_tableMetaFKColumn = 'event_id';
			$this->_addTypes();
            parent::__construct($id);
		}
	}
