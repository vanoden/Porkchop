<?php
	/** @class Register\Organization\Association\Type\Customer
	 * Handles customer association types between organizations.
	 */
	namespace Register\Organization\Association\Type;

	class Customer Extends \Register\Organization\Association\Type {
		public function __construct($id = null) {
			parent::__construct($id);
			$this->name = "CUSTOMER";
			$this->description = "Association type for customers between organizations.";
		}
	}
