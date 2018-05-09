<?
	namespace Email;

	class Message {
		private $_error;
		private $_recipients = array();
		private $_from;
		private $_subject;
		private $_body;
		private $_attachments = array();
		private $_html = false;

		public function __construct($parameters = array()) {
			$schema = new Schema();
			if ($schema->error) {
				$this->_error = $schema->error;
				return null;
			}

			if (isset($parameters['to'])) $this->add_recipients($parameters['recipients']);
			if (isset($parameters['from'])) $this->from($parameters['from']);
			if (isset($parameters['subject'])) $this->subject($parameters['subject']);
			if (isset($parameters['body'])) $this->body($parameters['body']);
		}

		public function html($state = null) {
			if (isset($state)) $this->_html = $state;
			return $this->_html;
		}

		public function to($to = null) {
			if (isset($to)) $this->_recipients = array($to);
			return $this->_recipients[0];
		}

		public function add_recipients($recipients = null) {
			if (isset($recipients)) array_push($this->_recipients,$recipients);
		}

		public function from($from = null) {
			if (isset($from)) $this->_from = $from;
			return $this->_from;
		}

		public function subject($subject = null) {
			if (isset($subject)) $this->_subject = $subject;
			return $this->_subject;
		}

		public function body($body = null) {
			if (isset($body)) $this->_body = $body;
			return $this->_body;
		}
	}
	
?>