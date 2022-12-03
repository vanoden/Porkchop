<?php
	namespace Register;

	class TagList Extends \BaseListClass {

		public function find($parameters = array()) {
			app_log("Register::TagList::find()",'trace',__FILE__,__LINE__);
			$this->resetCount();
			$this->clearError();
			
			$this->error = null;
			$get_tags_query = "
				SELECT	id, name
				FROM	register_tags
				WHERE	id = id
			";

			$bind_params = array();
			if (isset($parameters['type']) && !empty($parameters['type'])) {
				$get_tags_query .= "
				AND     type = ?";
				array_push($bind_params,$parameters['type']);
			}
			if (isset($parameters['register_id']) && !empty($parameters['register_id'])) {
				$get_tags_query .= "
				AND     register_id = ?";
				array_push($bind_params,$parameters['register_id']);
			}
			if (isset($parameters['name']) && !empty($parameters['name'])) {
				$get_tags_query .= "
				AND     name = ?";
				array_push($bind_params,$parameters['name']);
			}
			
			query_log($get_tags_query,$bind_params);
			$rs = $GLOBALS['_database']->Execute($get_tags_query,$bind_params);
			if (! $rs) {
				$this->SQLError($GLOBALS['_database']->ErrorMsg());
				return null;
			}
			
			$regsterTags = array();
			while (list($id) = $rs->FetchRow()) {
			    $regsterTag = new \Register\Tag($id);
			    $regsterTag->details();
			    $this->incrementCount();
			    array_push($regsterTags,$regsterTag);
			}
			
			return $regsterTags;
		}
		
		public function getDistinct() {
            app_log("Register::TagList::getDistinct()",'trace',__FILE__,__LINE__);
			$this->resetCount();
			$this->clearError();

			$bind_params = array();
			$get_tags_query = "
				SELECT	distinct(name)
				FROM	register_tags
				WHERE	id = id
			";

			query_log($get_tags_query,$bind_params);
			$rs = $GLOBALS['_database']->Execute($get_tags_query,$bind_params);
			if (! $rs) {
				$this->SQLError($GLOBALS['_database']->ErrorMsg());
				return null;
			}

			$regsterTags = array();
			while (list($name) = $rs->FetchRow()) {
				$this->incrementCount();
				$regsterTags[] = $name;
			}
			return $regsterTags;
		}
	}
