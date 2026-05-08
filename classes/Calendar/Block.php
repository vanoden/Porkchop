<?php
	namespace Calendar;

	/** @class Block
	 * Base Class representing a block of time: Hour, Day, Week, Month, Year
	 */
	abstract class Block Extends \BaseClass {
		protected int $_timestamp_start;
		protected int $_length;				// Length of block in seconds
		protected int $_interval; 			// Number of seconds in a part of block (hours for a day, days for a week, days for a month, etc)

		/** @method public startTime
		 * Get/Set the start of the period based on some
		 * provided time within the period, ie now()
		 * @param int seed - Optional timestamp within period
		 * @return int startTime - first second of the period
		 */
		public function startTime($seed = 0) {
		}

		/** @method public length()
		 * Get/Set the length of the block in seconds
		 * @param int length - length of block in seconds
		 * @return int length - length of block in seconds
		 */
		public function length($length = null) {
			if ($length !== null) {
				$this->_length = $length;
			}
			return $this->_length;
		}
	}
