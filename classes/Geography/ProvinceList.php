<?php
namespace Geography;

class ProvinceList Extends \BaseListClass {
	public function __construct() {
		$this->_tableName = 'geography_provinces';
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

		// Build the query (full row: avoid N+1 Admin::details queries per province)
		$find_objects_query = "
				SELECT	id, code, country_id, name, type, abbreviation, label
				FROM	geography_provinces
				WHERE	id = id
			";

		// Add Parameters
		if (isset($parameters['country_id'])) {
			$find_objects_query .= "
				AND		country_id = ?";
			$database->AddParam($parameters['country_id']);
		}

		if (isset($parameters['name']) && $parameters['name']) {
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
		while ($object = $rs->FetchNextObject(false)) {
			$province = new Province(0);
			$province->id = (int) $object->id;
			$province->code = (string) $object->code;
			$province->country_id = (int) $object->country_id;
			$province->name = (string) $object->name;
			$province->type = isset($object->type) ? (string) $object->type : null;
			$province->abbreviation = (string) $object->abbreviation;
			$province->label = isset($object->label) ? (string) $object->label : null;
			$province->exists(true);
			$province->cached(false);

			$cache = $province->cache();
			if (isset($cache)) {
				$cache->set($object);
			}

			array_push($provinces, $province);
			$this->incrementCount();
		}
		return $provinces;
	}
}
