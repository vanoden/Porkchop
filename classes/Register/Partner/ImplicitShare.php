<?php
	/** @class Register\Partner\ImplicitShare
	 * Resource class + JSON filters granted to a partner organization.
	 */
	namespace Register\Partner;

	class ImplicitShare Extends \BaseModel {
		public ?int $partnership_id = null;
		public ?string $resource_class = null;
		public ?string $filters = null;
		public $read_only = 1;

		public function __construct($id = 0) {
			$this->_tableName = 'partner_implicit_shares';
			$this->_addFields(array('id','partnership_id','resource_class','filters','read_only'));
			parent::__construct($id);
		}

		/** @method public validResourceClass(string): bool
		 * Backslash path object name, e.g. \Product\Item
		 */
		public function validResourceClass($class): bool {
			return is_string($class) && (bool)preg_match('/^\\\\?[A-Za-z_][A-Za-z0-9_]*(\\\\[A-Za-z_][A-Za-z0-9_]*)*$/', $class);
		}

		/** @method public normalizeFilters($filters): string|false */
		public function normalizeFilters($filters) {
			if (is_array($filters)) {
				$encoded = json_encode($filters);
				return $encoded === false ? false : $encoded;
			}
			if ($filters === null || $filters === '') {
				return '{}';
			}
			if (!is_string($filters)) return false;
			$decoded = json_decode($filters, true);
			if (json_last_error() !== JSON_ERROR_NONE) return false;
			$encoded = json_encode($decoded);
			return $encoded === false ? false : $encoded;
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
			// Store leading backslash form
			if ($resource_class[0] !== '\\') $resource_class = '\\'.$resource_class;

			$filters = $this->normalizeFilters($parameters['filters'] ?? '{}');
			if ($filters === false) {
				$this->error("Filters must be valid JSON");
				return false;
			}

			$read_only = !empty($parameters['read_only']) ? 1 : 0;

			return parent::add(array(
				'partnership_id' => (int)$partnership_id,
				'resource_class' => $resource_class,
				'filters' => $filters,
				'read_only' => $read_only
			));
		}

		/** @method public filtersArray(): array */
		public function filtersArray(): array {
			$decoded = json_decode($this->filters ?? '{}', true);
			return is_array($decoded) ? $decoded : array();
		}

		/** @method public delete(): bool */
		public function delete(): bool {
			$this->clearError();
			if (empty($this->id)) {
				$this->error("Share id required");
				return false;
			}
			$database = new \Database\Service();
			$query = "DELETE FROM partner_implicit_shares WHERE id = ?";
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
