<?php
	/** @class Register\Partner\ExplicitShare
	 * Specific resource instance granted to a partner organization.
	 */
	namespace Register\Partner;

	class ExplicitShare Extends \BaseModel {
		public ?int $partnership_id = null;
		public ?string $resource_class = null;
		public ?int $resource_id = null;
		public $read_only = 1;

		public function __construct($id = 0) {
			$this->_tableName = 'partner_explicit_shares';
			$this->_addFields(array('id','partnership_id','resource_class','resource_id','read_only'));
			parent::__construct($id);
		}

		/** @method public validResourceClass(string): bool */
		public function validResourceClass($class): bool {
			return is_string($class) && (bool)preg_match('/^\\\\?[A-Za-z_][A-Za-z0-9_]*(\\\\[A-Za-z_][A-Za-z0-9_]*)*$/', $class);
		}

		/** @method public addShare(array $parameters): bool */
		public function addShare(array $parameters = []): bool {
			$this->clearError();

			$partnership_id = $parameters['partnership_id'] ?? null;
			if (!is_numeric($partnership_id)) {
				$this->error("Partnership id is required");
				return false;
			}
			$entity = new Entity((int)$partnership_id);
			if (!$entity->exists()) {
				$this->error("Partnership not found");
				return false;
			}

			$resource_class = $parameters['resource_class'] ?? '';
			if (!$this->validResourceClass($resource_class)) {
				$this->error("Invalid resource class. Use Backslash Path format, e.g. \\Product\\Item");
				return false;
			}
			if ($resource_class[0] !== '\\') $resource_class = '\\'.$resource_class;

			$resource_id = $parameters['resource_id'] ?? null;
			if (!is_numeric($resource_id) || (int)$resource_id < 1) {
				$this->error("A valid resource id is required");
				return false;
			}

			$read_only = !empty($parameters['read_only']) ? 1 : 0;

			return parent::add(array(
				'partnership_id' => (int)$partnership_id,
				'resource_class' => $resource_class,
				'resource_id' => (int)$resource_id,
				'read_only' => $read_only
			));
		}

		/** @method public delete(): bool */
		public function delete(): bool {
			$this->clearError();
			if (empty($this->id)) {
				$this->error("Share id required");
				return false;
			}
			$database = new \Database\Service();
			$query = "DELETE FROM partner_explicit_shares WHERE id = ?";
			$database->AddParam($this->id);
			$database->Execute($query);
			if ($database->ErrorMsg()) {
				$this->SQLError($database->ErrorMsg());
				return false;
			}
			$this->_exists = false;
			$this->id = null;
			return true;
		}
	}
