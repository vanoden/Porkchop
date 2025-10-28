<?php
	namespace Calendar;

	/** @class Block
	 * Base Class representing a block of time: Hour, Day, Week, Month, Year
	 */
	abstract class Block Extends \BaseClass {
		protected int $_timestamp_start;
		protected int $_timestamp_end;
		protected int $_interval; 			// Number of seconds in a part of block (hours for a day, days for a week, days for a month, etc)

		/** @method public startTime
		 * Get/Set the start of the period based on some
		 * provided time within the period, ie now()
		 * @param int seed - Optional timestamp within period
		 * @return int startTime - first second of the period
		 */
		abstract public function startTime($seed = 0) {
		}

		/** @method public endTime
		 * Get/Set the end of the period based on some
		 * provided time within the period, ie now()
		 * @param int seed - Optional timestamp within period
		 * @return int startTime - final second of the period
		 */
		abstract public function endTime($seed = 0) {
		}

		/** @method public length
		 * Number of seconds in interval
		abstract public function length() {
		}
	}
