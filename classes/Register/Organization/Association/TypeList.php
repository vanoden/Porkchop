<?php
	/** @class Register\Organization\Association\TypeList
	 * Handles the list of association types between organizations.
	 */
	namespace Register\Organization\Association;

	class TypeList Extends \BaseClassFileList {
		protected $directoryPath = 'Register\\Organization\\Association\\Type';
		protected $namespacePrefix = 'Register\\Organization\\Association\\Type';

		/** @method public getNames()
		 * Returns all association types.
		 * @return array An array of all association type names
		 */
		public function getNames() {
			$types = $this->getObjects();
			$names = array();
			foreach ($types as $type) {
				array_push($names,$type->name);
			}
			return $names;
		}
	}