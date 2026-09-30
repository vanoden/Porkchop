<?php
	/** @class Geography\TimesRecord
	 * @brief A record of time data for a given location and time
	 */
	namespace Geography;

	class TimesRecord extends \BaseModel {
		public ?int $id;
		public ?int $zip_code_id;
		public $date_record;
		public $sunrise;				// Sunrise time
		public $sunset;					// Sunset time
		public $moonrise;				// Moonrise time
		public $moonset;				// Moonset time
		public $moon_phase;				// Moon phase description
		public $timezone;				// Timezone of the location
		public $timezone_offset;		// Timezone offset from UTC

		public function __construct($parameters = array()) {
			$this->_tableName = "geography_times";
			$this->_tableUKColumn = "id";
			$this->_cacheKeyPrefix = "geography.times_record";
			parent::__construct($parameters);
		}
	}