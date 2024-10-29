<?php
namespace Network;

class LeaseList Extends \BaseListClass {
	public function __construct() {
		$this->_modelName = '\Network\Lease';
	}

	public function findAdvanced($parameters, $advanced, $controls): array {
		$this->clearError();
		$this->resetCount();

		// Initialize Database Service
		$database = new \Database\Service();

		// Build Query
		$find_objects_query = "
				SELECT	mac_address
				FROM	network_leases
				WHERE	mac_address = mac_address
			";

		// Add Parameters
		$validationClass = new $this->_modelName();

		if (isset($parameters["mac_address"])) {
			if ($validationClass->validateMacAddress($parameters["mac_address"])) {
				$find_objects_query .= "
						AND		mac_address = ?";
				$database->AddParam($parameters['mac_address']);
			}
			else {
				$this->error("Invalid mac address");
				return null;
			}
		}
		if (isset($parameters["ip_address"])) {
			if ($validationClass->validIPAddress($parameters['ip_address'])) {
				$find_objects_query .= "
						AND		ip_address = ?";
				$database->AddParam($parameters['ip_address']);
			}else {
				$this->error("Invalid ip address");
				return null;
			}
		}
		if (isset($parameters["hostname"])) {
			if ($validationClass->validHostname($parameters['hostname'])) {
				$find_objects_query .= "
						AND		hostname = ?";
				$database->AddParam($parameters['hostname']);
			}
			else {
				$this->error("Invalid hostname");
				return null;
			}
		}

		$rs = $database->Execute($find_objects_query);
		if (!$rs) {
			$this->SQLError($database->ErrorMsg());
			return null;
		}
		$objects = array();
		while (list($mac_address) = $rs->FetchRow()) {
			$object = new Lease($mac_address);
			array_push($objects, $object);
			$this->incrementCount();
		}
		return $objects;
	}
}