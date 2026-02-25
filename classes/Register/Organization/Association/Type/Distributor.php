<?php
	/** @class Register\Organization\Association\Type\Distributor
	 * Represents a distributor association type between organizations.
	 */
	namespace Register\Organization\Association\Type;

	class Distributor Extends \Register\Organization\Association\Type {
		public function __construct($id = null) {
			parent::__construct($id);
			$this->name = "DISTRIBUTOR";
			$this->description = "Association type for distributors between organizations.";
		}
	}