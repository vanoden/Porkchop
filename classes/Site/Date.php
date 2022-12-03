<?php
	namespace Site;

	class Date Extends \BaseClass {
		public $timezone;
		
		public function __construct() {
			$this->timezone = $GLOBALS['_SESSION_']->timezone;
		}

		public function Local($time) {
			$timezone = new \DateTimeZone($this->timezone);
			$datetime = new \DateTime('now',$timezone);
			$datetime->setTimeStamp($time);
			return $datetime->format("M j G:i");
		}
	}