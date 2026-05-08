<?php
	/** @class Register\Organization\Association\Type\Partner
	 * Handles partner association types between organizations.
	 */
	namespace Register\Organization\Association\Type;

	class Partner Extends \Register\Organization\Association\Type {
		public function __construct($id = null) {
			parent::__construct($id);
			$this->name = "PARTNER";
			$this->description = "Association type for partners between organizations.";
		}
	}