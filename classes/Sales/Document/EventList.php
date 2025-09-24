<?php
	namespace Sales\Document;

	class EventList Extends \BaseListClass {
		public function __construct() {
			$this->_modelName = "Sales\Order\Event";
		}
	}
?>
