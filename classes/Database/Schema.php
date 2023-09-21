<?php
	namespace Database;

	class Schema Extends \BaseClass {
		private $name;

		public function table($name) {
			return new \Database\Schema\Table($name);
		}
	}