<?php
	namespace Register;

	class OrganizationList {
	
		public $count = 0;
		public $error;

		public function search($parameters = array()) {
			app_log("Register::OrganizationList::search()",'trace',__FILE__,__LINE__);
			$this->error = null;
			$get_organizations_query = "
				SELECT	id
				FROM	register_organizations
			";

			$string = $parameters['string'];

			if (! preg_match('/^[\'\w\-\.\_\s\*]+$/',$string)) {
				app_log("Invalid search string: '$string'",'info');
				$this->error = "Invalid search string";
				return undef;
			}
			$string = str_replace("'","\'",$string);
			$string = preg_replace('/\*/','%',$string);

			$get_organizations_query .= "
				WHERE name like '$string'";

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
				AND		status = ".$GLOBALS['_database']->qstr($parameters['status'],get_magic_quotes_gpc);
			}
			else
				$get_organizations_query .= "
				AND		status IN ('NEW','ACTIVE','EXPIRED')";

			if (isset($parameters['is_reseller'])) {
				if ($parameters['is_reseller'])
					$get_organizations_query .= "
					AND		is_reseller = 1";
				else
					$get_organizations_query .= "
					AND		is_reseller = 0";
			}
			if (isset($parameters['reseller_id'])) {
				$get_organizations_query .= "
				AND		reseller_id = ".$GLOBALS['_database']->qstr($parameters['reseller_id'],get_magic_quotes_gpc);
			}

			$get_organizations_query .= "
				ORDER BY name
			";

			if (isset($parameters['_limit']) and preg_match('/^\d+$/',$parameters['_limit'])) {
				if (preg_match('/^\d+$/',$parameters['_offset']))
					$get_organizations_query .= "
					LIMIT	".$parameters['_offset'].",".$parameters['_limit'];
				else
					$get_organizations_query .= "
					LIMIT	".$parameters['_limit'];
			}
			query_log($get_organizations_query);
			$rs = $GLOBALS['_database']->Execute($get_organizations_query);
			if (! $rs) {
				$this->error = "SQL Error in register::organization::find: ".$GLOBALS['_database']->ErrorMsg();
				return null;
			}
			$organizations = array();
			while (list($id) = $rs->FetchRow()) {
				if (1) {
					$organization = new Organization($id,array('nocache' => true));
					$this->count ++;
					array_push($organizations,$organization);
				}
				else {
					array_push($organizations,$id);
					$this->count ++;
				}
			}
			return $organizations;
		}

		public function find($parameters = array(),$recursive = true) {
		
			app_log("Register::OrganizationList::find()",'trace',__FILE__,__LINE__);
			
			$this->error = null;
			$get_organizations_query = "
				SELECT	id
				FROM	register_organizations
				WHERE	id = id
			";

			$bind_params = array();
			if (isset($parameters['name'])) {
				if (isset($parameters['_like']) && in_array("name",$parameters['_like'])) {
					$get_organizations_query .= "
					AND		name like '%".preg_replace('/[^\w\-\.\_\s]/','',$parameters['name'])."%'";
				} else {
					$get_organizations_query .= "
					AND		name = ?";
					array_push($bind_params,$parameters['name']);
				}
			}

			if (isset($parameters['code'])) {
				$get_organizations_query .= "
				AND		code = ?";
				array_push($bind_params,$parameters['code']);
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
			} elseif (isset($parameters['status'])) {
				$get_organizations_query .= "
				AND		status = ?";
				array_push($bind_params,$parameters['status']);
			} else
				$get_organizations_query .= "
				AND		status IN ('NEW','ACTIVE')";

			if (isset($parameters['is_reseller'])) {
				if ($parameters['is_reseller'])
					$get_organizations_query .= "
					AND		is_reseller = 1";
				else
					$get_organizations_query .= "
					AND		is_reseller = 0";
			}
			if (isset($parameters['reseller_id'])) {
				$get_organizations_query .= "
				AND		reseller_id = ?";
				array_push($bind_params,$parameters['reseller_id']);
			}

			$get_organizations_query .= "
				ORDER BY name
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
				$this->error = "SQL Error in register::organization::find: ".$GLOBALS['_database']->ErrorMsg();
				return null;
			}
			
			$organizations = array();
			while (list($id) = $rs->FetchRow()) {
				if ($recursive) {
					$organization = new Organization($id);
					$this->count ++;
					array_push($organizations,$organization);
				}
				else {
					array_push($organizations,$id);
					$this->count ++;
				}
			}
			
			return $organizations;
		}
		
		public function findArray($parameters = array()) {
		
			app_log("Register::OrganizationList::findArray()",'trace',__FILE__,__LINE__);
			$this->error = null;
			$objects = $this->find($parameters);

			$organizations = array();
			foreach ($objects as $object) {
				$organization = array();
				$organization['id'] = $object->id;
				$organization['name'] = $object->name;
				array_push($organizations,$organization);
			}
			return $organizations;
		}
		
		public function expire($threshold = 365) {
		
			if (! is_numeric($threshold)) {
				$this->error = "threshold must be numeric";
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
				$this->error = "SQL Error in Register::OrganizationList::expire(): ".$GLOBALS['_database']->ErrorMsg();
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
