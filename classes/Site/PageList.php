<?php
	namespace Site;
	
	class PageList Extends \BaseListClass {

		public function find($parameters = array()) {
			$this->clearError();
			$this->resetCount();

			$database = new \Database\Service();

			# Prepare Query
			$get_object_query = "
				SELECT	id
				FROM	page_pages
				WHERE	id = id
			";
			if (!empty($parameters['module'])) {
				$get_object_query .= "
					AND		module = ?";
				$database->AddParam($parameters['module']);
			}
			if (!empty($parameters['view'])) {
				$get_object_query .= "
					AND		view = ?";
				$database->AddParam($parameters['view']);
			}
			if (!empty($parameters['index'])) {
				$get_object_query .= "
					AND		`index` = ?";
				$database->AddParam($parameters['index']);
			}
			if (!empty($parameters['sitemap'])) {
				$get_object_query .= "
					AND		`sitemap` = ?";
				if ($parameters['sitemap'] == true || $parameters['sitemap'] == 1) $database->AddParam(1);
				else $database->AddParam(0);
			}

			$get_object_query .= "
					ORDER BY module,view
			";
			$rs = $database->Execute($get_object_query);
			if (! $rs) {
				$this->SQLError($database->ErrorMsg());
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
