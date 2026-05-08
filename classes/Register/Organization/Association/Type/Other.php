<?php
	/** @class Register\Organization\Association\Type\Other
	 * Handles other association types between organizations.
	 */
	namespace Register\Organization\Association\Type;

	class Other Extends \Register\Organization\Association\Type {
		public function __construct($id = null) {
			parent::__construct($id);
			$this->name = "OTHER";
			$this->description = "Association type for other types between organizations.";
		}
	}
