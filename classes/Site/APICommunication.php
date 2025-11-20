<?php
	namespace Site;

	class APICommunication Extends \BaseModel{
		// Implementation of APICommunication
		public $session;
		private $session_id;
		public $url;
		public $method;
		public $request;
		public $response;
		public $result;
		public $timestamp;
		public $request_json;
		public $response_json;

		public function __construct($session_id = null) {
			$this->_tableName = 'monitor_communications';
			$this->_auditEvents = false;
			if (is_numeric($session_id)) {
				$this->session_id = $session_id;
				$this->details();
			}
			else {
				$this->session = $GLOBALS['_SESSION_'] ?? null;
			}
			parent::__construct();
		}

		public function add($parameters = []) {
			$this->clearError();

			if (!$this->session || !isset($this->session->id)) {
				$this->error("No valid session available");
				return null;
			}

			list($request,$response) = $parameters;
			$database = new \Database\Service();

			$add_event_query = "
				INSERT INTO monitor_communications
				(	session_id,
					request,
					response,
					`timestamp`
				)
				VALUES
				(	?,
					?,
					?,
					unix_timestamp(sysdate())
				)
				ON DUPLICATE KEY UPDATE
					request = ?,
					response = ?,
					`timestamp` = unix_timestamp(sysdate()
				)
			";
			$database->AddParam($this->session->id);
			$database->AddParam($request);
			$database->AddParam($response);
			$database->AddParam($request);
			$database->AddParam($response);
			$database->Execute($add_event_query);
			if ($database->ErrorMsg()) {
				$this->SQLError($database->ErrorMsg());
				return null;
			}

			return 1;
		}

		public function update($response = []): bool {
			if (!$this->session || !isset($this->session->id)) {
				$this->error("No valid session available");
				return false;
			}

			$database = new \Database\Service();

			$update_event_query = "
				UPDATE	monitor_communications
				SET		response = ?
				WHERE	session_id = ?
			";
			$database->Execute(
				$update_event_query,
				array(
					$response,
					$this->session->id
				)
			);
			if ($database->ErrorMsg()) {
				$this->SQLError($database->ErrorMsg());
				return false;
			}

			return true;
		}

		public function details(): bool {
			$database = new \Database\Service();

			$get_event_query = "
				SELECT	*
				FROM	monitor_communications
				WHERE	session_id = ?
			";
			$rs = $database->Execute(
				$get_event_query,
				array($this->session_id)
			);
			if (! $rs) {
				$this->SQLError($database->ErrorMsg());
				return false;
			}
			$object = $rs->FetchNextObject(false);
			if (!$object) {
				$this->error("No communication record found for session ID: " . $this->session_id);
				return false;
			}
			
			$this->session = new \Site\Session($object->session_id);
			$this->timestamp = $object->timestamp;
			$this->request_json = prettyPrint($object->request);
			$this->request = json_decode($object->request);
			
			// Check if JSON decode was successful for request
			if (json_last_error() !== JSON_ERROR_NONE) {
				$this->error("Failed to decode request JSON: " . json_last_error_msg());
				$this->request = null;
			}
			
			if ($object->response == "[PENDING]") {
				$this->result = 'Incomplete';
				return true;
			}
			
			// Safely access method parameter
			if ($this->request && isset($this->request->parameters) && isset($this->request->parameters->method)) {
				$this->method = $this->request->parameters->method;
			} else {
				$this->method = null;
			}
			
			$this->response_json = prettyPrint($object->response);
			$this->response = json_decode($object->response);
			
			// Check if JSON decode was successful for response
			if (json_last_error() !== JSON_ERROR_NONE) {
				$this->error("Failed to decode response JSON: " . json_last_error_msg());
				$this->response = null;
			}
			
			// Safely check response success
			if ($this->response && isset($this->response->success)) {
				if ($this->response->success == '1') {
					$this->result = 'Success';
				} elseif ($this->response->success == '0') {
					$this->result = "Error";
				} else {
					$this->result = "Unknown";
				}
			} else {
				$this->result = "Unknown";
			}
			
			return true;
		}

		public function session() {
			return new \Site\Session($this->session_id);
		}

		public function response() {
			return new \APIResponse();
		}

		public function APIResponse() {
			$response = new \APIResponse();

			if ($this->session && isset($this->session->customer) && isset($this->session->customer->id) && is_numeric($this->session->customer->id)) {
				$customerObj = new \Register\Customer($this->session->customer->id);
				$customer = new \stdClass();
				$customer->code = $customerObj->code;
				$customer->id = $customerObj->id;
				$response->addElement('customer',$customer);
			}
			
			$request = new \stdClass();
			
			// Safely access request properties
			if ($this->request) {
				$request->module = $this->request->module ?? null;
				$request->view = $this->request->view ?? null;
				$request->client_ip = $this->request->client_ip ?? null;
				$request->user_agent = $this->request->user_agent ?? null;
				$request->timer = $this->request->timer ?? null;
				$request->method = $this->request->method ?? null;
				
				if (!empty($this->request->post)) {
					$req = $this->request->post;
					$request->http_method = 'post';
				} else {
					$req = $this->request->get ?? null;
					$request->http_method = 'get';
				}
			} else {
				$request->module = null;
				$request->view = null;
				$request->client_ip = null;
				$request->user_agent = null;
				$request->timer = null;
				$request->method = null;
				$req = null;
				$request->http_method = 'get';
			}
			
			$request->result = $this->result;
			$request->timestamp = $this->timestamp;
			$request->parameters = $req;
			$request->json = $this->request_json;
			$response->addElement('request',$request);

			$resp = new \stdClass();
			$resp = $this->response();
			$response->addElement('response',$resp);

			return $response;
		}
	}