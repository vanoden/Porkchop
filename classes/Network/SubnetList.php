<?php
	/* @class Network\SubnetList
	 * @description A service class for managing a list of network subnets.
	 * @package Network
	 */
	namespace Network;

	class SubnetList Extends \BaseListClass {
		private ?int $_matched = null;	// ID of the subnet that was matched in the last contains() check
		private array $_matches = [];	// Array of subnet IDs that were matched in the last contains() check

		public function __construct() {
			$this->_modelName = "\Network\Subnet";
		}

		public function findAdvanced($parameters = [], $controls = [], $advanced = []): array {
			// Clear Errors and Reset Count
			$this->clearErrors();
			$this->resetCount();

			// Initialize Database Service
			$database = new \Database\Service();

			// Prepare Base Query
			$find_objects_query = "
				SELECT	id
				FROM	network_subnets
				WHERE	id = id
			";

			// Validate and Append Parameters to Query
			if (isset($parameters['address'])) {
				$find_objects_query .= "
				AND		address = ?";
				$database->AddParam($parameters['address']);
			}

			if (isset($parameters['size'])) {
				$find_objects_query .= "
				AND		size = ?";
				$database->AddParam($parameters['size']);
			}

			if (isset($parameters['type'])) {
				$find_objects_query .= "
				AND		type = ?";
				$database->AddParam($parameters['type']);
			}

			if (isset($parameters['managed']) && preg_match('/^(AUTO|MANUAL)$/', $parameters['managed'])) {
				$find_objects_query .= "
				AND		managed = ?";
				$database->AddParam($parameters['managed']);
			}

			if (!empty($parameters['risk_level_min']) && is_numeric($parameters['risk_level_min'])) {
				$find_objects_query .= "
				AND		risk_level >= ?";
				$database->AddParam($parameters['risk_level_min']);
			}
			else if (isset($parameters['risk_level_min'])) {
				$this->error("Invalid risk_level_min parameter");
				return [];
			}

			if (!empty($parameters['risk_level_max']) && is_numeric($parameters['risk_level_max'])) {
				$find_objects_query .= "
				AND		risk_level <= ?";
				$database->AddParam($parameters['risk_level_max']);
			}
			else if (isset($parameters['risk_level_max'])) {
				$this->error("Invalid risk_level_max parameter");
				return [];
			}

			if (!empty($parameters['date_last_seen_after']) && preg_match('/^\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2}$/', $parameters['date_last_seen_after'])) {
				$find_objects_query .= "
				AND		date_last_seen >= ?";
				$database->AddParam($parameters['date_last_seen_after']);
			}
			else if (isset($parameters['date_last_seen_after'])) {
				$this->error("Invalid date_last_seen_after parameter");
				return [];
			}

			if (!empty($parameters['date_last_seen_before']) && preg_match('/^\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2}$/', $parameters['date_last_seen_before'])) {
				$find_objects_query .= "
				AND		date_last_seen <= ?";
				$database->AddParam($parameters['date_last_seen_before']);
			}
			else if (isset($parameters['date_last_seen_before'])) {
				$this->error("Invalid date_last_seen_before parameter");
				return [];
			}

			if ($controls['sort_by'] ?? false) {
				$allowed_sort_fields = ['id', 'address', 'size', 'type', 'risk_level', 'managed', 'date_last_seen'];
				if (in_array($controls['sort_by'], $allowed_sort_fields)) {
					$find_objects_query .= "
					ORDER BY ".$controls['sort_by']." ".($controls['sort_dir'] ?? 'ASC');
				}
				else {
					$this->error("Invalid sort_by control");
					return [];
				}
			}
			else {
				$find_objects_query .= "
				ORDER BY address ASC, size ASC, type ASC";
			}

			$rs = $database->Execute($find_objects_query);
			if (! $rs) {
				$this->SQLError($database->ErrorMsg());
				return [];
			}

			$results = [];
			while (list($id) = $rs->FetchRow()) {
				$results[] = new \Network\Subnet($id);
				$this->incrementCount();
			}

			return $results;
		}

		

		/** @method public contains($ip)
		 * Checks if the given IP address is within the subnet.
		 * @param string $ip The IP address to check (can be in standard notation or long format)
		 * @return bool True if the IP is in the subnet, false otherwise
		 */
		public function contains($ip, $type = 'ipv4'): bool {
			// Clear Errors
			$this->clearErrors();

			// Initialize Database Service
			$database = new \Database\Service();

			// Convert IP to long format if necessary
			if (preg_match('/^ipv4$/i', $type)) {
				if (! filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4)) {
					$this->error("Invalid IPv4 address");
					return false;
				}
				$ip = ip2long($ip);
				$type = 'ipv4';
				if ($ip === false) {
					$this->error("Invalid IPv4 address");
					return false;
				}
				else if ($ip < 0) {
					$ip = sprintf("%u", $ip);
				}
				else {
					$ip = (string)$ip;
				}
			}
			elseif (preg_match('/^ipv6$/i', $type)) {
				if (! filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV6)) {
					$this->error("Invalid IPv6 address");
					return false;
				}
				$ip = inet_pton($ip);
				$type = 'ipv6';
				if ($ip === false) {
					$this->error("Invalid IPv6 address");
					return false;
				}
			}
			elseif (preg_match('/^(\d{1,3})\.(\d{1,3})\.(\d{1,3})\.(\d{1,3})$/', $ip)) {
				$ip = ip2long($ip);
				$type = 'ipv4';
				if ($ip === false) {
					$this->error("Invalid IPv4 address");
					return false;
				}
				else if ($ip < 0) {
					$ip = sprintf("%u", $ip);
				}
				else {
					$ip = (string)$ip;
				}
			}
			elseif (preg_match('/^([a-fA-F0-9:]+)$/', $ip)) {
				$ip = inet_pton($ip);
				$type = 'ipv6';
				if ($ip === false) {
					$this->error("Invalid IPv6 address");
					return false;
				}
			}
			elseif (!empty($ip) && is_numeric($ip)) {
				$ip = (string)$ip;
				$type = 'ipv4';
			}
			else {
				$this->error("Invalid IP address format");
				return false;
			}

			// Prepare Query to Check if IP is in Subnet
			$check_ip_query = "
				SELECT	id
				FROM	network_subnets
				WHERE	address <= ?
				AND		address + size > ?
				AND		type = ?
				ORDER BY address DESC, size ASC, managed DESC
			";

			$database->AddParam($ip);
			$database->AddParam($ip);
			$database->AddParam($type);

			$rs = $database->Execute($check_ip_query);
			if (! $rs) {
				$this->SQLError($database->ErrorMsg());
				return false;
			}

			// Get the details now so we don't have to do another query if we find a match
			$this->_matched = null;
			$this->_matches = [];
			while ($found_match = $rs->FetchNextObject(false)) {
				$this->_matches[] = $found_match->id;
				$this->incrementCount();
			}
			if (!empty($this->_matches)) {
				$this->_matched = $this->_matches[0];
				return true;
			}
			else return false;
		}

		public function matched(): ?Subnet {
			if ($this->_matched) {
				$subnet = new \Network\Subnet($this->_matched);
				return $subnet;
			}
			else return null;
		}

		public function matches(): array {
			$subnets = [];
			foreach ($this->_matches as $match_id) {
				$subnet = new \Network\Subnet($match_id);
				if (! $subnet->error()) {
					$subnets[] = $subnet;
				}
			}
			return $subnets;
		}
	}