<?php

	namespace S4Engine;

	/**
	 * Session List
	 */
	class SessionList Extends \BaseListClass {
		public $list = [];			// List of Sessions keyed on ID

		/**
		 * Create a new session and add to the list
		 * @param array $session 
		 * @return Session 
		 */
		public function addInstance($params = []): ?\S4Engine\Session {
			$this->clearError();

			$session = new \S4Engine\Session();
			$session->_startTime = time();
			$session->_endTime = time();

			if (!empty($params["client"])) {
				print "Adding session for client ".$params['client']->id()."\n";
				$session->client($params["client"]);
				$client = $params["client"];
			}
			elseif (!empty($params["client_id"])) {
				$client = new \S4Engine\Client();
				$client->id($params["client_id"]);
				$session->client($client);
			}
			else {
				$this->error("Client ID required to create session");
				print_r($params);
				return null;
			}

			$session->add(array('client_id' => $client->id()));
			app_log("Added session ".$session->id()." with client ".$client->id()." to list",'info');
			print_r("We now have ".count($this->list)." sessions\n");
			return $session;
		}

		/**
		 * Get an array of sessions matching specified parameters
		 * @param array $params 
		 * @return array 
		 */
		public function find($params = []) {
			$this->clearError();
			$this->resetCount();

			print "Looking in list of ".count($this->list)." sessions\n";
			$results = [];
			if (isset($params["key"])) {
				foreach ($this->list as $session) {
					if ($session->keyString() == $params["key"]) {
						array_push($results,$session);
						$this->incrementCount();
					}
				}
			}
			elseif (isset($params["client_code"]) && isset($params["code"])) {
				foreach ($this->list as $session) {
					print_r("Checking session ".$session->keyHex()."\n");
					if ($session->client()->codeArray() == $params["client_code"] && $session->codeArray() == $params["code"]) {
						array_push($results,$session);
						$this->incrementCount();
					}
				}
			}
			return $results;
		}
	}