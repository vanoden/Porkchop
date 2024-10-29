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

		$rs = $database->Execute($find_objects_query, $parameters);
		if (! $rs) {
			$this->SQLError($database->ErrorMsg());
			return null;
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
