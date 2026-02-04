<?php

	namespace Calendar\Event;

	class User Extends \Register\Customer {
		public bool $optional = false;

		public function __construct($id = null) {
			$this->_tableName = 'register_customers';
			parent::__construct($id);
		}
	}