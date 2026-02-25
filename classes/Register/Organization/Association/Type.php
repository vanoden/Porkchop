<?php
	/** @class Register\Organization\Association\Type
	 * Base Class for Register\Organization\Association\Type
	 * Handles association types between organizations.
	 * This class provides methods to validate and manage association types.
	 */
	namespace Register\Organization\Association;

	class Type Extends \BaseModel {
		public ?int $id = null;						// Auto-incrementing ID for the association type
		public ?string $name;						// Name of the association type (e.g., "VENDOR","CUSTOMER","PARTNER")
		public ?string $description;				// Description of the association type
	}
