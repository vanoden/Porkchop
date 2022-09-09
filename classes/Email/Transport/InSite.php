<?php
	namespace Email\Transport;

	class InSite extends \Base {
	
		private $_error;
		private $_recipients = array();
		private $_from;
		private $_subject;
		private $_content;

		public function __construct($argument = null) {
			if (gettype($argument) == 'array') {
				if (isset($argument['to'])) $this->add_recipients($argument['recipients']);
				if (isset($argument['from'])) $this->from($argument['from']);
				if (isset($argument['subject'])) $this->subject($argument['subject']);
				if (isset($argument['content'])) $this->content($argument['content']);
			} elseif (gettype($argument) == 'object') {
				if (isset($argument->to)) $this->add_recipients($argument->to);
				if (isset($argument->from)) $this->from($argument->from);
				if (isset($argument->subject)) $this->subject($argument->subject);
				if (isset($argument->content)) $this->content($argument->content);
			}
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

		public function content($content = null) {
			if (isset($content)) $this->_content = $content;
			return $this->_content;
		}

		public function error() {
			return $this->_error;
		}
		
		public function send() {
		
    		if (isset($this->_recipients) && !empty($this->_recipients)) {
    		
    		    if (isset($this->_from) && !empty($this->_from)) {
        		    $this->_error = "No from user ID is set for InSite Message.";
        		    return false;
    		    }
    		    
    		    if (isset($this->_subject) && !empty($this->_subject)) {
        		    $this->_error = "No subject is set for InSite Message.";
        		    return false;
    		    }
    		    
    		    if (isset($this->_content) && !empty($this->_content)) {
        		    $this->_error = "No content is set for InSite Message.";
        		    return false;
    		    }

    		    foreach ($this->_recipients as $recipient) {
    		        $siteMessage = new \Site\SiteMessage();    
    		        $siteMessage->add(array('user_created' => $this->_from, 'recipient_id' => $recipient, 'subject' => $this->_subject, 'content' => $this->_content));
    		    }
    		}
		}
		
	}
