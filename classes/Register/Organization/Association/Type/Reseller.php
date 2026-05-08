<?php
	/** @class Register\Organization\Association\Type\Reseller
	 * Represents a reseller association type between organizations.
	 */
	namespace Register\Organization\Association\Type;

	class Reseller Extends \Register\Organization\Association\Type {
		public function __construct($id = null) {
			parent::__construct($id);
			$this->name = "RESELLER";
			$this->description = "Association type for resellers between organizations.";
		}
	}