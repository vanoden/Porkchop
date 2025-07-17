<?php
	namespace Email;

	class Message Extends \BaseClass {
		private $_recipients = array();		// Array of recipients
		private $_from;						// Sender email address
		private $_subject;					// Subject of the email
		private $_body;						// Body of the email
		private $_attachments = array();	// Array of attachments
		private $_html = false;				// HTML state

		/** @constructor */
		public function __construct($argument = null) {
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

		/** @method public html(bool)
		 * Sets or gets the HTML state of the email message.
		 * @param bool $state If provided, sets the HTML state.
		 * @return bool Returns the current HTML state if no parameter is provided, otherwise returns void
		 */
		public function html($state = null) {
			if (isset($state)) $this->_html = $state;
			return $this->_html;
		}

		/** @method public to(string)
		 * Sets or gets the recipient of the email message.
		 * @param string $to If provided, sets the recipient.
		 * @return string Returns the current recipient if no parameter is provided, otherwise returns void
		 */
		public function to($to = null) {
			if (isset($to)) $this->_recipients = array($to);
			return $this->_recipients[0];
		}

		/** @method public add_recipients(array)
		 * Adds recipients to the email message.
		 * @param array $recipients An array of recipient email addresses.
		 */
		public function add_recipients($recipients = null) {
			if (isset($recipients)) array_push($this->_recipients,$recipients);
		}

		/** @method public recipients()
		 * Returns the array of recipients for the email message.
		 * @return array Returns the array of recipients.
		 */
		public function recipients() {
			return $this->_recipients;
		}

		/** @method public from(string)
		 * Sets or gets the sender of the email message.
		 * @param string $from If provided, sets the sender.
		 * @return string Returns the current sender if no parameter is provided, otherwise returns void
		 */
		public function from($from = null) {
			if (isset($from)) $this->_from = $from;
			return $this->_from;
		}

		/** @method public subject(string)
		 * Sets or gets the subject of the email message.
		 * @param string $subject If provided, sets the subject.
		 * @return string Returns the current subject if no parameter is provided, otherwise returns void
		 */
		public function subject($subject = null) {
			if (isset($subject)) $this->_subject = $subject;
			return $this->_subject;
		}

		/** @method public body(string)
		 * Sets or gets the body of the email message.
		 * @param string $body If provided, sets the body.
		 * @return string Returns the current body if no parameter is provided, otherwise returns void
		 */
		public function body($body = null) {
			if (isset($body)) $this->_body = $body;
			return $this->_body;
		}

		/** @method public summary()
		 * Returns a summary of the email message.
		 * @return string Returns a summary string containing all parts of the object.
		 */
		public function summary() {
			$summary = "From: ".$this->from()."\n";
			$summary .= "To: ".implode(", ", $this->recipients())."\n";
			$summary .= "Subject: ".$this->subject()."\n";
			$summary .= "Body: ".$this->body()."\n";
			$summary .= "HTML: ".($this->html() ? 'Yes' : 'No')."\n";
			return $summary;
		}
	}
