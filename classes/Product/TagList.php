<?php
	namespace Product;

	class TagList Extends \BaseListClass {
		public function __construct() {
			$this->_modelName = '\Product\Tag';
		}

		public function findAdvanced($parameters, $advanced, $controls): array {
			$this->clearError();
			$this->resetCount();

			// Initialize Database Service
			$database = new \Database\Service();

			// Build Query
			$get_tags_query = "
				SELECT	id, name
				FROM 	product_tags
				WHERE	id = id
			";

			// Add Parameters
			$validationClass = new $this->_modelName();
			if (!empty($parameters['product_id']) && is_numeric($parameters['product_id'])) {
				$product = new \Product\Item($parameters['product_id']);
				if ($product->exists()) {
					$get_tags_query .= "
					AND		product_id = ?
					";
					$database->AddParam($parameters['product_id']);
				}
				else {
					$this->error("Product not found");
					return false;
				}
			}
			if (!empty($parameters['name'])) {
				if ($validationClass->validName($parameters['name'])) {
					$get_tags_query .= "
					AND     name = ?";
					$database->AddParam($parameters['name']);
				}
				else {
					$this->error("Invalid name");
					return false;
				}
			}
			if (!empty($parameters['id']) && is_numeric($parameters['id'])) {
				$get_tags_query .= "
				AND     id = ?";
				$database->AddParam($parameters['id']);
			}

			// Limit Clause
			$get_tags_query .= $this->limitClause($controls);

			// Execute Query
			$rs = $database->Execute($get_tags_query);
			if (! $rs) {
				$this->SQLError($database->ErrorMsg());
				return [];
			}
			
			$objects = array();
			while (list($id) = $rs->FetchRow()) {
			    $object = new $this->_modelName($id);
			    $this->incrementCount();
			    array_push($objects,$object);
			}
			
			return $objects;
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
