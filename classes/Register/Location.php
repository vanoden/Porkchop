<?php
	namespace Register;
	
	class Location {
		public $id;
		public $address_1;
		public $address_2;
		public $city;
		public $region;
		public $country;
		public $zip_code;
		public $name;

		public function __construct($id = null) {
			if (is_numeric($id) {
				$this->id = $id;
				$this->details();
			}
		}
		
		public function details() {
			$get_object_query = "
				SELECT	*
				FROM	register_locations
				WHERE	id = ?
			";
		}
	}
?>
