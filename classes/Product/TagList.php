<?php
	namespace Product;

	class TagList Extends \BaseListClass {

		public function find($parameters = array()) {
		
			app_log("Product::TagList::find()",'trace',__FILE__,__LINE__);
			$this->resetCount();
			$this->clearError();
			
			$this->error = null;
			$get_tags_query = "
				SELECT	id, name
				FROM 	product_tags
				WHERE	id = id
			";

			$bind_params = array();
			if (isset($parameters['product_id']) && !empty($parameters['product_id'])) {
				$get_tags_query .= "
				AND     product_id = ?";
				array_push($bind_params,$parameters['product_id']);
			}
			if (isset($parameters['name']) && !empty($parameters['name'])) {
				$get_tags_query .= "
				AND     name = ?";
				array_push($bind_params,$parameters['name']);
			}			
			if (isset($parameters['id']) && !empty($parameters['id'])) {
				$get_tags_query .= "
				AND     id = ?";
				array_push($bind_params,$parameters['id']);
			}

			query_log($get_tags_query,$bind_params);
			$rs = $GLOBALS['_database']->Execute($get_tags_query,$bind_params);
			if (! $rs) {
				$this->SQLError($GLOBALS['_database']->ErrorMsg());
				return null;
			}
			
			$regsterTags = array();
			while (list($id) = $rs->FetchRow()) {
			    $regsterTag = new \Product\Tag($id);
			    $regsterTag->details();
			    $this->incrementCount();
			    array_push($regsterTags,$regsterTag);
			}
			
			return $regsterTags;
		}
		
		public function getDistinct() {
            app_log("Product::TagList::getDistinct()",'trace',__FILE__,__LINE__);
			$this->resetCount();
			$this->clearError();

			$bind_params = array();
			$get_tags_query = "
				SELECT	distinct(name)
				FROM	product_tags
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
