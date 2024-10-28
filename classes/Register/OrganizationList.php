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

			$get_organizations_query = "
				SELECT	ro.id
				FROM	register_organizations ro
			";

			// add searched Tag Join
			if (!empty($search_string)) {
				if (!$this->validSearchString($search_string)) {
					app_log("Invalid search string: '$search_string'",'info');
					$this->error("Invalid search string '".$search_string."'");
					return null;
				}

				$get_organizations_query .= ",
				INNER JOIN  search_tags st
				ON 			st.class = 'Register::Organization'
				INNER JOIN	search_tags_xref stx ON stx.object_id = ro.id
				AND 		stx.tag_id = st.id
				WHERE	(
								ro.name = ?
							OR 	ro.code = ?
							OR	(
									st.category = ?
								AND st.value = ?
							)
				)";
				$database->AddParam($search_string);
				$database->AddParam($search_string);
				$database->AddParam($advanced['tag']['type']);
				$database->AddParam($advanced['tag']['string']);
			}

			if (! empty($string)) {
				if (!$this->validSearchString($string)) {
					app_log("Invalid search string: '$string'",'info');
					$this->error("Invalid search string '".$string."'");
					return null;
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

				$get_organizations_query .= "
					WHERE ro.name like '$string'";
			}
			else {
				$get_organizations_query .= "
					WHERE	ro.id = ro.id";
			}
			
			if (!empty($search_string)) {
				$get_organizations_query .= " AND rt.name = ? ";
				$database->AddParam($search_string);
				$get_organizations_query .= " AND rt.type = 'ORGANIZATION' ";
			}

			if (isset($parameters['status']) && is_array($parameters['status'])) {
				$icount = 0;
				$get_organizations_query .= "
				AND	status IN (";
				foreach ($parameters['status'] as $status) {
                    if (!$validationClass->validStatus($status)) {
                        $this->error("Invalid status");
                        return null;
                    }
					if ($icount > 0) $get_organizations_query .= ","; 
					$icount ++;
					if (preg_match('/^[\w\-\_\.]+$/',$status))
					$get_organizations_query .= "'".$status."'";
				}
				$get_organizations_query .= ")";
			}
			elseif (isset($parameters['status'])) {
                if (!$validationClass->validStatus($parameters['status'])) {
                    $this->error("Invalid status");
                    return null;
                }
				$get_organizations_query .= "
				AND		ro.status = ?";
				$database->AddParam($parameters['status']);
			}
			else
				$get_organizations_query .= "
				AND		ro.status IN ('NEW','ACTIVE','EXPIRED')";

			if (is_numeric($parameters['is_reseller'])) 
				$get_organizations_query .= "
				AND		ro.is_reseller = ".$parameters['is_reseller'];


            if (isset($controls['sort'])) {
                if (!$validationClass->hasField($controls['sort'])) {
                    $this->error("Invalid sort field");
                    return null;
                }
                switch($controls['sort']) {
                    case 'status':
                        $get_organizations_query .= "
                        ORDER BY ro.status ".$controls['order'];
                        break;
                    default:
                        $get_organizations_query .= "
                        ORDER BY ro.name ".$controls['order'];
                        break;
                }
            }
            else {
    			$get_organizations_query .= "
	    			ORDER BY ro.name
		    	";
            }

			if (is_numeric($controls['limit'])) {
				$get_organizations_query .= "
                    LIMIT ".$controls['limit'];
                if (is_numeric($controls['offset']))
					$get_organizations_query .= "
					OFFSET	".$controls['offset'];
			}

			$rs = $database->Execute($get_organizations_query);
			if (! $rs) {
				$this->SQLError($database->ErrorMsg());
				return null;
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

		public function findAdvanced($parameters = [], $advanced = [], $controls = []): array {
			$this->clearError();
			$this->resetCount();

            $validationClass = new $this->_modelName();

			// Initialize Database Service
			$database = new \Database\Service();

			// Build Query
			$get_organizations_query = "
				SELECT	ro.id
				FROM	register_organizations ro
			";

			// Apply Tags Filter if specified
			if (isset($advanced['tags']) && !empty($advanced['tags'])) {
				$tagIds = $this->getTagIds($advanced['tags']);
				$get_organizations_query .= ",
								search_tags_xref stx
					WHERE		stx.object_id = ro.id
					AND			stx.tag_id IN (".implode(',',$tagIds).")";
			}
			else {
				$get_organizations_query .= "
					WHERE		ro.id = ro.id";
			}
			
			if (!empty($parameters['name'])) {
				if (isset($parameters['_like']) && in_array("name",$parameters['_like'])) {
					$get_organizations_query .= "
					AND		ro.name like '%".preg_replace('/[^\w\-\.\_\s]/','',$parameters['name'])."%'";
				} else {
					$get_organizations_query .= "
					AND		ro.name = ?";
					$database->AddParam($parameters['name']);
				}
			}

			if (!empty($parameters['code'])) {
                if (! $validationClass->validCode($parameters['code'])) {
                    $this->error("Invalid code");
                    return null;
                }
				$get_organizations_query .= "
				AND		ro.code = ?";
				$database->AddParam($parameters['code']);
			}

			if (!empty($parameters['status']) && is_array($parameters['status'])) {
				$icount = 0;
				$get_organizations_query .= "
				AND	ro.status IN (";
				foreach ($parameters['status'] as $status) {
                    if (! $validationClass->validStatus($status)) {
                        $this->error("Invalid status");
                        return null;
                    }
					if ($icount > 0) $get_organizations_query .= ","; 
					$icount ++;
					if (preg_match('/^[\w\-\_\.]+$/',$status))
					$get_organizations_query .= "'".$status."'";
				}
				$get_organizations_query .= ")";
			}
            elseif (!empty($parameters['status'])) {
                if (! $validationClass($parameters['status'])) {
                    $this->error("Invalid status");
                    return null;
                }
				$get_organizations_query .= "
				AND		ro.status = ?";
				$database->AddParam($parameters['status']);
			} else
				$get_organizations_query .= "
				AND		ro.status IN ('NEW','ACTIVE')";

			if (isset($parameters['reseller_id']) && is_numeric($parameters['reseller_id'])) {
				$get_organizations_query .= "
				AND		ro.reseller_id = ?";
				$database->AddParam($parameters['reseller_id']);
			}

            if (isset($controls['sort'])) {

                if (!$validationClass->hasField($controls['sort'])) {
                    $this->error("Invalid sort field");
                    return null;
                }

				switch($controls['sort']) {
					case 'status':
						$get_organizations_query .= "
						ORDER BY ro.status ".$controls['order'];
						break;
					default:
						$get_organizations_query .= "
						ORDER BY ro.name ".$controls['order'];
						break;
				}
			}
			else {
				$get_organizations_query .= "
				ORDER BY ro.name ".$controls['order'];
			}

			if (isset($controls['limit']) && is_numeric($controls['limit'])) {
                $get_organizations_query .= "
                    LIMIT ".$controls['limit'];
                if (is_numeric($controls['offset'])) $get_organizations_query .= "
                    OFFSET ".$controls['offset'];
			}

			$rs = $database->Execute($get_organizations_query);
			if (! $rs) {
				$this->SQLError($GLOBALS['_database']->ErrorMsg());
				return null;
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
	}
