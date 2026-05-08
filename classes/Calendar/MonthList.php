<?php

	namespace Calendar;

	class MonthList Extends BlockList {
		public function __construct($date = null) {
			parent::__construct($date);
		}

		public function findAdvanced(array $parameters, array $advanced, array $controls): array
		{
			$results = [];

			if (!empty($parameters['start_year']) && !empty($parameters['end_year'])) {
				if (!empty($parameters['start_month']) && !empty($parameters['end_month'])) {
					$startYear = (int)$parameters['start_year'];
					$endYear = (int)$parameters['end_year'];
					$startMonth = (int)$parameters['start_month'];
					$endMonth = (int)$parameters['end_month'];

					for ($year = $startYear; $year <= $endYear; $year++) {
						$monthStart = ($year === $startYear) ? $startMonth : 1;
						$monthEnd = ($year === $endYear) ? $endMonth : 12;

						for ($month = $monthStart; $month <= $monthEnd; $month++) {
							$monthDate = mktime(0, 0, 0, $month, 1, $year);
							$results[] = new Month($monthDate);
						}
					}
					return $results;
				}
				else {
					$startYear = (int)$parameters['start_year'];
					$endYear = (int)$parameters['end_year'];
					for ($year = $startYear; $year <= $endYear; $year++) {
						for ($month = 1; $month <= 12; $month++) {
							$monthDate = mktime(0, 0, 0, $month, 1, $year);
							$results[] = new Month($monthDate);
						}
					}
					return $results;
				}
			}
			elseif (!empty($parameters['year'])) {
				$year = (int)$parameters['year'];
				for ($month = 1; $month <= 12; $month++) {
					$monthDate = mktime(0, 0, 0, $month, 1, $year);
					$results[] = new Month($monthDate);
				}
				return $results;
			}

			return $results;
		}
	}