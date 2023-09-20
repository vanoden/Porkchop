<?php
	namespace Site;

	class TermsOfUseEventList Extends \BaseListClass {
	
		public function __construct() {
			$this->_modelName = '\Site\TermsOfUseEvent';

			$this->_tableDefaultSortBy = 'date_event';
			$this->_tableDefaultSortOrder = 'desc';
		}
	}
