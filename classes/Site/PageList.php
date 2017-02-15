<?
	namespace Site;
	
	class PageList {
		public $error;

        public function __construct () {
			# Initialize Schema
			$schema = new \Site\Page\Schema();
			if (isset($schema->error)) {
				$schema->error = "Error initializing Page schema: ".$init->error;
				return null;
			}
		}
		public function find($parameters = array()) {
			# Prepare Query
			$get_object_query = "
				SELECT	id
				FROM	page_pages
				WHERE	id = id
			";
			if (isset($parameters['module']))
				$get_object_query .= "
					AND		module = ".$GLOBALS['_database']->qstr($parameters['module'],get_magic_quotes_gpc());
			if (isset($parameters['view']))
				$get_object_query .= "
					AND		view = ".$GLOBALS['_database']->qstr($parameters['view'],get_magic_quotes_gpc());
			if (isset($parameters['index']))
				$get_object_query .= "
					AND		`index` = ".$GLOBALS['_database']->qstr($parameters['index'],get_magic_quotes_gpc());
			$rs = $GLOBALS['_database']->Execute($get_object_query);
			if (! $rs) {
				$this->error = "SQL Error in PageList::find: ".$GLOBALS['_database']->ErrorMsg();
				return null;
			}
			$pages = array();
			while(list($id) = $rs->FetchRow()) {
				$page = new \Site\Page($id);
				array_push($pages,$page);
			}
			return $pages;
		}
	}
?>