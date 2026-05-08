<?php
	/** @class Storage\Repository\Validation
	 * A virtual class for validating repository parameters before creating or updating a repository
	 * This class is not meant to be instantiated, but provides a common interface for validation across repository types
	 */
	namespace Storage\Repository;

	class Validation Extends \Storage\Repository {
		public function connect() {
			// This virtual class cannot test a connection
			$this->error("Cannot connect to virtual repository");
			return false;
		}

		public function addFile($file, $path): bool {
			// This virtual class cannot add a file
			$this->error("Cannot add file to virtual repository");
			return false;
		}
	}