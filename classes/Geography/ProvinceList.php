<?php
namespace Geography;

class ProvinceList Extends \BaseListClass {
	public function __construct() {
		$this->_modelName = 'Geography\Province';
	}
	
	/**
	 * search provinces by parameters
	 * 
	 * @param array $parameters
	 * @param array $advanced
	 * @param array $controls
	 * @return NULL|array
	 */
	public function findAdvanced($parameters, $advanced, $controls): array {
		$this->clearError();
		$this->resetCount();

		// Initialize Database Service
		$database = new \Database\Service();

		// Initialize Working Class
		$workingClass = new $this->_modelName();

		// Build the query
		$find_objects_query = "
				SELECT	id
				FROM	geography_provinces
				WHERE	id = id
			";

		// Add Parameters
		if (isset($parameters['country_id'])) {
			$find_objects_query .= "
				AND		country_id = ?";
			$database->AddParam($parameters['country_id']);
		}

		if ($parameters['name']) {
			// Handle Wildcards
			if (preg_match('/[\*\?]/',$parameters['name']) && preg_match('/^[\*\?\w\-\.\s]+$/',$parameters['name'])) {
				$parameters['name'] = str_replace('*','%',$parameters['name']);
				$parameters['name'] = str_replace('?','_',$parameters['name']);
				$find_objects_query .= "
				AND	name LIKE ?";
				$database->AddParam($parameters['name']);
			}
			// Handle Exact Match
			elseif ($workingClass->validName($parameters['name'])) {
				$find_objects_query .= "
				AND	name = ?";
				$database->AddParam($parameters['name']);
			}
			else {
				$this->error("Invalid Name");
				return [];
			}
		}

		$rs = $database->Execute($find_objects_query);
		if (! $rs) {
			$this->SQLError($database->ErrorMsg());
			return [];
		}

		$provinces = array ();
		while (list($id) = $rs->FetchRow()) {
			$province = new Province($id);
			$province->id = $id;
			array_push($provinces, $province);
			$this->incrementCount();
		}
		return $provinces;
	}
}
