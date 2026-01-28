<?php
	namespace Register;

	class OrganizationList Extends \BaseListClass {
        public function __construct() {
            $this->_modelName = '\Register\Organization';
        }

		public function searchAdvanced($search_string, $advanced = [], $controls = []): array {
			
			$this->clearError();
			$this->resetCount();

			if (! $this->validSearchString($search_string)) {
				$this->error("Invalid search string");
				return array();
			}

			return $this->findAdvanced(['search_string' => $search_string], $advanced, $controls);

			// Initialize Database Service
			$database = new \Database\Service();
	
            $validationClass = new $this->_modelName();

			$find_objects_query = "
				SELECT	ro.id
				FROM	register_organizations ro
			";

			// add searched Tag Join
			if (!empty($advanced['tag'])) {
				$find_objects_query .= ",
						search_tags_xref stx
				WHERE	stx.tag_id IN (".implode(',',$this->getTagIds($advanced['tag'])).")";
			}
			else {
				$find_objects_query .= "
				WHERE	ro.id = ro.id";
			}

			if (! empty($string)) {
				if (!$this->validSearchString($string)) {
					app_log("Invalid search string: '$string'",'info');
					$this->error("Invalid search string '".$string."'");
					return [];
				}
				else {
					$string = str_replace("'","\'",$string);
				}

				if (preg_match('/^\*/',$string) || preg_match('/\*$/',$string)) {
					$string = preg_replace('/^\*/','%',$string);
                    $string = preg_replace('/\*$/','%',$string);
				}
				else {
					$string = '%'.$string.'%';
				}

				$find_objects_query .= "
					WHERE ro.name like '$string'";
			}
			else {
				$find_objects_query .= "
					WHERE	ro.id = ro.id";
			}
			
			if (!empty($search_string)) {
				$find_objects_query .= " AND rt.name = ? ";
				$database->AddParam($search_string);
				$find_objects_query .= " AND rt.type = 'ORGANIZATION' ";
			}

			if (isset($parameters['status']) && is_array($parameters['status'])) {
				$icount = 0;
				$find_objects_query .= "
				AND	status IN (";
				foreach ($parameters['status'] as $status) {
                    if (!$validationClass->validStatus($status)) {
                        $this->error("Invalid status");
                        return [];
                    }
					if ($icount > 0) $find_objects_query .= ","; 
					$icount ++;
					if (preg_match('/^[\w\-\_\.]+$/',$status))
					$find_objects_query .= "'".$status."'";
				}
				$find_objects_query .= ")";
			}
			elseif (isset($parameters['status'])) {
                if (!$validationClass->validStatus($parameters['status'])) {
                    $this->error("Invalid status");
                    return [];
                }
				$find_objects_query .= "
				AND		ro.status = ?";
				$database->AddParam($parameters['status']);
			}
			else
				$find_objects_query .= "
				AND		ro.status IN ('NEW','ACTIVE','EXPIRED')";

			if (is_numeric($parameters['is_reseller'])) 
				$find_objects_query .= "
				AND		ro.is_reseller = ".$parameters['is_reseller'];

			if (!empty($parameters['reseller_id']) && is_numeric($parameters['reseller_id'])) {
				$find_objects_query .= "
				AND		ro.reseller_id = ?";
				$database->AddParam($parameters['reseller_id']);
			}
			if (isset($parameters['is_customer']) && is_numeric($parameters['is_customer'])) {
				$find_objects_query .= "
						AND	ro.is_customer = ?";
				$database->AddParam($parameters['is_customer']);
			}
			if (isset($parameters['is_vendor']) && is_numeric($parameters['is_vendor'])) {
				$find_objects_query .= "
						AND	ro.is_vendor = ?";
				$database->AddParam($parameters['is_vendor']);
			}

            if (isset($controls['sort'])) {
                if (!$validationClass->hasField($controls['sort'])) {
                    $this->error("Invalid sort field");
                    return [];
                }
                switch($controls['sort']) {
                    case 'status':
                        $find_objects_query .= "
                        ORDER BY ro.status ".$controls['order'];
                        break;
                    default:
                        $find_objects_query .= "
                        ORDER BY ro.name ".$controls['order'];
                        break;
                }
            }
            else {
    			$find_objects_query .= "
	    			ORDER BY ro.name
		    	";
            }

			if (is_numeric($controls['limit'])) {
				$find_objects_query .= "
                    LIMIT ".$controls['limit'];
                if (is_numeric($controls['offset']))
					$find_objects_query .= "
					OFFSET	".$controls['offset'];
			}

			$rs = $database->Execute($find_objects_query);
			if (! $rs) {
				$this->SQLError($database->ErrorMsg());
				return [];
			}
			$organizations = array();
			while (list($id) = $rs->FetchRow()) {
				if (1) {
					$organization = new Organization($id,array('nocache' => true));
					$this->incrementCount();
					array_push($organizations,$organization);
				}
				else {
					array_push($organizations,$id);
					$this->incrementCount();
				}
			}
			return $organizations;
		}

		public function findAdvanced($parameters, $advanced, $controls): array {
			$this->clearError();
			$this->resetCount();

			// Initialize Database Service
			$database = new \Database\Service();

			// Dereference Working Class
            $workingClass = new $this->_modelName();

			// Build Query
			$find_objects_query = "
				SELECT	ro.id
				FROM	register_organizations ro
			";

			// Apply Tags Filter if specified
			if (isset($advanced['tags']) && !empty($advanced['tags'])) {
				$tagIds = $this->getTagIds($advanced['tags']);
				$find_objects_query .= ",
								search_tags_xref stx
					WHERE		stx.object_id = ro.id
					AND			stx.tag_id IN (".implode(',',$tagIds).")";
			}
			else {
				$find_objects_query .= "
					WHERE		ro.id = ro.id";
			}

			if (!empty($parameters['name'])) {
				// Handle Wildcards
				if (preg_match('/[\*\?]/',$parameters['name']) && preg_match('/^[\*\?\w\-\.\s]+$/',$parameters['name'])) {
					$search_name = str_replace('*','%',$parameters['name']);
					$search_name = str_replace('?','_',$search_name);
					
					// Special case: if search ends with * and has no other wildcards, treat same as no wildcards
					// This makes "Inc" and "Inc*" behave the same way
					if (preg_match('/^[^*?]+\*$/', $parameters['name'])) {
						// Remove the trailing * and add wildcards on both sides
						$base_name = rtrim($parameters['name'], '*');
						$search_name = '%' . $base_name . '%';
					}
					
					$find_objects_query .= "
					AND	ro.name LIKE ?";
					$database->AddParam($search_name);
				}
				// Handle No Wildcards - Add * on both sides
				elseif ($workingClass->validName($parameters['name'])) {
					$search_name = '%' . $parameters['name'] . '%';
					$find_objects_query .= "
					AND		ro.name LIKE ?";
					$database->AddParam($search_name);
				}
				else {
					$this->error("Invalid name");
					return [];
				}
			}

			if (!empty($parameters['code'])) {
                if (! $workingClass->validCode($parameters['code'])) {
                    $this->error("Invalid code");
                    return [];
                }
				$find_objects_query .= "
				AND		ro.code = ?";
				$database->AddParam($parameters['code']);
			}

			if (!empty($parameters['status']) && is_array($parameters['status'])) {
				$icount = 0;
				$find_objects_query .= "
				AND	ro.status IN (";
				foreach ($parameters['status'] as $status) {
                    if (! $workingClass->validStatus($status)) {
                        $this->error("Invalid status");
                        return [];
                    }
					if ($icount > 0) $find_objects_query .= ","; 
					$icount ++;
					if (preg_match('/^[\w\-\_\.]+$/',$status))
					$find_objects_query .= "'".$status."'";
				}
				$find_objects_query .= ")";
			}
            elseif (!empty($parameters['status'])) {
                if (! $workingClass($parameters['status'])) {
                    $this->error("Invalid status");
                    return [];
                }
				$find_objects_query .= "
				AND		ro.status = ?";
				$database->AddParam($parameters['status']);
			}
			else
				$find_objects_query .= "
				AND		ro.status IN ('NEW','ACTIVE')";

			if (isset($parameters['is_reseller'])) {
				if (is_numeric($parameters['is_reseller'])) {
					$find_objects_query .= "
					AND		ro.is_reseller = ?";
					$database->AddParam($parameters['is_reseller']);
				}
				else if ($parameters['is_reseller'] === true || $parameters['is_reseller'] === 'true') {
					$find_objects_query .= "
					AND		ro.is_reseller = 1";
				}
				else if ($parameters['is_reseller'] === false || $parameters['is_reseller'] === 'false') {
					$find_objects_query .= "
					AND		ro.is_reseller = 0";
				}
			}
			if (!empty($parameters['is_vendor'])) {
				if (is_numeric($parameters['is_vendor'])) {
					$find_objects_query .= "
					AND		ro.is_vendor = ?";
					$database->AddParam($parameters['is_vendor']);
				}
				else if ($parameters['is_vendor'] === false || $parameters['is_vendor'] === 'false') {
					$find_objects_query .= "
						AND		ro.is_vendor = 0";
				}
				else if ($parameters['is_vendor'] === true || $parameters['is_vendor'] === 'true') {
					$find_objects_query .= "
						AND		ro.is_vendor > 0";
				}
			}
			if (isset($parameters['is_customer'])) {
				if (is_numeric($parameters['is_customer'])) {
					$find_objects_query .= "
						AND	ro.is_customer = ?";
					$database->AddParam($parameters['is_customer']);
				}
				else if ($parameters['is_customer'] === true || $parameters['is_customer'] === 'true') {
					$find_objects_query .= "
						AND	ro.is_customer = 1";
				}
				else if ($parameters['is_customer'] === false || $parameters['is_customer'] === 'false') {
					$find_objects_query .= "
						AND	ro.is_customer = 0";
				}
			}
			if (isset($parameters['is_vendor']) && is_numeric($parameters['is_vendor'])) {
				$find_objects_query .= "
						AND	ro.is_vendor = ?";
				$database->AddParam($parameters['is_vendor']);
			}
            if (isset($controls['sort'])) {
                if (!$workingClass->hasField($controls['sort'])) {
                    $this->error("Invalid sort field");
                    return [];
                }

				switch($controls['sort']) {
					case 'status':
						$find_objects_query .= "
						ORDER BY ro.status ".$controls['order'];
						break;
					default:
						$find_objects_query .= "
						ORDER BY ro.name ".$controls['order'];
						break;
				}
			}
			else {
				$find_objects_query .= "
				ORDER BY ro.name ";
				if (isset($controls['order']) && !empty($controls['order'])) $find_objects_query .= $controls['order'];
			}

			// Limit Clause
			$find_objects_query .= $this->limitClause($controls);

			// Execute Query
			$rs = $database->Execute($find_objects_query);
			if (! $rs) {
				$this->SQLError($database->ErrorMsg());
				return [];
			}

			$organizations = array();
			while (list($id) = $rs->FetchRow()) {
				if (isset($controls['id']) || isset($controls['count'])) {
					array_push($organizations,$id);
				}
                else {
					$organization = new Organization($id);
					array_push($organizations,$organization);
				}
				$this->incrementCount();
			}
			
			return $organizations;
		}
		
		public function expire($threshold = 365) {
		
			if (! is_numeric($threshold)) {
				$this->error("threshold must be numeric");
				return null;
			}

			# Find Existing Active Organizations
			$find_organizations_query = "
				SELECT	id
				FROM	register_organizations
				WHERE	status in ('NEW','ACTIVE')
				AND		date_created < date_sub(sysdate(),interval 3 month)
			";
			$rs = $GLOBALS['_database']->Execute($find_organizations_query);
			if (! $rs) {
				$this->SQLError($GLOBALS['_database']->ErrorMsg());
				return null;
			}
			$counter = 0;
			while (list($id) = $rs->FetchRow()) {
				# Get Active Accounts
				$organization = new Organization($id);
				$active = $organization->activeCount();
				app_log("Organization ".$organization->name." has $active members",'debug',__FILE__,__LINE__);
				if ($active < 1) {
					$organization->expire();
					$counter ++;
				}
			}
			return $counter;
		}

		/** @method findDuplicateGroups(match_length, min_matches)
		 * Find groups of duplicate organizations based on normalized name matching
		 * @param int $match_length Length of normalized string to match (1-50)
		 * @param int $min_matches Minimum number of matches required (2-100)
		 * @return array|null Array of duplicate groups with 'match_count' and 'nickname' keys, or null on error
		 */
		public function findDuplicateGroups($match_length = 10, $min_matches = 2): ?array {
			app_log("Register::OrganizationList::findDuplicateGroups()",'trace',__FILE__,__LINE__);
			
			$this->clearError();
			
			// Validate parameters
			if ($match_length < 1 || $match_length > 50) {
				$this->error("Match length must be between 1 and 50");
				return null;
			}
			if ($min_matches < 2 || $min_matches > 100) {
				$this->error("Minimum matches must be between 2 and 100");
				return null;
			}
			
			// Initialize Database Service
			$database = new \Database\Service();
			
			$match_length_int = (int) $match_length;
			$min_matches_int = (int) $min_matches;
			$get_duplicates_query = "
				SELECT	COUNT(*) as match_count,
						SUBSTRING(
							REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(
								REPLACE(LOWER(name),'&','and'),
							' ',''),'.',''),'-',''),';',''),':',''),CHAR(9),''),
							1,{$match_length_int}) as nickname
				FROM	register_organizations
				WHERE	status = 'ACTIVE'
				GROUP BY nickname
				HAVING COUNT(*) >= {$min_matches_int}
				ORDER BY match_count DESC, nickname
			";
			
			$rs = $database->Execute($get_duplicates_query);
			if ($database->ErrorMsg()) {
				$this->SQLError($database->ErrorMsg());
				return null;
			}
			
			$duplicate_groups = array();
			while ($row = $rs->FetchRow()) {
				$duplicate_groups[] = array(
					'match_count' => $row[0],
					'nickname' => $row[1]
				);
			}
			
			return $duplicate_groups;
		}

		/** @method findByMatchString(match_string, match_length)
		 * Find organizations matching a specific normalized name string
		 * @param string $match_string The normalized string to match against
		 * @param int $match_length Length of normalized string to match (1-50)
		 * @return array|null Array of organizations with 'id', 'name', 'code', 'status', 'date_created', 'user_count', or null on error
		 */
		public function findByMatchString($match_string, $match_length = 10): ?array {
			app_log("Register::OrganizationList::findByMatchString()",'trace',__FILE__,__LINE__);
			
			$this->clearError();
			
			// Validate parameters
			if (empty($match_string)) {
				$this->error("Match string is required");
				return null;
			}
			if ($match_length < 1 || $match_length > 50) {
				$this->error("Match length must be between 1 and 50");
				return null;
			}
			
			// Initialize Database Service
			$database = new \Database\Service();
			
			$match_length_int = (int) $match_length;
			$get_organizations_query = "
				SELECT	id, name, code, status, date_created,
						(SELECT COUNT(*) FROM register_users WHERE organization_id = register_organizations.id) as user_count
				FROM	register_organizations
				WHERE	status = 'ACTIVE'
				AND		SUBSTRING(
							REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(
								REPLACE(LOWER(name),'&','and'),
							' ',''),'.',''),'-',''),';',''),':',''),CHAR(9),''),
						1,{$match_length_int}) = ?
				ORDER BY name
			";
			
			$rs = $database->Execute($get_organizations_query, array($match_string));
			if ($database->ErrorMsg()) {
				$this->SQLError($database->ErrorMsg());
				return null;
			}
			
			$organizations = array();
			while ($row = $rs->FetchRow()) {
				$organizations[] = array(
					'id' => $row[0],
					'name' => $row[1],
					'code' => $row[2],
					'status' => $row[3],
					'date_created' => $row[4],
					'user_count' => $row[5]
				);
			}
			
			return $organizations;
		}
	}
