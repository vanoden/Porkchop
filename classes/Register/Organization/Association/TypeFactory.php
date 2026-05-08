<?php
	/** @class Register\Organization\Association\TypeFactory
	 * Handles the creation of association type instances.
	 */
	namespace Register\Organization\Association;

	class TypeFactory {
		public static function create($type, $id = null) {
			$typeClass = "\\Register\\Organization\\Association\\Type\\" . ucfirst(strtolower($type));
			if (class_exists($typeClass)) {
				return new $typeClass($id);
			}
			return null;
		}
	}
