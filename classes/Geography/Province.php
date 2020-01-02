<?php
	namespace Geography;

	class Province extends Admin {
		public function __construct($id = null) {
			app_log("Loading province $id",'notice');
			parent::__construct($id);
		}
	}