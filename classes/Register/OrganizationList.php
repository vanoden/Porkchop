<?
	namespace Register;

	class OrganizationList {
		public $count = 0;
		public function find($parameters = array(),$recursive = true) {
			app_log("Register::OrganizationList::find()",'trace',__FILE__,__LINE__);
			$this->error = null;
			$get_organizations_query = "
				SELECT	id
				FROM	register_organizations
				WHERE	id = id
			";

			if (isset($parameters['name'])) {
				if (in_array("name",$parameters['_like'])) {
					$get_organizations_query .= "
					AND		name like '%".preg_replace('/[^\w\-\.\_\s]/','',$parameters['name'])."%'";
				}
				else {
					$get_organizations_query .= "
					AND		name = ".$GLOBALS['_database']->qstr($parameters['name'],get_magic_quotes_gpc);
				}
			}
			if (isset($parameters['code'])) {
				$get_organizations_query .= "
				AND		code = ".$GLOBALS['_database']->qstr($parameters['code'],get_magic_quotes_gpc);
			}
			if (isset($parameters['status']))
				$get_organizations_query .= "
				AND		status = ".$GLOBALS['_database']->qstr($parameters['status'],get_magic_quotes_gpc);
			else
				$get_organizations_query .= "
				AND		status IN ('NEW','ACTIVE')";
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
			$rs = $GLOBALS['_database']->Execute($get_organizations_query);
			if (! $rs) {
				$this->error = "SQL Error in register::organization::find: ".$GLOBALS['_database']->ErrorMsg();
				return null;
			}
			$organizations = array();
			while (list($id) = $rs->FetchRow()) {
				if ($recursive) {
					$organization = new Organization($id);
					$organization->details();
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
	}
?>