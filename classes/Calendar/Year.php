<?php

	namespace Calendar;

	class Year Extends Block {
		public function __construct($date = null) {
			if ($date === null) {
				$date = mktime(0,0,0,1,1,(int)date('Y'));
			}
			elseif (is_int($date)) {
				$date = mktime(0,0,0,1,1,(int)date('Y', $date));
			}
			elseif ($date instanceof \DateTime) {
				$date = mktime(0,0,0,1,1,(int)$date->format('Y'));
			}
			else {
				$date = mktime(0,0,0,1,1,(int)date('Y', strtotime($date)));
			}
			$this->_timestamp_start = $date;
			$this->_length = $this->seconds();
		}

		/** @method public month(int $monthInt): ?Month
		 * Get a Month object for the given month integer (1-12)
		 * @param int monthInt - Month integer (1-12)
		 * @return Month|null - Month object or null if invalid monthInt
		 */
		public function month($monthInt): ?Month {
			if ($monthInt < 1 || $monthInt > 12) {
				return null;
			}
			$monthDate = mktime(0,0,0,$monthInt,1,$this->number());
			return new Month($monthDate);
		}

		/** @method public months(): array
		 * Get an array of Month objects for all months in the year
		 * @return array - Array of Month objects
		 */
		public function months(): array {
			$monthList = new \Calendar\MonthList(mktime(0,0,0,1,1,$this->number()));
			return $monthList->find(array('year' => $this->number()));
		}

		/** @method public day(int $dayInt): ?Day
		 * Get a Day object for the given day integer (1-31)
		 * @param int dayInt - Day integer (1-31)
		 * @return Day|null - Day object or null if invalid dayInt
		 */
		public function day($dayInt): ?Day {
			if ($dayInt < 1 || $dayInt > 31) {
				return null;
			}
			$dayDate = mktime(0,0,0,1,$dayInt,$this->number());
			return new Day($dayDate);
		}

		/** @method public days(): array
		 * Get an array of Day objects for all days in the year
		 * @return array - Array of Day objects
		 */
		public function days(): array {
			$dayList = new \Calendar\DayList();
			return $dayList->find(array('year' => $this->number()));
		}

		/** @method public number(): int
		 * Get/Set the year integer for this Year object
		 * @param int year - Optional year integer to set
		 * @return int - Year integer
		 */
		public function number(?int $year = null): int {
			if ($year !== null) {
				$this->_timestamp_start = mktime(0, 0, 0, 1, 1, $year);
				$this->_length = $this->seconds();
			}
			return (int)date('Y', $this->_timestamp_start);
		}

		/** @method start()
		 * Get the starting DateTime of the year
		 * @return DateTime - Starting DateTime of the year
		 */
		public function start(): \DateTime {
			return new \DateTime("{$this->number()}-01-01 00:00:00", new \DateTimeZone('UTC'));
		}

		public function seconds(): int {
			// Create a DateTime object for the start of the specified year
			$startOfYear = new \DateTime("{$this->number()}-01-01 00:00:00", new \DateTimeZone('UTC'));

			// Create a DateTime object for the start of the *next* year
			$next = $this->number() + 1;
			$endOfYear = new \DateTime("{$next}-01-01 00:00:00", new \DateTimeZone('UTC'));

			// Calculate the difference between the two dates, resulting in a DateInterval object
			$interval = $startOfYear->diff($endOfYear);

			// The DateInterval's total days property (%a) gives the accurate number of days,
			// which then needs to be converted to seconds.
			$daysInYear = $interval->format('%a');

			// Convert total days to seconds (ignoring potential leap seconds, which are unpredictable)
			$seconds = $daysInYear * 24 * 60 * 60;

			return $seconds;
		}
	}