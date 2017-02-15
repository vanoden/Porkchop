<?
	namespace Site;

	class Domain
	{
		private $schema_version = 1;
		public	$error;
		public	$id;

		public function __construct()
		{	
		}

		public function find($parameters = array())
		{
			$find_objects_query = "
				SELECT	id
				FROM	company_domains
				WHERE	id = id";

			if ($parameters['name'])
			{
				$find_objects_query .= "
				AND		domain_name = ".$GLOBALS['_database']->qstr($parameters['name'],get_magic_quotes_gpc());
			}

			$rs = $GLOBALS['_database']->Execute($find_objects_query);
			if (! $rs)
			{
				$this->error = "SQL Error in company::CompanyDomain::find: ".$GLOBALS['_database']->ErrorMsg();
				return 0;
			}
			
			$objects = array();
			while (list($id) = $rs->FetchRow())
			{
				array_push($objects,$this->details($id));
			}
			return $objects;
		}

		public function details($id=0)
		{
			if (! preg_match('/^\d+$/',$id))
			{
				$this->error = "Valid id required for details in company::CompanyDomain::details";
				return null;
			}

			$get_details_query = "
				SELECT	*,
						domain_name name
				FROM	company_domains
				WHERE	id = ?
			";
			$rs = $GLOBALS['_database']->Execute($get_details_query,array($id));
			if (! $rs)
			{
				$this->error = "SQL Error in company::CompanyDomain::details: ".$GLOBALS['_database']->ErrorMsg();
				return null;
			}
			$array = $rs->FetchRow();
			$object = (object) $array;
			return $object;
		}

		public function add($parameters = array())
		{
			if (! preg_match('/^\d+$/',$GLOBALS['_company']->id))
			{
				$this->error = "company must be set for company::CompanyDomain::add";
				return 0;
			}
			if (! preg_match('/\w/',$parameters['name']))
			{
				$this->error = "name parameter required in company::CompanyDomain::add";
				return 0;
			}
			if (! preg_match('/^(0|1)$/',$parameters['status']))
			{
				$parameters['status'] = 0;
			}
			
			$add_object_query = "
				INSERT
				INTO	company_domains
				(		company_id,
						domain_name,
						status
				)
				VALUES
				(		".$parameters['company_id'].",
						".$GLOBALS['_database']->qstr($parameters['name'],get_magic_quotes_gpc()).",
						".$parameters['status']."
				)
			";

			$GLOBALS['_database']->Execute($add_object_query);
			if ($GLOBALS['_database']->ErrorMsg())
			{
				$this->error = "SQL Error in company::CompanyDomain::add: ".$GLOBALS['_database']->ErrorMsg();
				return 0;
			}
			$this->id = $GLOBALS['_database']->Insert_ID();
			
			return $this->update($this->id,$parameters);
		}

		public function update($id = 0, $parameters = array())
		{

			if (! preg_match('/^\d+$/',$id))
			{
				$this->error = "Valid id required for details in company::CompanyDomain::update";
				return 0;
			}

			if ($parameters['name'])
				$update_object_query .= ",
						domain_name = ".$GLOBALS['_database']->qstr($parameters['name'],get_magic_quotes_gpc());

			if (preg_match('/^(0|1)$/',$parameters['active']))
				$update_object_query .= ",
						active = ".$parameters['active'];

			if (preg_match('/^\d+$/',$parameters['status']))
				$update_object_query .= ",
						status = ".$parameters['status'];

			# Update Object
			$update_object_query = "
				UPDATE	company_domains
				SET		id = id";
			
			$update_object_query .= "
				WHERE	id = $id
			";

			$GLOBALS['_database']->Execute($update_object_query);
			if ($GLOBALS['_database']->ErrorMsg())
			{
				$this->error = "SQL Error in company::CompanyDomain::update: ".$GLOBALS['_database']->ErrorMsg();
				return 0;
			}
			
			return $this->details($id);
		}
	}
?>