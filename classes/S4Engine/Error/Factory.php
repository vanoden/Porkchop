<?php
	namespace S4Engine\Error;

	class Factory Extends \BaseClass {
		/**  @method public function createError(int $typeId): ?\S4Engine\Error\Error
		 * Create an Error object based on the type ID
		 * @param int $typeId Type ID of the error
		 * @return ?\S4Engine\Error\Error Created Error object or null on failure
		 */
		public function createError(int $typeId): ?\S4Engine\Error\Error {
			$this->clearError();
			$error = null;
			switch ($typeId) {
				case 1:
					$error = new \S4Engine\Error\InvalidSession();
					break;
				case 2:
					$error = new \S4Engine\Error\InvalidFunction();
					break;
				case 3:
					$error = new \S4Engine\Error\InvalidClient();
					break;
				case 4:
					$error = new \S4Engine\Error\InvalidServer();
					break;
				case 5:
					$error = new \S4Engine\Error\UnparseableEnvelope();
					break;
				case 6:
					$error = new \S4Engine\Error\UnparseableContent();
					break;
				case 7:
					$error = new \S4Engine\Error\ServerError();
					break;
				case 8:
					$error = new \S4Engine\Error\ResourceNotFound();
					break;
				default:
					$error = new \S4Engine\Error\Unhandled();
					break;
			}
			return $error;
		}	
	}