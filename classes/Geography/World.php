<?php
	namespace Geography;

	class World {
		protected $_error;

		public function countries() {
			$countryList = new CountryList();
			return $countryList->find();
		}
	}
