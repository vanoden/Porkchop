<?php
	namespace Register;

	class TagList Extends \BaseListClass {
		public function __construct() {
			$this->_modelName = '\Register\Tag';
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
