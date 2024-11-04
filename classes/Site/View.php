<?php
	namespace Site;

	class View Extends \BaseClass {
		public function validIndex($string): bool {
			return $this->validCode($string);
		}
	}