<?php
	/** @class Network\RDAP
	 * @description Class for performing RDAP lookups.
	 */
	namespace Network;

	class RDAP Extends \BaseClass {
		public $result = null;

		public function lookup($address, $type) {
			// Validate input
			if (empty($address) || empty($type)) {
				$this->error("Address and Type are required for RDAP lookup");
				return false;
			}

			// Perform RDAP lookup using external library or API
			// This is a placeholder implementation and should be replaced with actual RDAP lookup logic
			print_r("Performing RDAP lookup for address: $address, type: $type");
			$result = $this->performRDAPLookup($address, $type);
			if ($result === false) {
				print_r("Nope");
				$this->error("RDAP lookup failed for address: $address, type: $type");
				return false;
			}
			//print_r("Result: ");
			//print_r($result);
			return $result;
		}

		private function performRDAPLookup($address, $type) {
			$ch = curl_init();

			// Set the URL and other options for the cURL request
			curl_setopt($ch, CURLOPT_URL, "https://rdap.org/registry/ip/$address");
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
			$response = curl_exec($ch);
			print_r("cURL Response: ");
			print_r($response);
			if ($response === false) {
				return false;
			}
			curl_close($ch);
			$this->result = json_decode($response, true);
			print_r($this->result);
			return $this->result;
		}

		public function startAddress() {
			if (empty($this->result)) {
				$this->error("No RDAP result available");
				return null;
			}
			if (empty($this->result['startAddress'])) {
				$this->error("startAddress not found in RDAP result");
				return null;
			}
			return $this->result['startAddress'] ?? null;
		}

		public function endAddress() {
			if (empty($this->result)) {
				$this->error("No RDAP result available");
				return null;
			}
			if (empty($this->result['endAddress'])) {
				$this->error("endAddress not found in RDAP result");
				return null;
			}
			return $this->result['endAddress'] ?? null;
		}

		public function name() {
			if (empty($this->result)) {
				$this->error("No RDAP result available");
				return null;
			}
			if (empty($this->result['name'])) {
				$this->error("name not found in RDAP result");
				return null;
			}
			return $this->result['name'] ?? null;
		}

		public function handle() {
			if (empty($this->result)) {
				$this->error("No RDAP result available");
				return null;
			}
			if (empty($this->result['handle'])) {
				$this->error("handle not found in RDAP result");
				return null;
			}
			return $this->result['handle'] ?? null;
		}

		public function type() {
			if (empty($this->result)) {
				$this->error("No RDAP result available");
				return null;
			}
			if (empty($this->result['type'])) {
				$this->error("type not found in RDAP result");
				return null;
			}
			return $this->result['type'] ?? null;
		}

		public function summary() {
			if (empty($this->result)) {
				$this->error("No RDAP result available");
				return null;
			}
			$summary = [];
			if (!empty($this->result['name'])) {
				$summary["name"] = $this->result['name'];
			}
			if (!empty($this->result['handle'])) {
				$summary["handle"] = $this->result['handle'];
			}
			if (!empty($this->result['type'])) {
				$summary["type"] = $this->result['type'];
			}
			if (!empty($this->result['startAddress'])) {
				$summary["startAddress"] = $this->result['startAddress'];
			}
			if (!empty($this->result['endAddress'])) {
				$summary["endAddress"] = $this->result['endAddress'];
			}
			if (!empty($this->result['entities'])) {
				$summary["entity"]['name'] = $this->result['entities'][0]['vCardArray'][1][1][3] ?? null;
				$summary["entity"]['address'] = $this->result['entities'][0]['vCardArray'][1][2][1] ?? null;
			}
			return $summary;
		}
	}
