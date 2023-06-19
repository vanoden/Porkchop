<?php
	namespace Register;

	class OrganizationList Extends \BaseListClass {

		public function search($parameters = array(), $wildcards = false) {
			app_log("Register::OrganizationList::search()",'trace',__FILE__,__LINE__);
			$this->clearError();
			$this->resetCount();

			$database = new \Database\Service();
	
			$get_organizations_query = "
				SELECT	ro.id
				FROM	register_organizations ro
			";

			// add searched Tag Join
			if (isset($parameters['searchedTag']) && !empty($parameters['searchedTag'])) $get_organizations_query .= " INNER JOIN register_tags rt ON rt.register_id = ro.id ";

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
				if ($wildcards) {
					$string = preg_replace('/\*/','%',$string);
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
					if ($icount > 0) $get_organizations_query .= ","; 
					$icount ++;
					if (preg_match('/^[\w\-\_\.]+$/',$status))
					$get_organizations_query .= "'".$status."'";
				}
				$get_organizations_query .= ")";
			}
			elseif (isset($parameters['status'])) {
				$get_organizations_query .= "
				AND		ro.status = ?";
				$database->AddParam($parameters['status']);
			}
			else
				$get_organizations_query .= "
				AND		ro.status IN ('NEW','ACTIVE','EXPIRED')";

			if (isset($parameters['is_reseller'])) {
				if ($parameters['is_reseller'])
					$get_organizations_query .= "
					AND		ro.is_reseller = 1";
				else
					$get_organizations_query .= "
					AND		ro.is_reseller = 0";
			}
			if (isset($parameters['reseller_id'])) {
				$get_organizations_query .= "
				AND		ro.reseller_id = ?";
				$database->AddParam(parameters['reseller_id']);
			}

			$get_organizations_query .= "
				ORDER BY ro.name
			";

			if (isset($parameters['_limit']) and preg_match('/^\d+$/',$parameters['_limit'])) {
				if (preg_match('/^\d+$/',$parameters['_offset']))
					$get_organizations_query .= "
					LIMIT	".$parameters['_offset'].",".$parameters['_limit'];
				else
					$get_organizations_query .= "
					LIMIT	".$parameters['_limit'];
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

		public function find($parameters = array(),$recursive = true) {

			$this->clearError();
			$this->resetCount();
			app_log("Register::OrganizationList::find()",'trace',__FILE__,__LINE__);
			
			$this->error = null;
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
			
			if (isset($parameters['name'])) {
				if (isset($parameters['_like']) && in_array("name",$parameters['_like'])) {
					$get_organizations_query .= "
					AND		ro.name like '%".preg_replace('/[^\w\-\.\_\s]/','',$parameters['name'])."%'";
				} else {
					$get_organizations_query .= "
					AND		ro.name = ?";
					array_push($bind_params,$parameters['name']);
				}
			}

			if (isset($parameters['code'])) {
				$get_organizations_query .= "
				AND		ro.code = ?";
				array_push($bind_params,$parameters['code']);
			}

			if (isset($parameters['status']) && is_array($parameters['status'])) {
				$icount = 0;
				$get_organizations_query .= "
				AND	ro.status IN (";
				foreach ($parameters['status'] as $status) {
					if ($icount > 0) $get_organizations_query .= ","; 
					$icount ++;
					if (preg_match('/^[\w\-\_\.]+$/',$status))
					$get_organizations_query .= "'".$status."'";
				}
				$get_organizations_query .= ")";
			} elseif (isset($parameters['status'])) {
				$get_organizations_query .= "
				AND		ro.status = ?";
				array_push($bind_params,$parameters['status']);
			} else
				$get_organizations_query .= "
				AND		ro.status IN ('NEW','ACTIVE')";

			if (isset($parameters['is_reseller'])) {
				if ($parameters['is_reseller'])
					$get_organizations_query .= "
					AND		ro.is_reseller = 1";
				else
					$get_organizations_query .= "
					AND		ro.is_reseller = 0";
			}
			if (isset($parameters['reseller_id'])) {
				$get_organizations_query .= "
				AND		ro.reseller_id = ?";
				array_push($bind_params,$parameters['reseller_id']);
			}

			$get_organizations_query .= "
				ORDER BY ro.name
			";
			
			if (isset($parameters['_limit']) and preg_match('/^\d+$/',$parameters['_limit'])) {
				if (preg_match('/^\d+$/',$parameters['_offset']))
					$get_organizations_query .= "
					LIMIT	".$parameters['_offset'].",".$parameters['_limit'];
				else
					$get_organizations_query .= "
					LIMIT	".$parameters['_limit'];
			}
			
			query_log($get_organizations_query,$bind_params);
			$rs = $GLOBALS['_database']->Execute($get_organizations_query,$bind_params);
			if (! $rs) {
				$this->SQLError($GLOBALS['_database']->ErrorMsg());
				return null;
			}
			
			$organizations = array();
			while (list($id) = $rs->FetchRow()) {
				if ($recursive) {
					$organization = new Organization($id);
					$this->incrementCount();
					array_push($organizations,$organization);
				} else {
					array_push($organizations,$id);
					$this->incrementCount();
				}
			}
			
			return $organizations;
		}
		
		public function findArray($parameters = array()) {
			$this->clearError();
			$this->resetCount();
			app_log("Register::OrganizationList::findArray()",'trace',__FILE__,__LINE__);
			$this->error = null;
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
