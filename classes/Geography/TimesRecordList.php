<?php
	/** @class Geography\TimesRecordList
	 * @brief Object Model for a list of time records for a given location and time.
	 */
	namespace Geography;

	class TimesRecordList extends \BaseListClass {
		public function __construct() {
			$this->_tableName = "geography_times";
			$this->_modelName = "\\Geography\\TimesRecord";
		}

		public function findAdvanced(array $parameters = [], array $advanced = [], array $controls = []): array {
			return parent::findAdvanced($parameters, $advanced, $controls);
		}
	}