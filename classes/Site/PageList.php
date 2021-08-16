<?php
	namespace Site;
	
	class PageList {
		public $error;

		public function find($parameters = array()) {
			$bind_params = array();
			# Prepare Query
			$get_object_query = "
				SELECT	id
				FROM	page_pages
				WHERE	id = id
			";
			if (isset($parameters['module']) && $parameters['module']) {
				$get_object_query .= "
					AND		module = ?";
				array_push($bind_params,$parameters['module']);
			}
			if (isset($parameters['view']) && $parameters['view']) {
				$get_object_query .= "
					AND		view = ?";
				array_push($bind_params,$parameters['view']);
			}
			if (isset($parameters['index']) && $parameters['index']) {
				$get_object_query .= "
					AND		`index` = ?";
				array_push($bind_params,$parameters['index']);
			}
			$get_object_query .= "
					ORDER BY module,view
			";
			$rs = $GLOBALS['_database']->Execute($get_object_query,$bind_params);
			if (! $rs) {
				$this->error = "SQL Error in PageList::find: ".$GLOBALS['_database']->ErrorMsg();
				return null;
			}
			query_log($get_object_query,$bind_params,true);
			$pages = array();
			while(list($id) = $rs->FetchRow()) {
				$page = new \Site\Page($id);
				array_push($pages,$page);
			}
			return $pages;
		}
	}
