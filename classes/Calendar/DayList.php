<?php

	namespace Calendar;

	class DayList Extends BlockList {
		public function __construct() {
			parent::__construct();
		}

		public function findAdvanced(array $parameters, array $advanced, array $controls): array {
			$this->clearError();
			$this->resetCount();

			$results = [];

			if ($parameters['year'] && $parameters['month'] && $parameters['week']) {
				// Return Days for the specified week
				$year = (int)$parameters['year'];
				$month = (int)$parameters['month'];
				$week = (int)$parameters['week'];

				$firstDayOfMonth = mktime(0, 0, 0, $month, 1, $year);
				$firstDayOfWeek = strtotime("+".($week - 1)." weeks", $firstDayOfMonth);
				$startOfWeek = strtotime("last Sunday", $firstDayOfWeek);
				if (date('j', $startOfWeek) > 7) {
					$startOfWeek = strtotime("next Sunday", $startOfWeek);
				}

				for ($i = 0; $i < 7; $i++) {
					$dayDate = strtotime("+$i days", $startOfWeek);
					if (date('m', $dayDate) == $month) {
						$results[] = new Day($dayDate);
					}
				}
			}
			elseif ($parameters['year'] && $parameters['month']) {
				// Return Days for the specified month
				$year = (int)$parameters['year'];
				$month = (int)$parameters['month'];

				$daysInMonth = cal_days_in_month(CAL_GREGORIAN, $month, $year);

				for ($day = 1; $day <= $daysInMonth; $day++) {
					$dayDate = mktime(0, 0, 0, $month, $day, $year);
					$results[] = new Day($dayDate);
				}
			}
			elseif ($parameters['year']) {
				// Return Days for the specified year
				$year = (int)$parameters['year'];

				for ($month = 1; $month <= 12; $month++) {
					$daysInMonth = cal_days_in_month(CAL_GREGORIAN, $month, $year);

					for ($day = 1; $day <= $daysInMonth; $day++) {
						$dayDate = mktime(0, 0, 0, $month, $day, $year);
						$results[] = new Day($dayDate);
					}
				}
			}
			elseif ($parameters['start_date'] && $parameters['end_date']) {
				// Return Days between start_date and end_date
				$startDate = strtotime($parameters['start_date']);
				$endDate = strtotime($parameters['end_date']);

				for ($currentDate = $startDate; $currentDate <= $endDate; $currentDate = strtotime("+1 day", $currentDate)) {
					$results[] = new Day($currentDate);
				}
			}

			return $results;
		}
	}