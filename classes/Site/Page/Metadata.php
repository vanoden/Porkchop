<?
	namespace Site\Page;

	class Metadata {
		public $template;

		###################################################
		### Get Page Metadata							###
		###################################################
		public function all($page_id) {
			$get_object_query = "
				SELECT	`key`,value
				FROM	page_metadata
				WHERE	page_id = ?
			";

			$rs = $GLOBALS['_database']->Execute(
				$get_object_query,
				array($page_id)
			);
			if (! $rs) {
				$this->error = "SQL Error getting view metadata in Site::Page::Metadata::get: ".$GLOBALS['_database']->ErrorMsg();
				return null;
			}
			$metadata = array();
			while(list($key,$value) = $rs->FetchRow()) {
				$metadata[$key] = $value;
			}
			$metadata = (object) $metadata;
			return $metadata;
		}
		public function get($page_id,$key = '') {
			if (! $key) return null;
			$get_object_query = "
				SELECT	value
				FROM	page_metadata
				WHERE	page_id = ?
				AND		`key` = ?
			";

			$rs = $GLOBALS['_database']->Execute(
				$get_object_query,
				array($id,$key)
			);
			if (! $rs) {
				$this->error = "SQL Error getting view metadata in Site::Page::Metadata::get: ".$GLOBALS['_database']->ErrorMsg();
				return 0;
			}
			list($value) = $rs->FetchRow();
			return $value;
		}
		public function set($page_id,$key,$value) {
			if (! preg_match('/^\d+$/',$page_id)) {
				$this->error = "Invalid page id in Site::Page::Metadata::set";
				return null;
			}
			if (! isset($key)) {
				$this->error = "Invalid key name in Site::Page::Metadata::set";
				return null;
			}

			$set_data_query = "
				REPLACE
				INTO	page_metadata
				(		page_id,`key`,value)
				VALUES
				(		?,?,?)
			";
			$GLOBALS['_database']->Execute(
				$set_data_query,
				array($page_id,$key,$value)
			);
			if ($GLOBALS['_database']->ErrorMsg()) {
				$this->error = "SQL Error setting metadata in Site::Page::Metadata::add: ".$GLOBALS['_database']->ErrorMsg();
				return null;
			}
			return $this->update($GLOBALS['_database']->Insert_ID(),$parameters);
		}
		###################################################
		### Find Page Metadata							###
		###################################################
		public function find($parameters) {
			$find_data_query = "
				SELECT	id
				FROM	page_metadata
				WHERE	id = id
			";

			if ($paramters['page_id'])
				$find_data_query .= "
					AND		page_id = ".$GLOBALS['_database']->qstr($parameters['page_id'],get_magic_quotes_gpc);
			if ($parameters['key'])
				$find_data_query .= "
					AND		`key` = ".$GLOBALS['_database']->qstr($parameters['key'],get_magic_quotes_gpc);
			if ($parameters['value'])
				$find_data_query .= "
					AND		value = ".$GLOBALS['_database']->qstr($parameters['value'],get_magic_quotes_gpc);

			$rs = $GLOBALS['_database']->Execute($find_data_query);
			if (! $rs)
			{
				$this->error = "SQL Error getting page metadata in Site::Page::Metadata::find: ".$GLOBALS['_database']->ErrorMsg();
				return 0;
			}
			$object_array = array();
			while (list($id) = $rs->FetchRow())
			{
				$object = $this->details($id);
				array_push($object_array,$object);
			}
			return $object_array;
		}
		###################################################
		### Get Details for Metadata					###
		###################################################
		public function details($id = 0) {
			$get_object_query = "
				SELECT	`key`,
						value
				FROM	page_metadata
				WHERE	id = ?
			";
			$rs = $GLOBALS['_database']->Execute(
				$get_object_query,
				array($id)
			);
			if (! $rs)
			{
				$this->error = "SQL Error getting view metadata in Site::Page::Metadata::details: ".$GLOBALS['_database']->ErrorMsg();
				return 0;
			}
			$object_array = array();
			while (list($key,$value) = $rs->FetchRow())
			{
				$object_array[$key] = $value;
			}
			$object = (object) $object_array;
			return $object;
		}
	}
?>
