<?php

	namespace Calendar\Event;

	class Location Extends \Register\Location {

		public function __construct($id = null) {
			$this->_tableName = 'register_locations';
			parent::__construct($id);
		}
	}