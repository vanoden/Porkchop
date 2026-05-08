<?php
	/** @class Register\Organization\AssociationList
	 * Handles the list of associations between organizations.
	 */
	namespace Register\Organization;

	class AssociationList Extends \BaseListClass {
		/** @method public findAdvanced($parameters, $advanced, $controls)
		 * Finds associations between organizations based on advanced parameters.
		 * @param array $parameters An array of parameters to filter the associations
		 * @param array $advanced An array of advanced parameters for filtering
		 * @param array $controls An array of controls for the search
		 * @return array An array of associations that match the criteria
		 */
		public function findAdvanced($parameters, $advanced, $controls) {
			// Clear Errors
			$this->clearErrors();

			// Reset Count
			$this->resetCount();

			// Initialize Database Service
			$database = new \Database\Service();

			// Prepare Query
			$find_objects_query = "
				SELECT	id
				FROM	register_organization_associations
				WHERE	1 = 1
			";

			// Bind Parameters
			$validation_class = new \Register\Organization\Association();
			if (isset($parameters['organization_id'])) {
				$find_objects_query .= "
					AND organization_id = ?
				";
				$database->AddParam($parameters['organization_id']);
			}
			if (isset($parameters['associated_organization_id'])) {
				$find_objects_query .= "
					AND associated_organization_id = ?
				";
				$database->AddParam($parameters['associated_organization_id']);
			}
			if (isset($parameters['association_type'])) {
				if ($validation_class->validateAssociationType($parameters['association_type']) === false) {
					$this->addError("Invalid association type.");
					return false;
				}
				$find_objects_query .= "
					AND association_type = ?
				";
				$database->AddParam($parameters['association_type']);
			}
			if (isset($parameters['date_associated_before'])) {
				if (strtotime($parameters['date_associated_before']) === false) {
					$this->addError("Invalid date format for date_associated_before.");
					return false;
				}
				$find_objects_query .= "
					AND date_associated < ?
				";
				$database->AddParam($parameters['date_associated_before']);
			}
			if (isset($parameters['date_associated_after'])) {
				if (strtotime($parameters['date_associated_after']) === false) {
					$this->addError("Invalid date format for date_associated_after.");
					return false;
				}
				$find_objects_query .= "
					AND date_associated > ?
				";
				$database->AddParam($parameters['date_associated_after']);
			}
			if (isset($parameters['expired'])) {
				if ($parameters['expired']) {
					$find_objects_query .= "
						AND date_expires < ?
					";
				}
				else {
					$find_objects_query .= "
						AND (date_expires IS NULL OR date_expires > ?)
					";
				}
				$database->AddParam(date('Y-m-d H:i:s'));
			}
			if (isset($parameters['invitation_id'])) {
				$find_objects_query .= "
					AND invitation_id = ?
				";
				$database->AddParam($parameters['invitation_id']);
			}
			if (isset($parameters['token'])) {
				if ($validation_class->validToken($parameters['token']) === false) {
					$this->addError("Invalid token.");
					return false;
				}
				$find_objects_query .= "
					AND token = ?
				";
				$database->AddParam($parameters['token']);
			}

			// Sort Clause
			if ($controls['sortBy']) {
				$find_objects_query .= "
					ORDER BY " . $controls['sortBy'] . " " . ($controls['sortAsc'] ? "ASC" : "DESC") . "
				";
			}
			else {
				$find_objects_query .= "
					ORDER BY id DESC
				";
			}

			// Limit Clause
			if (isset($controls['limit'])) {
				$find_objects_query .= "
					LIMIT ?, ?
				";
				$database->AddParam($controls['offset'] ?? 0);
				$database->AddParam($controls['limit']);
			}

			// Execute Query
			$rs = $database->Execute($find_objects_query);
			if ($database->error()) {
				$this->addError("Database error finding associations: ".$database->error());
				return false;
			}

			// Assemble Results
			$associations = array();
			while (list($id) = $rs->FetchRow()) {
				$association = new Association($id);
				if ($association->error()) {
					$this->addError("Error loading association with ID $id: ".$association->error());
					continue;
				}
				$this->incrementCount();
				array_push($associations,$association);
			}
			return $associations;
		}
	}