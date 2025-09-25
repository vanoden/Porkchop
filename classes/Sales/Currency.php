<?php
	namespace Sales;

	/** @class Currency
	 * Models currency for sales
	 */
	class Currency Extends \BaseModel {

		public $name = 'Dollar';			// Name of Currency
		public $name_plural = 'Dollars';	// Plural Name of Currency
		public $symbol = '$';				// Symbol Representing Currency
		public $exchange_rate = "1";		// How much of this currency = 1 dollar

		/** @constructor */
		public function __construct($id = 0) {
			$this->_tableName = 'sales_currencies';
			$this->_cacheKeyPrefix = 'sales.currency';
			$this->_tableUKColumn = 'name';
			$this->_auditEvents = true;
			$this->_addField('name','symbol');
    		parent::__construct($id);
		}
	}
?>
