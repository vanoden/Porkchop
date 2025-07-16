<?php
	namespace Email;

	/* Base Class for APIs */
	class API extends \API {

		public function __construct() {
			$this->_admin_role = 'email manager';
			$this->_name = 'email';
			$this->_version = '0.1.1';
			$this->_release = '2021-08-13';
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

		public function _methods() {
			return array(
				'ping'	=> array(),
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
				)
			);
		}
	}
