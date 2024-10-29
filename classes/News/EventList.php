<?php
    namespace News;

    class EventList Extends \BaseListClass {
		public function __construct() {
			$this->_modelName = '\News\Event';
		}

        public function findAdvanced($parameters, $advanced, $controls): array {
            return array();
        }
    }