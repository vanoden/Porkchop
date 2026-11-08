<?php
	namespace Email;

	/* Base Class for APIs */
	class API extends \API {

		public function __construct() {
			$this->_admin_role = 'email manager';
			$this->_name = 'email';
			$this->_version = '0.3.0';
			$this->_release = '2026-11-07';
			$this->_schema = new \Email\Schema();
			parent::__construct();
		}

		###################################################
		### Send Email									###
		###################################################
		public function sendEmail() {
			if (!$this->validCSRFToken()) $this->error("Invalid Request");

			$parameters = array();
			if ($_REQUEST['to']) $parameters['to'] = $_REQUEST['to'];
			if ($_REQUEST['from']) $parameters['from'] = $_REQUEST['from'];
			if ($_REQUEST['body']) $parameters['body'] = $_REQUEST['body'];
			if ($_REQUEST['subject']) $parameters['subject'] = $_REQUEST['subject'];

			$email = new \Email\Message();
			$email->to($_REQUEST['to']);
			$email->from($_REQUEST['from']);
			$email->subject($_REQUEST['subject']);
			$email->body($_REQUEST['body']);
			
			$transport = (new \Email\Transport)->Create(array("provider" => $GLOBALS['_config']->email->provider));
			if (empty($transport)) $this->app_error("Invalid transport");
			if (isset($GLOBALS['_config']->email->hostname)) $transport->hostname($GLOBALS['_config']->email->hostname);
			if (isset($GLOBALS['_config']->email->username)) $transport->username($GLOBALS['_config']->email->username);
			if (isset($GLOBALS['_config']->email->password)) $transport->password($GLOBALS['_config']->email->password);
			if (isset($GLOBALS['_config']->email->token)) $transport->token($GLOBALS['_config']->email->token);
			if (! $transport->deliver($email)) $this->error("Transport error: ".$transport->error(),__FILE__,__LINE__);

			$response = new \APIResponse();
			$response->AddElement('result',$transport->result());
			$response->print();
		}

		###################################################
		### Get Emails									###
		###################################################
		public function findQueueMessages() {
			$queue = new \Email\Queue();
			$messages = $queue->messages();

			$response = new \APIResponse();
			$response->success = 1;
			$response->addElement('message',$messages);
			$response->print();
		}

		###################################################
		### Get Next Queued Email						###
		###################################################
		public function nextUnsent() {
			$queue = new \Email\Queue();
			$message = $queue->takeNextUnsent();

			$response = new \APIResponse();

			if (is_numeric($message->id) && $message->id > 0) {
				app_log("Returning message ".$message->id,'notice');
				$response->addElement('message',$message);
				$response->print();
			}
			else {
				app_log("No message returned",'notice');
				$response->success(false);
				$response->error("No message found");
				$response->print();
			}
		}

		###################################################
		### Record Delivery Outcome						###
		###################################################
		public function deliveryEvent() {
			$response = new \APIResponse();

			$message = new \Email\Queue\Message($_REQUEST['id']);
			if (! $message->id) $this->error("Message not found");
			if ($message->recordEvent(
				$_REQUEST['status'],
				$_REQUEST['code'],
				$_REQUEST['host'],
				$_REQUEST['response']
			)) {
				$response->success(true);
			}
			else {
				$response->success(false);
				$response->error($message->error());
			}
			$response->print();
		}

		###################################################
		### Queue a message and its deliveries			###
		###################################################
		public function queueMessage() {
			$this->requireQueueAccess();

			$from = $_REQUEST['from_address'] ?? ($_REQUEST['from'] ?? '');
			$subject = $_REQUEST['subject'] ?? '';
			$content = $_REQUEST['content'] ?? ($_REQUEST['body'] ?? '');
			$mime = $_REQUEST['mime_type'] ?? '';
			if ($mime === '' && !empty($_REQUEST['html']) && \Email\MailQueue\Record::truthy($_REQUEST['html'])) {
				$mime = 'text/html';
			}

			$userId = 0;
			if (!empty($GLOBALS['_SESSION_']->customer->id)) {
				$userId = (int)$GLOBALS['_SESSION_']->customer->id;
			}
			if (!empty($_REQUEST['user_code'])) {
				$person = new \Register\Person();
				if (!$person->get($_REQUEST['user_code'])) $this->error("User not found");
				$userId = (int)$person->id;
			}

			$deliveries = array();
			foreach (array('TO' => 'to', 'CC' => 'cc', 'BCC' => 'bcc') as $type => $field) {
				if (!isset($_REQUEST[$field]) || $_REQUEST[$field] === '' || $_REQUEST[$field] === array()) continue;
				$values = is_array($_REQUEST[$field]) ? $_REQUEST[$field] : preg_split('/\s*,\s*/', (string)$_REQUEST[$field]);
				foreach ($values as $value) {
					$parsed = \Email\MailQueue\Message::parseAddress($value, $type);
					if ($parsed) $deliveries[] = $parsed;
				}
			}

			$message = new \Email\MailQueue\Message();
			if (!$message->enqueue(array(
				'user_id' => $userId,
				'from_address' => $from,
				'subject' => $subject,
				'mime_type' => $mime,
				'content' => $content,
				'deliveries' => $deliveries,
			))) {
				$this->queueFailure($message->error());
			}

			$response = new \APIResponse();
			$response->success(true);
			$response->addElement('message', $message->apiRecord());
			$response->print();
		}

		###################################################
		### Find queued messages						###
		###################################################
		public function findQueuedMessages() {
			$this->requireQueueAccess();

			$parameters = $this->requestParams(array(
				'user_code',
				'from_address',
				'date_attempt_start',
				'date_attempt_end',
				'date_queued_start',
				'date_queued_end',
				'mime_type',
				'count_attempt_more',
				'count_attempt_less',
			));

			$queue = new \Email\MailQueue\Message();
			$messages = $queue->find($parameters);
			if ($messages === null) $this->error($queue->error() ?: "Unable to find messages");

			$records = array();
			foreach ($messages as $message) $records[] = $message->apiRecord();

			$response = new \APIResponse();
			$response->success(true);
			$response->addElement('message', $records);
			$response->print();
		}

		###################################################
		### Find message deliveries						###
		###################################################
		public function findMessageDeliveries() {
			$this->requireQueueAccess();

			$parameters = $this->requestParams(array(
				'message_code',
				'status',
				'from_address',
				'to_address',
				'date_queued_start',
				'date_queued_end',
				'date_queue_end',
				'last_attempt_start',
				'last_attempt_end',
			));
			if (empty($parameters['date_queued_end']) && !empty($parameters['date_queue_end'])) {
				$parameters['date_queued_end'] = $parameters['date_queue_end'];
			}

			$queue = new \Email\MailQueue\Delivery();
			$deliveries = $queue->find($parameters);
			if ($deliveries === null) $this->error($queue->error() ?: "Unable to find deliveries");

			$records = array();
			foreach ($deliveries as $delivery) $records[] = $delivery->apiRecord();

			$response = new \APIResponse();
			$response->success(true);
			$response->addElement('delivery', $records);
			$response->print();
		}

		###################################################
		### Record a delivery attempt					###
		###################################################
		public function addDeliveryAttempt() {
			$this->requireQueueAccess();

			$delivery = new \Email\MailQueue\Delivery();
			if (!$delivery->get($_REQUEST['delivery_code'] ?? '')) {
				$this->queueFailure($delivery->error() ?: "Delivery not found");
			}

			$recorded = $delivery->recordAttempt(array(
				'date_attempt' => $_REQUEST['date_attempt'] ?? '',
				'server_response' => $_REQUEST['server_response'] ?? '',
				'response_code' => $_REQUEST['response_code'] ?? '',
				'success' => $_REQUEST['success'] ?? false,
			));

			$response = new \APIResponse();
			if ($recorded) {
				$response->success(true);
			}
			else {
				$response->success(false);
				$response->addElement('message', $this->publicQueueError($delivery->error()));
			}
			$response->print();
		}

		protected function requireQueueAccess() {
			$this->requireAuth();
			$this->requireRole('email manager');
		}

		protected function requestParams(array $names) {
			$parameters = array();
			foreach ($names as $name) {
				if (!array_key_exists($name, $_REQUEST)) continue;
				$value = $_REQUEST[$name];
				if (is_array($value)) {
					if (count($value) < 1) continue;
					$parameters[$name] = $value;
					continue;
				}
				if ($value === null || $value === '') continue;
				$parameters[$name] = $value;
			}
			return $parameters;
		}

		protected function queueFailure($message) {
			$response = new \APIResponse();
			$response->success(false);
			$response->addElement('message', $this->publicQueueError($message));
			$response->print();
			exit;
		}

		protected function publicQueueError($message) {
			$message = (string)$message;
			if ($message === '') return "Request failed";
			if (preg_match('/SQL\sError/', $message)) return "Unable to update the mail queue";
			return $message;
		}

		public function _methods() {
			return array(
				'sendEmail'			=> array(
					'description'	=> 'Send an email',
					'privilege_required'	=> 'send email',
					'parameters'	=> array(
						'to'		=> array('required' => true),
						'from'		=> array('required' => true),
						'subject'	=> array(),
						'body'		=> array()
					)
				),
				'findQueueMessages'	=> array(),
				'nextUnsent'	=> array(),
				'deliveryEvent'	=> array(
					'status'	=> array(),
					'code'		=> array(),
					'host'		=> array(),
					'response'	=> array(),
				),
				'queueMessage'	=> array(
					'description'	=> 'Queue an email for later delivery',
					'authentication_required'	=> true,
					'parameters'	=> array(
						'from_address'	=> array(
							'required' => true,
							'content-type' => 'email',
						),
						'to'		=> array('required' => true),
						'cc'		=> array(),
						'bcc'		=> array(),
						'subject'	=> array('required' => true),
						'content'	=> array(),
						'mime_type'	=> array(),
						'user_code'	=> array(),
					),
				),
				'findQueuedMessages'	=> array(
					'description'	=> 'Find queued email messages',
					'authentication_required'	=> true,
					'return_element'	=> 'message',
					'parameters'	=> array(
						'user_code'		=> array(),
						'from_address'	=> array('content-type' => 'email'),
						'date_attempt_start'	=> array('content-type' => 'datetime'),
						'date_attempt_end'		=> array('content-type' => 'datetime'),
						'date_queued_start'		=> array('content-type' => 'datetime'),
						'date_queued_end'		=> array('content-type' => 'datetime'),
						'mime_type'		=> array(),
						'count_attempt_more'	=> array('content-type' => 'integer'),
						'count_attempt_less'	=> array('content-type' => 'integer'),
					),
				),
				'findMessageDeliveries'	=> array(
					'description'	=> 'Find email deliveries for the queue processor',
					'authentication_required'	=> true,
					'return_element'	=> 'delivery',
					'parameters'	=> array(
						'message_code'	=> array(),
						'status'		=> array(),
						'from_address'	=> array('content-type' => 'email'),
						'to_address'	=> array('content-type' => 'email'),
						'date_queued_start'	=> array('content-type' => 'datetime'),
						'date_queue_end'	=> array('content-type' => 'datetime'),
						'last_attempt_start'	=> array('content-type' => 'datetime'),
						'last_attempt_end'		=> array('content-type' => 'datetime'),
					),
				),
				'addDeliveryAttempt'	=> array(
					'description'	=> 'Record the result of a delivery attempt',
					'authentication_required'	=> true,
					'parameters'	=> array(
						'delivery_code'	=> array('required' => true),
						'date_attempt'	=> array('content-type' => 'datetime'),
						'server_response'	=> array(),
						'response_code'	=> array(),
						'success'		=> array(),
					),
				),
			);
		}
	}
