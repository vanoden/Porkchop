<?php
	namespace Search;

	class TagList Extends \BaseListClass {

		public function __construct() {
			$this->_modelName = '\Search\Tag';
		}

		public function findAdvanced($parameters, $advanced, $controls): array {
			$this->clearError();
			$this->resetCount();

			// Initialize Database Service
			$database = new \Database\Service();

			// Load the Model Class for Field Validations
			$validationClass = new $this->_modelName();

			// Build the Query
			$find_objects_query = "
				SELECT	id
				FROM	search_tags
				WHERE	id = id";

			if (!empty($parameters['class']) && $validationClass->validCode($parameters['class'])) {
				$find_objects_query .= "
				AND		class = ?";
				$database->AddParam($parameters['class']);
			}

			if (!empty($parameters['category']) && $validationClass->validCode($parameters['category'])) {
				$find_objects_query .= "
				AND		category = ?";
				$database->AddParam($parameters['category']);
			}

			if (!empty($parameters['value']) && $validationClass->validCode($parameters['value'])) {
				$find_objects_query .= "
				AND		value = ?";
				$database->AddParam($parameters['value']);
			}

			// Sort Clause
			if (!empty($controls['sort']) && $validationClass->hasField($controls['sort'])) {
				$find_objects_query .= "
					ORDER BY ".$controls['sort'];
				if (preg_match('/^(asc|desc)$/i',$controls['order']))
					$find_objects_query .= " ".$controls['order'];
			}

			// Limit Clause
			if (!empty($controls['limit'])) {
				if (is_numeric($controls['limit'])) {
					$find_objects_query .= "
						LIMIT ".$controls['limit'];
					if (!empty($controls['offset']) && is_numeric($controls['offset'])) {
						$find_objects_query .= "
						OFFSET ".$controls['offset'];
					}
				}
			}

			// Execute Query
			$rs = $database->Execute($find_objects_query);
			if (! $rs) {
				$this->SQLError($database->ErrorMsg());
				return [];
			}

			// Build Response
			$objects = [];
			while (list($id) = $rs->FetchRow()) {
				if ($controls['ids'] == true) {
					array_push($objects, $id);
				}
				else {
					$object = new $this->_modelName($id);
					array_push($objects, $object);
				}
				$this->incrementCount();
			}
			return $objects;
		}
	}