<?php
	/** @class Register\Organization\Association\Invitation
	 * Handles invitations for associations between organizations.
	 * Invitations are stored in the register_organization_association_invitations table, which links an organization to its invited organizations.
	 * This class provides methods to create, delete, and retrieve invitations, as well as check if an invitation exists.
	 */
	namespace Register\Organization\Association;

	class Invitation Extends \BaseModel {
		public ?int $id = null;						// Auto-incrementing ID for the invitation
		public ?int $organization_id;				// The organization that is sending the invitation
		public ?string $token;						// Unique token for the invitation, used for accepting/declining
		public ?int $user_id = null;				// User that created the invitation
		public ?int $associated_organization_id;	// The organization that accepted the invitation
		public ?string $association_type;			// The type of association being invited to (e.g., "VENDOR","CUSTOMER","PARTNER"))
		public ?string $date_created;				// Timestamp of when the invitation was created
		public ?string $date_expires;				// Timestamp of when the invitation expires
		public ?string $date_associated;			// Timestamp of when the invitation was accepted and the association was created
		public ?int $associated_customer_id;		// The customer that accepted the invitation and is now associated

		/** @method __construct(id = null)
		 * @param int|null $id
		 */
		public function __construct($id = null) {
			$this->_tableName = "register_organization_association_invitations";
			$this->_fields = array("id","organization_id","invited_organization_id","invitation_type","status");

			if ($id) {
				$this->id = $id;
				$this->details();
			}
		}

		/** @method public add(parameters)
		 * Creates a new invitation for an association between organizations.
		 * @param string $association_type The type of association being invited to (e.g., "VENDOR","CUSTOMER","PARTNER").
		 * @param string|null $last_for The duration for which the invitation is valid, in a format accepted by strtotime (e.g., "+1 week", "+1 month").
		 * @return bool True on success, false on failure.
		 */
		public function add($parameters = array()): bool {
			// Clear Previous Errors
			$this->clearErrors();

			// Validate Parameters
			if (empty($parameters['association_type'])) {
				$this->addError("Association type is required.");
				return false;
			}
			if (! $this->validateAssociationType($parameters['association_type'])) {
				$this->addError("Invalid association type.");
				return false;
			}
			if (empty($parameters['last_for'])) {
				$this->addError("Expiration duration is required.");
				return false;
			}
			$expires_timestamp = strtotime($parameters['last_for']);
			if ($expires_timestamp === false) {
				$this->addError("Invalid expiration duration format.");
				return false;
			}

			// Generate Unique Token
			$this->token = bin2hex(random_bytes(16));

			// Set Expiration Date
			$this->date_expires = date("Y-m-d H:i:s", $expires_timestamp);

			// Initialize Database Service
			$database = new \Database\Service();

			// Prepare Query
			$add_object_query = "
				INSERT
				INTO	register_organization_association_invitations
				(		organization_id,
						association_type,
						token,
						date_created,
						date_expires,
						user_id)
				VALUES
				(		?,
						?,
						?,
						sysdate(),
						?,
						?
				)
			";

			// Bind Parameters
			$database->AddParam($GLOBALS['_SESSION_']->customer()->organization_id);
			$database->AddParam($parameters['association_type']);
			$database->AddParam($this->token);
			$database->AddParam($this->date_expires);
			$database->AddParam($GLOBALS['_SESSION_']->customer()->id);

			// Execute Query
			$database->Execute($add_object_query);
			if ($database->error()) {
				$this->addError("Database error creating invitation: ".$database->error());
				return false;
			}

			// Set ID of Current Object
			$this->id = $database->Insert_ID();

			return $this->update($parameters);
		}

		/** @method public update()
		 * Updates the invitation with new parameters. Currently supports updating the associated organization when an invitation is accepted.
		 * @param array $parameters The parameters to update (e.g., 'associated_organization_id' => 123)
		 * @return bool True on success, false on failure.
		 */
		public function update($parameters = array()): bool {
			// Clear Previous Errors
			$this->clearErrors();

			// See if Invitation Already Accepted
			if ($this->date_associated) {
				$this->addError("Invitation has already been accepted.");
				return false;
			}

			// Initialize Database Service
			$database = new \Database\Service();

			// Prepare Query
			$update_query = "
				UPDATE	register_organization_association_invitations
				SET		1 = 1
			";

			// Bind Parameters
			if (!empty($parameters['associated_organization_id'])) {
				$update_query .= ",
					associated_organization_id = ?,
					date_associated = sysdate(),
					associated_customer_id = ?
				";
				$database->AddParam($parameters['associated_organization_id']);
				$database->AddParam($GLOBALS['_SESSION_']->customer()->id);
			}
	
			if (!empty($parameters['association_type'])) {
				if (! $this->user_id || $this->user_id != $GLOBALS['_SESSION_']->customer()->id) {
					$this->addError("Only the user that created the invitation can update the association type.");
					return false;
				}
				if (! $this->validateAssociationType($parameters['association_type'])) {
					$this->addError("Invalid association type.");
					return false;
				}
				$update_query .= ",
					association_type = ?
				";
				$database->AddParam($parameters['association_type']);
			}

			if (!empty($parameters['date_expires'])) {
				if (! $this->user_id || $this->user_id != $GLOBALS['_SESSION_']->customer()->id) {
					$this->addError("Only the user that created the invitation can update the expiration date.");
					return false;
				}
				$expires_timestamp = strtotime($parameters['date_expires']);
				if ($expires_timestamp === false) {
					$this->addError("Invalid expiration date format.");
					return false;
				}
				$update_query .= ",
					date_expires = ?
				";
				$database->AddParam(date("Y-m-d H:i:s", $expires_timestamp));
			}

			// Add ID for WHERE Clause
			$update_query .= "
				WHERE id = ?
			";
			$database->AddParam($this->id);

			// Execute Query
			$database->Execute($update_query);
			if ($database->error()) {
				$this->addError("Database error updating invitation: ".$database->error());
				return false;
			}

			return $this->details();
		}

		/** @method public status()
		 * Returns the current status of the invitation based on the date fields and whether it has been accepted.
		 * Possible return values: "pending", "accepted", "expired"
		 * @return string The status of the invitation
		 */
		public function status() {
			if ($this->date_associated) {
				return "accepted";
			}
			else if ($this->date_expires && strtotime($this->date_expires) < time()) {
				return "expired";
			}
			else {
				return "pending";
			}
		}

		/** @method public isExpired()
		 * Checks if the invitation has expired based on the date_expires field.
		 * @return bool True if the invitation has expired, false otherwise
		 */
		public function isExpired() {
			return $this->date_expires && strtotime($this->date_expires) < time();
		}

		/** @method public organization()
		 * Returns the organization that sent the invitation.
		 * @return \Register\Organization The organization that sent the invitation
		 */
		public function organization() {
			return new \Register\Organization($this->organization_id);
		}

		/** @method public user()
		 * Returns the user that created the invitation, if applicable.
		 * @return \Register\Person|null The user that created the invitation, or null if not set
		 */
		public function user() {
			if ($this->user_id) {
				return new \Register\Person($this->user_id);
			}
			return null;
		}

		/** @method public invitedOrganization()
		 * Returns the organization that accepted the invitation.
		 * @return \Register\Organization The organization that accepted the invitation
		 */
		public function invitedOrganization() {
			if ($this->associated_organization_id) {
				return new \Register\Organization($this->associated_organization_id);
			}
			return null;
		}

		/** @method public validAssociationType(type)
		 * Validates the association type against a predefined list of valid types.
		 * @param string $type The association type to validate
		 * @return bool True if the association type is valid, false otherwise
		 */
		public function validateAssociationType(string $type): bool {
			$typeList = new TypeList();
			$validTypes = $typeList->getAll();
			foreach ($validTypes as $validType) {
				if ($validType->name === $type) {
					return true;
				}
			}
			return false;
		}
	}