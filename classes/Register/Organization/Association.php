<?php
	/** @class Register\Organization\Association
	 * Handles associations between organizations, allowing for hierarchical relationships and shared resources.
	 * Associations are stored in the register_organization_associations table, which links an organization to its associated organizations.
	 * This class provides methods to create, delete, and retrieve associations, as well as check if an association exists.
	 */
	namespace Register\Organization;

	class Association Extends \BaseXREF {
		/** @method __construct(id = null)
		 * @param int|null $id
		 */
		public function __construct($id = null) {
			$this->_tableName = "register_organization_associations";
			$this->_fields = array("id","organization_id","associated_organization_id","association_type","invitation_id");

			if ($id) {
				$this->id = $id;
				$this->details();
			}
		}

		/** @method public create(organization_id, associated_organization_id, association_type, invitation_id)
		 * Creates a new association between two organizations.
		 * @param int $organization_id The ID of the primary organization.
		 * @param int $associated_organization_id The ID of the associated organization.
		 * @param string $association_type The type of association (e.g., "partner", "subsidiary").
		 * @param int|null $invitation_id Optional ID of the invitation that led to this association.
		 * @return bool True on success, false on failure.
		 */
		public function create(int $organization_id, int $associated_organization_id, string $association_type, ?int $invitation_id = null) {
			// Clear Previous Errors
			$this->clearErrors();

			// Validate Parameters
			$organization = new \Register\Organization($organization_id);
			if ($organization->error()) {
				$this->addError("Invalid organization ID: ".$organization->error());
				return false;
			}
			if (!$organization->exists()) {
				$this->addError("Organization not found.");
				return false;
			}
			$associated_organization = new \Register\Organization($associated_organization_id);
			if ($associated_organization->error()) {
				$this->addError("Invalid associated organization ID: ".$associated_organization->error());
				return false;
			}
			if (!$associated_organization->exists()) {
				$this->addError("Associated organization not found.");
				return false;
			}

			if (empty($association_type)) {
				$this->addError("Association type is required.");
				return false;
			}
			if (! $this->validateAssociationType($association_type)) {
				$this->addError("Invalid association type.");
				return false;
			}

			// Initialize Database Service
			$database = new \Database();

			// Check if Association Already Exists
			if ($this->associationExists($organization_id, $associated_organization_id)) {
				$this->addError("Association already exists.");
				return true;
			}

			// Prepare Query
			$insert_query = "
				INSERT INTO register_organization_associations
				(organization_id, associated_organization_id, association_type, invitation_id)
				VALUES (?, ?, ?, ?)
			";

			// Bind Parameters
			$database->AddParam($organization_id);
			$database->AddParam($associated_organization_id);
			$database->AddParam($association_type);
			$database->AddParam($invitation_id);

			// Execute Query
			$database->Execute($insert_query);
			if ($database->error()) {
				$this->addError("Database error creating association: ".$database->error());
				return false;
			}
			return true;
		}

		/** @method public associationExists(organization_id, associated_organization_id)
		 * Checks if an association already exists between two organizations.
		 * @param int $organization_id The ID of the primary organization.
		 * @param int $associated_organization_id The ID of the associated organization.
		 * @return bool True if the association exists, false otherwise.
		 */
		public function associationExists(int $organization_id, int $associated_organization_id, string $association_type): bool {
			// Clear Errors
			$this->clearErrors();

			// Validate Parameters
			$organization = new \Register\Organization($organization_id);
			if ($organization->error()) {
				$this->addError("Invalid organization ID: ".$organization->error());
				return false;
			}
			if (!$organization->exists()) {
				$this->addError("Organization not found.");
				return false;
			}
			$associated_organization = new \Register\Organization($associated_organization_id);
			if ($associated_organization->error()) {
				$this->addError("Invalid associated organization ID: ".$associated_organization->error());
				return false;
			}
			if (!$associated_organization->exists()) {
				$this->addError("Associated organization not found.");
				return false;
			}
			if (empty($association_type)) {
				$this->addError("Association type is required.");
				return false;
			}
			if (! $this->validateAssociationType($association_type)) {
				$this->addError("Invalid association type.");
				return false;
			}

			// Initialize Database Service
			$database = new \Database();

			// Prepare Query
			$query = "
				SELECT	1
				FROM	register_organization_associations
				WHERE	organization_id = ?
				AND		associated_organization_id = ?
				AND		association_type = ?
			";

			// Bind Parameters
			$database->AddParam($organization_id);
			$database->AddParam($associated_organization_id);
			$database->AddParam($association_type);

			// Execute Query
			$database->Execute($query);
			if ($database->error()) {
				$this->addError("Database error checking association: ".$database->error());
				return false;
			}
			list($found) = $database->FetchRow();
			return $found > 0;
		}
	}