<?php
	namespace Register;

	class Location extends \BaseModel {
	
		public $name;
		public $address_1;
		public $address_2;
		public $city;
		public $province_id;
		public $zip_code;
		public $notes;
		public $country_id;
		public $hidden;
		public $province;
		public $country;

		public function __construct($id = 0,$parameters = array()) {		
			$this->_tableName = 'register_locations';
			$this->_addFields(array('id','name','address_1','address_2','city','province_id','zip_code', 'notes','country_id','hidden'));
			parent::__construct($id);
			if (isset($parameters['recursive']) && $parameters['recursive']) {
				$this->province = new \Geography\Province($this->province_id);
				$this->country = new \Geography\Country($this->province->country_id);
			}
		}

		/** @method public add($parameters)
		 * Add new Location record
		 * @param array $parameters
		 * @return bool
		 */
		public function add($parameters = array()) {
			$province = new \Geography\Province($parameters['province_id']);
			if (!$province->id) {
				$this->error("Province not found");
				return false;
			}
			// Ensure province details are loaded to get country_id
			if (empty($province->country_id)) {
				$province->details();
			}
			$parameters['country_id'] = $province->country_id;
			return parent::add($parameters);
		}

		/** @method public update($parameters)
		 * Update Location record
		 * @param array $parameters
		 * @return bool
		 */
		public function update($parameters = []): bool {
			// If province_id is being updated, also update country_id
			if (isset($parameters['province_id']) && $parameters['province_id'] != $this->province_id) {
				$province = new \Geography\Province($parameters['province_id']);
				if (!$province->id) {
					$this->error("Province not found");
					return false;
				}
				// Ensure province details are loaded to get country_id
				if (empty($province->country_id)) {
					$province->details();
				}
				$parameters['country_id'] = $province->country_id;
			}
			return parent::update($parameters);
		}

        /** @method public findExistingByAddress($parameters)
         * find existing entry by user provided address info
         * @param array $parameters
		 * @return bool
         */
        public function findExistingByAddress($parameters = array()): bool {
			$this->clearError();

			$database = new \Database\Service();

			// Sanitize input parameters
			if (!empty($parameters['address_1'])) {
				$parameters['address_1'] = $database->escapeString($parameters['address_1']);
			} else {
				$parameters['address_1'] = '';
			}
			if (!empty($parameters['address_2'])) {
				$parameters['address_2'] = $database->escapeString($parameters['address_2']);
			} else {
				$parameters['address_2'] = '';
			}
			if (!empty($parameters['city'])) {
				$parameters['city'] = $database->escapeString($parameters['city']);
			} else {
				$parameters['city'] = '';
			}
			if (!empty($parameters['zip_code'])) {
				$parameters['zip_code'] = $database->escapeString($parameters['zip_code']);
			} else {
				$parameters['zip_code'] = '';
			}

			// Build query to find existing location by address fields
            $getObjectQuery = "SELECT id FROM `$this->_tableName` WHERE
                LOWER(`address_1`) LIKE '%".strtolower($parameters['address_1'])."%'
                AND LOWER(`address_2`) LIKE '%".strtolower($parameters['address_2'])."%'
                AND LOWER(`city`) LIKE '%".strtolower($parameters['city'])."%'
                AND LOWER(`zip_code`) LIKE '%".strtolower($parameters['zip_code'])."%'
			";
			
			// Execute Query
			$rs = $database->Execute($getObjectQuery);
            if ($rs) {
                list($id) = $rs->FetchRow();
                if ($id) {
                    $this->id = $id;
                    $this->details();
                    return true;
                }
            }
            return false;
        }

		/** @method public associateUser(user_id)
		 * Associate Location with User
		 * @param int $user_id
		 * @return bool
		 */
		public function associateUser($user_id) {
			$add_record_query = "
				INSERT
				INTO	register_user_locations
				(user_id,location_id)
				VALUES	(?,?)
				ON DUPLICATE KEY UPDATE
				location_id = location_id
			";
			$bind_params = array($user_id,$this->id);
			query_log($add_record_query,$bind_params,true);
			$GLOBALS['_database']->Execute($add_record_query,$bind_params);
			if ($GLOBALS['_database']->ErrorMsg()) {
				$this->SQLError($GLOBALS['_database']->ErrorMsg());
				return false;
			}
			return true;
		}

		/** @method public associateOrganization(organization_id, location_name)
		 * Associate Location with Organization
		 * @param int $organization_id
		 * @param string $location_name
		 * @return bool
		 */
		public function associateOrganization($organization_id, $location_name = '') {
			$add_record_query = "
				INSERT
				INTO	register_organization_locations
				(organization_id, location_id)
				VALUES	(?,?)
				ON DUPLICATE KEY UPDATE
				location_id = location_id
			";
			$bind_params = array($organization_id,$this->id);
			query_log($add_record_query,$bind_params,true);
			$GLOBALS['_database']->Execute($add_record_query,$bind_params);
			if ($GLOBALS['_database']->ErrorMsg()) {
				$this->SQLError($GLOBALS['_database']->ErrorMsg());
				return false;
			}
			return true;
		}

        public function applyDefaultBillingAndShippingAddresses($organizationId, $locationId, $isDefaultBilling=false, $isDefaultShipping=false) {
            // bust register_organizations cache
            $cache_key = "organization[".$organizationId."]";
            $cache_item = new \Cache\Item($GLOBALS['_CACHE_'],$cache_key);
            $cache_item->delete();
        
            // Handle billing address independently
            if (!empty($isDefaultBilling)) {
                // Set this location as default billing
                $update_record_query = "
                    UPDATE `register_organizations` SET `default_billing_location_id` = ? WHERE id = ?;
                ";
                $bind_params = array($locationId, $organizationId);	                
                query_log($update_record_query,$bind_params,true);
                $GLOBALS['_database']->Execute($update_record_query,$bind_params);
                if ($GLOBALS['_database']->ErrorMsg()) {
                    $this->SQLError($GLOBALS['_database']->ErrorMsg());
                    return false;
                }
            } else {
                // Clear default billing if this location was previously set as default
                $check_current_query = "
                    SELECT default_billing_location_id FROM `register_organizations` WHERE id = ?;
                ";
                $rs = $GLOBALS['_database']->Execute($check_current_query, array($organizationId));
                if ($rs && !$rs->EOF) {
                    list($current_billing_id) = $rs->FetchRow();
                    if ($current_billing_id == $locationId) {
                        $update_record_query = "
                            UPDATE `register_organizations` SET `default_billing_location_id` = NULL WHERE id = ?;
                        ";
                        $bind_params = array($organizationId);
                        query_log($update_record_query,$bind_params,true);
                        $GLOBALS['_database']->Execute($update_record_query,$bind_params);
                        if ($GLOBALS['_database']->ErrorMsg()) {
                            $this->SQLError($GLOBALS['_database']->ErrorMsg());
                            return false;
                        }
                    }
                }
            }
            
            // Handle shipping address independently
            if (!empty($isDefaultShipping)) {
                // Set this location as default shipping
                $update_record_query = "
                    UPDATE `register_organizations` SET `default_shipping_location_id` = ? WHERE id = ?;
                ";
                $bind_params = array($locationId, $organizationId);
                query_log($update_record_query,$bind_params,true);
                $GLOBALS['_database']->Execute($update_record_query,$bind_params);
                if ($GLOBALS['_database']->ErrorMsg()) {
                    $this->SQLError($GLOBALS['_database']->ErrorMsg());
                    return false;
                }
            } else {
                // Clear default shipping if this location was previously set as default
                $check_current_query = "
                    SELECT default_shipping_location_id FROM `register_organizations` WHERE id = ?;
                ";
                $rs = $GLOBALS['_database']->Execute($check_current_query, array($organizationId));
                if ($rs && !$rs->EOF) {
                    list($current_shipping_id) = $rs->FetchRow();
                    if ($current_shipping_id == $locationId) {
                        $update_record_query = "
                            UPDATE `register_organizations` SET `default_shipping_location_id` = NULL WHERE id = ?;
                        ";
                        $bind_params = array($organizationId);
                        query_log($update_record_query,$bind_params,true);
                        $GLOBALS['_database']->Execute($update_record_query,$bind_params);
                        if ($GLOBALS['_database']->ErrorMsg()) {
                            $this->SQLError($GLOBALS['_database']->ErrorMsg());
                            return false;
                        }
                    }
                }
            }
            
            return true;
        }
        
		/**
		 * Check if this location has been used on any shipment (send or receive).
		 * Locations used in shipments should not be edited to preserve history.
		 *
		 * @return bool
		 */
		public function usedInShipment(): bool {
			if (!$this->id) {
				return false;
			}
			$check_query = "
				SELECT 1 FROM shipping_shipments
				WHERE send_location_id = ? OR rec_location_id = ?
				LIMIT 1
			";
			$rs = $GLOBALS['_database']->Execute($check_query, array($this->id, $this->id));
			if (!$rs) {
				return false;
			}
			return !$rs->EOF;
		}

		public function province() {
			return new \Geography\Province($this->province_id);
		}

        public function country() {
            $country_id = $this->province()->country_id;
            if (!isset($country_id)) {
                return new \Geography\Country();
            }
            return new \Geography\Country($country_id);
        }

		public function organization() {
			$get_org_query = "
				SELECT	organization_id
				FROM	register_organization_locations
				WHERE	location_id = ?
			";
			$rs = $GLOBALS['_database']->Execute($get_org_query,array($this->id));
			if (! $rs) {
				$this->SQLError($GLOBALS['_database']->ErrorMsg());
				return null;
			}
			list($org_id) = $rs->FetchRow();
			return new \Register\Organization($org_id);
		}

		public function user() {
			$get_user_query = "
				SELECT	user_id
				FROM	register_user_locations
				WHERE	location_id = ?
			";
			$rs = $GLOBALS['_database']->Execute($get_user_query,array($this->id));
			if (! $rs) {
				$this->SQLError($GLOBALS['_database']->ErrorMsg());
				return null;
			}
			list($user_id) = $rs->FetchRow();
			return new \Register\Person($user_id);
		}

        public function HTMLBlockFormat() {
            $address = "";
            if (!empty($this->address_1)) $address = $this->address_1."<br />\n";
            if (!empty($this->address_2)) $address .= $this->address_2."<br />\n";
            $address .= $this->city.", ".$this->province()->abbreviation." ".$this->zip_code."<br />\n";
            $address .= $this->country()->name;
            return $address;
        }

		/**
		 * Organization IDs associated with this location (register_organization_locations).
		 * @return int[]
		 */
		public function getOrganizationIds(): array {
			$ids = array();
			if (!$this->id) return $ids;
			$rs = $GLOBALS['_database']->Execute(
				"SELECT organization_id FROM register_organization_locations WHERE location_id = ?",
				array($this->id)
			);
			if ($rs) {
				while (list($oid) = $rs->FetchRow()) $ids[] = (int)$oid;
			}
			return $ids;
		}

		/**
		 * Location IDs associated with an organization (register_organization_locations).
		 * @param int $organization_id
		 * @return int[]
		 */
		public function locationIdsForOrganization(int $organization_id): array {
			$ids = array();
			$rs = $GLOBALS['_database']->Execute(
				"SELECT location_id FROM register_organization_locations WHERE organization_id = ?",
				array($organization_id)
			);
			if ($rs) {
				while (list($lid) = $rs->FetchRow()) $ids[] = (int)$lid;
			}
			return $ids;
		}

		/**
		 * Whether this location is linked to an organization.
		 * @param int $organization_id
		 * @return bool
		 */
		public function belongsToOrganization(int $organization_id): bool {
			return in_array($organization_id, $this->getOrganizationIds(), true);
		}

		/**
		 * Whether this location is linked to a user (register_user_locations).
		 * @param int $user_id
		 * @return bool
		 */
		public function belongsToUser(int $user_id): bool {
			if (!$this->id) return false;
			$rs = $GLOBALS['_database']->Execute(
				"SELECT 1 FROM register_user_locations WHERE user_id = ? AND location_id = ? LIMIT 1",
				array($user_id, $this->id)
			);
			return $rs && !$rs->EOF;
		}

		/**
		 * Next copy number for a base name (e.g. "Site" -> 1, or 2 if "Site (copy 1)" exists).
		 * Scopes by organization if provided.
		 * @param string $baseName
		 * @param int|null $organization_id
		 * @return int
		 */
		public function nextCopyNumberForBaseName(string $baseName, ?int $organization_id = null): int {
			$likePattern = $baseName . ' (copy%';
			if ($organization_id !== null) {
				$rs = $GLOBALS['_database']->Execute(
					"SELECT rl.name FROM register_locations rl INNER JOIN register_organization_locations rol ON rol.location_id = rl.id WHERE rol.organization_id = ? AND rl.name LIKE ?",
					array($organization_id, $likePattern)
				);
			} else {
				$rs = $GLOBALS['_database']->Execute(
					"SELECT name FROM register_locations WHERE name LIKE ?",
					array($likePattern)
				);
			}
			$maxNum = 0;
			if ($rs) {
				while (list($name) = $rs->FetchRow()) {
					if (preg_match('/\s*\(copy\s+(\d+)\)\s*$/', $name, $m))
						$maxNum = max($maxNum, (int)$m[1]);
					elseif (preg_match('/\s*\(copy\)\s*$/', $name))
						$maxNum = max($maxNum, 1);
				}
			}
			return $maxNum + 1;
		}
	}
