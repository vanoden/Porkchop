<?php
	namespace S4Engine;

	/**
	 * Client Instance List
	 */
	class ClientList Extends \BaseListClass {
		public $list = [];

		public function addInstance($params = []): \S4Engine\Client {
			$client = new \S4Engine\Client();
			app_log("Add a client instance",'info');
			$client->add(array(
				'serial_number' => $params['serial_number'],
				'model_number' => $params['model_number']
			));
			return $client;
		}

		public function find($params = []): array {
			$this->clearError();
			$this->resetCount();

			// Initialize array to hold results
			$array = [];

			if (!empty($params['code']) && is_array($params['code'])) {
				// Code provided as 2 byte array
				$client_number = $params['code'][0] * 256 + $params['code'][1];
				$client = new \S4Engine\Client();
				if ($client->load($client_number)) {
					$array[] = $client;
					$this->incrementCount();
				}
			}
			elseif (!empty($params['code'])) {
				// Code provided as 2 character string
				foreach ($this->list as $client) {
					if ($client->codeString() == $params['code']) {
						$array[] = $client;
						$this->incrementCount();
					}
				}
			}
			return $array;
		}
	}