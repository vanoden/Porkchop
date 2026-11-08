<?php
	/** @class Register\Partner\Entity
	 * Approved one-way partnership.  organization_id owns/shares data;
	 * partner_id is granted access.  Destroying this record cascades share rows.
	 */
	namespace Register\Partner;

	class Entity Extends \BaseModel {
		public ?int $organization_id = null;
		public ?int $partner_id = null;
		public ?int $request_id = null;
		public ?string $date_created = null;

		public function __construct($id = 0) {
			$this->_tableName = 'partner_entities';
			$this->_addFields(array('id','organization_id','partner_id','request_id','date_created'));
			parent::__construct($id);
		}

		/** @method public createPartnership(int $organization_id, int $partner_id, ?int $request_id = null): bool
		 * organization_id shares data; partner_id receives access.
		 */
		public function createPartnership(int $organization_id, int $partner_id, ?int $request_id = null): bool {
			$this->clearError();

			if ($organization_id === $partner_id) {
				$this->error("An organization cannot partner with itself");
				return false;
			}
			$organization = new \Register\Organization($organization_id);
			if (!$organization->exists()) {
				$this->error("Sharing organization not found");
				return false;
			}
			$partner = new \Register\Organization($partner_id);
			if (!$partner->exists()) {
				$this->error("Partner organization not found");
				return false;
			}

			$existing = $this->findByOrganizations($organization_id, $partner_id);
			if ($existing) {
				$this->id = $existing->id;
				return $this->details();
			}

			return parent::add(array(
				'organization_id' => $organization_id,
				'partner_id' => $partner_id,
				'request_id' => $request_id,
				'date_created' => date('Y-m-d H:i:s')
			));
		}

		/** @method public findByOrganizations(int $organization_id, int $partner_id): ?Entity */
		public function findByOrganizations(int $organization_id, int $partner_id): ?Entity {
			$database = new \Database\Service();
			$query = "
				SELECT	id
				FROM	partner_entities
				WHERE	organization_id = ?
				AND		partner_id = ?
				LIMIT 1
			";
			$database->AddParam($organization_id);
			$database->AddParam($partner_id);
			$rs = $database->Execute($query);
			if (!$rs) {
				$this->SQLError($database->ErrorMsg());
				return null;
			}
			$row = $rs->FetchRow();
			if (!$row) return null;
			list($id) = $row;
			$entity = new Entity($id);
			return $entity->exists() ? $entity : null;
		}

		/** @method public organization(): \Register\Organization */
		public function organization() {
			return new \Register\Organization($this->organization_id);
		}

		/** @method public partner(): \Register\Organization */
		public function partner() {
			return new \Register\Organization($this->partner_id);
		}

		/** @method public delete(): bool
		 * Deletes partnership; share rows are removed via ON DELETE CASCADE.
		 */
		public function delete(): bool {
			$this->clearError();
			if (empty($this->id)) {
				$this->error("Partnership id required");
				return false;
			}
			$database = new \Database\Service();
			$query = "DELETE FROM partner_entities WHERE id = ?";
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
