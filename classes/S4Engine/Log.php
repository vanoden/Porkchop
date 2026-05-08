<?php
	namespace S4Engine;

	class Log Extends \BaseListClass {
		public $module = "s4engine";

		public function __construct($parameters = array()) {
			$this->_modelName = "\S4Engine\LogRecord";
		}

		public function findAdvanced($parameters, $advanced, $controls): array {
			$this->clearError();
			$this->resetCount();

			// Initialize Database Service
			$database = new \Database\Service();

			// Build Query
			$find_objects_query = "
				SELECT	id
				FROM	s4engine_log
				WHERE	id = id
			";

			// Add Parameters
			$validationClass = new $this->_modelName();

			if (isset($parameters['time_created_start']) && preg_match('/^\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2}$/',$parameters['time_created_start'])) {
				$find_objects_query .= "
				AND		time_created >= ?";
				$database->AddParam($parameters['time_created_start']);
			}
			if (isset($parameters['time_created_end']) && preg_match('/^\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2}$/',$parameters['time_created_end'])) {
				$find_objects_query .= "
				AND		time_created <= ?";
				$database->AddParam($parameters['time_created_end']);
			}

			if (isset($parameters['function_byte']) && is_numeric($parameters['function_byte']) && $parameters['function_byte'] >= 0 && $parameters['function_byte'] <= 255) {
				$find_objects_query .= "
				AND		function_id LIKE ?";
				$database->AddParam(chr($parameters['function_byte']).'%');
			}
			if (isset($parameters['client_byte']) && is_numeric($parameters['client_byte']) && $parameters['client_byte'] >= 0 && $parameters['client_byte'] <= 255) {
				$find_objects_query .= "
				AND		client_id LIKE ?";
				$database->AddParam(chr($parameters['client_byte']).'%');
			}
			if (isset($parameters['server_byte']) && is_numeric($parameters['server_byte']) && $parameters['server_byte'] >= 0 && $parameters['server_byte'] <= 255) {
				$find_objects_query .= "
				AND		server_id LIKE ?";
				$database->AddParam(chr($parameters['server_byte']).'%');
			}
			if (isset($parameters['session_code']) && preg_match('/^[0-9A-Fa-f]{16,16}$/',$parameters['session_code'])) {
				$find_objects_query .= "
				AND		session_code = ?";
				$database->AddParam(pack('H*',$parameters['session_code']));
			}

			// Sort Controls
			if (isset($controls['sort']) && in_array($controls['sort'], $validationClass->_fields())) {
				$find_objects_query .= "
					ORDER BY ".$controls['sort'];
				if (isset($controls['order']) && strtolower($controls['order']) == "desc") {
					$find_objects_query .= " DESC";
				} else {
					$find_objects_query .= " ASC";
				}
			} else {
				$find_objects_query .= "
					ORDER BY time_created DESC";
			}

			// Handle Controls
			if (isset($controls['limit']) && is_numeric($controls['limit'])) {
				$find_objects_query .= "
					LIMIT ".intval($controls['limit'])."
				";
			}

			// Execute Query
			$rs = $database->Execute($find_objects_query);
			if (! $rs) {
				$this->SQLError($database->ErrorMsg());
				return [];
			}

			// Fetch Results
			$results = [];
			while ($result = $rs->FetchRow()) {
				$id = $result['id'];
				$logRecord = new LogRecord($id);
				$results[] = $logRecord;
				$this->incrementCount();
			}

			return $results;
		}
	}