<?php
	namespace Database;

	class Schema Extends \BaseClass {
		private $name;

		public function table($name) {
			return new \Database\Schema\Table($name);
		}

		public function tables() {
			$database = new \Database\Service();
			
			$query = "
				SHOW TABLES";
			$rs = $database->Execute($query);
			$tables = [];
			while ($row = $rs->FetchRow()) {
				$tables[] = $row[0];
			}
			return $tables;
		}
	}