<?php
	namespace Email\MailQueue;

	class Record Extends \BaseClass {

		/** Minutes to wait after 1..6 failures. The attempt after the 1024 minute wait is the last. */
		const RETRY_DELAYS = array(1, 4, 16, 64, 256, 1024);

		/** A failed attempt at this count (previous failures already recorded) is marked FAILED. */
		const FINAL_FAILURE_COUNT = 6;

		public static function newCode() {
			return (new \Porkchop())->uuid();
		}

		public static function truthy($value) {
			if (is_bool($value)) return $value;
			if (is_int($value) || is_float($value)) return ((int)$value) === 1;
			return in_array(strtolower(trim((string)$value)), array('1', 'true', 'yes'), true);
		}

		public static function singleLine($value, $max = 255) {
			$text = str_replace(array("\r", "\n"), ' ', (string)$value);
			$text = trim(preg_replace('/\s+/', ' ', $text));
			if (strlen($text) > $max) $text = substr($text, 0, $max);
			return $text;
		}

		protected function database() {
			return new \Database\Service();
		}

		protected static function field($row, $name, $default = '') {
			if (!is_object($row) || !isset($row->$name)) return $default;
			return $row->$name;
		}

		protected function normalizeDate($value, $label) {
			if ($value === null || $value === '') return null;
			$normalized = get_mysql_date($value);
			if (!$normalized) {
				$this->error("Invalid $label");
				return false;
			}
			return $normalized;
		}
	}
