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
			if (! $transport->deliver($email)) {
				$this->error($transport->error(),__FILE__,__LINE__);
			}
			else {
				$this->response->success = 1;
				$this->response->result = $transport->result;
			}

			print $this->formatOutput($this->response);
		}

		###################################################
		### Get Emails									###
		###################################################
		public function findQueueMessages() {
			$queue = new \Email\Queue();
			$messages = $queue->messages();
			$this->response->success = 1;
			$this->response->message = $messages;
			
			print $this->formatOutput($this->response);
		}

		###################################################
		### Get Next Queued Email						###
		###################################################
		public function nextUnsent() {
			$queue = new \Email\Queue();
			$message = $queue->takeNextUnsent();
			$this->response->success = 1;
			if (is_numeric($message->id) && $message->id > 0) {
				app_log("Returning message ".$message->id,'notice');
				$this->response->message = $message;
			}
			else {
				app_log("No message returned",'notice');
				unset($this->response->message);
			}

			print $this->formatOutput($this->response);
		}

		###################################################
		### Record Delivery Outcome						###
		###################################################
		public function deliveryEvent() {
			$message = new \Email\Queue\Message($_REQUEST['id']);
			if (! $message->id) $this->error("Message not found");
			if ($message->recordEvent(
				$_REQUEST['status'],
				$_REQUEST['code'],
				$_REQUEST['host'],
				$_REQUEST['response']
			)) {
				$this->response->success = 1;
			}
			else {
				$this->response->success = 0;
				$this->response->error = $message->error();
			}
			print $this->formatOutput($this->response);
		}

		public function _methods() {
			return array(
				'sendEmail'			=> array(
					'to'	=> array('required' => true),
					'from'	=> array('required' => true),
					'subject'	=> array(),
					'body'		=> array()
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
