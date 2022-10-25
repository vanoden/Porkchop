<?php
	class BaseMetaClass Extends \BaseClass {
		public $key;
		public $value;

		public function validKey($string) {
			return preg_match('/^[\w\-\_\.\s]+$/',$string);
		}
		public function validValue($string) {
			if ($string == noXSS($string)) return true;
		}
	}