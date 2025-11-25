<?php
	namespace S4Engine\Error;

	class Error Extends \BaseClass {
		protected ?int $_typeId = null;
		protected ?string $_typeName = null;
		protected ?string $_description = null;
	
		public function typeId(?int $typeId = null): ?int {
			if ($typeId !== null) {
				$this->_typeId = $typeId;
			}
			return $this->_typeId;
		}
		public function typeName(?string $typeName = null): ?string {
			if ($typeName !== null) {
				$this->_typeName = $typeName;
			}
			return $this->_typeName;
		}

		public function description(?string $description = null): ?string {
			if ($description !== null) {
				$this->_description = $description;
			}
			return $this->_description;
		}
	}