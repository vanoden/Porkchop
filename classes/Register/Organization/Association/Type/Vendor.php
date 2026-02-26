<?php
	/** @class Register\Organization\Association\Type\Vendor
	 * Handles vendor association types between organizations.
	 */
	namespace Register\Organization\Association\Type;

	class Vendor Extends \Register\Organization\Association\Type {
		public function __construct($id = null) {
			parent::__construct($id);
			$this->name = "VENDOR";
			$this->description = "Association type for vendors between organizations.";
		}
	}