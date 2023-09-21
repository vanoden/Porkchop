<?php
	namespace Register;

	class OrganizationList Extends \BaseListClass {
        public function __construct() {
            $this->_modelName = '\Register\Organization';
        }

		public function search($parameters = [], $controls = []) {		
			app_log("Register::OrganizationList::search()",'trace',__FILE__,__LINE__);
			$this->clearError();
			$this->resetCount();

            // Backwards Compatibility
            if (is_bool($controls)) {
                $controls = array('id' => $controls);
            }
            elseif (! is_array($controls)) {
                $this->error("Invalid controls");
                return null;
            }

			$database = new \Database\Service();
	
            $validationClass = new \Register\Organization();

			$get_organizations_query = "
				SELECT	ro.id
				FROM	register_organizations ro
			";

			// add searched Tag Join
			if (isset($parameters['searchedTag']) && !empty($parameters['searchedTag']))
                $get_organizations_query .= " INNER JOIN register_tags rt ON rt.register_id = ro.id ";

			$string = $parameters['string'];
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
			
			if (isset($parameters['searchedTag']) && !empty($parameters['searchedTag'])) {
				$get_organizations_query .= " AND rt.name = ? ";
				$database->AddParam($parameters['searchedTag']);
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

            if (! preg_match('/^desc$/i',$controls['direction'])) $controls['direction'] = 'ASC';
            if (is_numeric($parameters['_limit'])) $controls['limit'] = $parameters['_limit'];
            if (is_numeric($parameters['_offset'])) $controls['offset'] = $parameters['_offset'];

            if (isset($controls['sort'])) {
                if (!$validationClass->hasField($controls['sort'])) {
                    $this->error("Invalid sort field");
                    return null;
                }
                switch($controls['sort']) {
                    case 'status':
                        $get_organizations_query .= "
                        ORDER BY ro.status ".$controls['direction'];
                        break;
                    default:
                        $get_organizations_query .= "
                        ORDER BY ro.name ".$controls['direction'];
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

		public function find($parameters = [],$controls = []) {
			$this->clearError();
			$this->resetCount();
			app_log("Register::OrganizationList::find()",'trace',__FILE__,__LINE__);

            if (is_bool($controls)) {
                $controls = array('id' => $controls);
            }
            elseif (! is_array($controls)) {
                $this->error("Invalid controls");
                return null;
            }

            $validationClass = new \Register\Organization();

			$get_organizations_query = "
				SELECT	ro.id
				FROM	register_organizations ro
			";
			
			$bind_params = array();

			// add searched Tag Join
			if (isset($parameters['searchedTag']) && !empty($parameters['searchedTag'])) $get_organizations_query .= " INNER JOIN register_tags rt ON rt.register_id = ro.id ";
			$get_organizations_query .= " WHERE	ro.id = ro.id ";
			
			if (isset($parameters['searchedTag']) && !empty($parameters['searchedTag'])) {
				$get_organizations_query .= " AND rt.name = ? ";
				array_push($bind_params,$parameters['searchedTag']);
				$get_organizations_query .= " AND rt.type = 'ORGANIZATION' ";
			}
			
			if (!empty($parameters['name'])) {
				if (isset($parameters['_like']) && in_array("name",$parameters['_like'])) {
					$get_organizations_query .= "
					AND		ro.name like '%".preg_replace('/[^\w\-\.\_\s]/','',$parameters['name'])."%'";
				} else {
					$get_organizations_query .= "
					AND		ro.name = ?";
					array_push($bind_params,$parameters['name']);
				}
			}

			if (!empty($parameters['code'])) {
                if (! $validationClass->validCode($parameters['code'])) {
                    $this->error("Invalid code");
                    return null;
                }
				$get_organizations_query .= "
				AND		ro.code = ?";
				array_push($bind_params,$parameters['code']);
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
				array_push($bind_params,$parameters['status']);
			} else
				$get_organizations_query .= "
				AND		ro.status IN ('NEW','ACTIVE')";

			if (is_numeric($parameters['reseller_id'])) {
				$get_organizations_query .= "
				AND		ro.reseller_id = ?";
				array_push($bind_params,$parameters['reseller_id']);
			}

            if (! preg_match('/^desc$/i',$controls['direction'])) $controls['direction'] = 'ASC';
            if (is_numeric($parameters['_limit'])) $controls['limit'] = $parameters['_limit'];
            if (is_numeric($parameters['_offset'])) $controls['offset'] = $parameters['_offset'];

            if (isset($controls['sort'])) {
                if (!$validationClass->hasField($controls['sort'])) {
                    $this->error("Invalid sort field");
                    return null;
                }
            }
            switch($controls['sort']) {
                case 'status':
                    $get_organizations_query .= "
                    ORDER BY ro.status ".$controls['direction'];
                    break;
                default:
                    $get_organizations_query .= "
                    ORDER BY ro.name ".$controls['direction'];
                    break;
            }

			if (is_numeric($controls['limit'])) {
                $get_organizations_query .= "
                    LIMIT ".$controls['limit'];
                if (is_numeric($controls['offset'])) $get_organizations_query .= "
                    OFFSET ".$controls['offset'];
			}
			
			query_log($get_organizations_query,$bind_params);
			$rs = $GLOBALS['_database']->Execute($get_organizations_query,$bind_params);
			if (! $rs) {
				$this->SQLError($GLOBALS['_database']->ErrorMsg());
				return null;
			}
			
			$organizations = array();
			while (list($id) = $rs->FetchRow()) {
				if ($controls['id'] || $controls['count']) {
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
		
		public function findArray($parameters = array()) {
			$this->clearError();
			$this->resetCount();
			app_log("Register::OrganizationList::findArray()",'trace',__FILE__,__LINE__);

			$objects = $this->find($parameters);

			$organizations = array();
			foreach ($objects as $object) {
				$organization = array();
				$organization['id'] = $object->id;
				$organization['name'] = $object->name;
				$this->incrementCount();
				array_push($organizations,$organization);
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
