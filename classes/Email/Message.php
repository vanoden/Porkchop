<?php
	namespace Email;

	class Message {
		private $_error;
		private $_recipients = array();
		private $_from;
		private $_subject;
		private $_body;
		private $_attachments = array();
		private $_html = false;

		public function __construct($argument = null) {
			$schema = new Schema();
			if ($schema->error) {
				$this->_error = $schema->error;
				return null;
			}

			if (gettype($argument) == 'array') {
				if (isset($argument['to'])) $this->add_recipients($argument['recipients']);
				if (isset($argument['from'])) $this->from($argument['from']);
				if (isset($argument['subject'])) $this->subject($argument['subject']);
				if (isset($argument['body'])) $this->body($argument['body']);
				if (isset($argument['html'])) $this->_html = $argument['html'];
			}
			elseif (gettype($argument) == 'object') {
				if (isset($argument->to)) $this->add_recipients($argument->to);
				if (isset($argument->from)) $this->from($argument->from);
				if (isset($argument->subject)) $this->subject($argument->subject);
				if (isset($argument->body)) $this->body($argument->body);
				if (isset($argument->html)) $this->_html = $argument->html;
			}
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

		public function error() {
			return $this->_error;
		}
	}
