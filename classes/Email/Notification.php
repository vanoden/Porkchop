<?php
	namespace Email;

	class Notification {
	    
	    public $templateVars;
	    public $template = TEMPLATES."/support/update_notification.html";
	    public $errors = array();
	    public $sender = 'no-reply@spectrosinstruments.com';
	    public $recipients = array();
	    public $message;
	    private $verifyTemplate;
	    private $transport;

        /**
         * construct new email notification
         * @param $parameters, required values set, 'subject', 'template', 'templateVars', 'customer'
         */
		public function __construct($parameters = array()) {
		
            if (empty($parameters['subject']) || !isset($parameters['subject'])) $this->addError('Email subject is required');
		    $this->templateVars = !empty($parameters['templateVars']) ? $parameters['templateVars'] : array();

        	// overide template if needed
        	if (isset($parameters['template']) && !empty($parameters['template'])) $this->template = $parameters['template'];
        	
            // get the template specified from the file system
        	if (! file_exists($this->template)) $this->addError("Template '".$this->template."' not found", __FILE__,__LINE__);
            $parameters['body'] = "";
        	try {	
        		$parameters['body'] = file_get_contents($this->template);
        	} catch (Exception $e) {
            	$this->addError("Email template load failed: ".$e->getMessage(),__FILE__,__LINE__);
        	}
        	
        	// overide sender if needed
        	if (isset($parameters['sender']) && !empty($parameters['sender'])) $this->sender = $parameters['sender'];
        	
            // get the recipients of the customer that has email contacts set to notify        	
        	$customerHasEmailNotifications = false;
        	if (isset($parameters['customer']) && is_a($parameters['customer'], '\Register\Customer')) {
            	$notifyCustomerEmails = $parameters['customer']->contacts(array('type'=>'email'));
                foreach ($notifyCustomerEmails as $customerEmail) {
                    if ($customerEmail->notify) {
                        $this->recipients[] = $customerEmail->value;
                        $customerHasEmailNotifications = true;
                    }
                }    
        	}
            if (!$customerHasEmailNotifications){
			    $this->addError("Error: Notification can not be processed, customer has <strong>no email address</strong> set to receive notifications.");
            }

		    // build the email message
            $this->message = new \Email\Message($parameters);
            $this->populateTemplateVars(); 
		}
		
		/**
		 * send out all notifications
		 */
		public function notify() {
            foreach ($this->recipients as $recipient) $this->send($recipient, $this->sender);
		}
		
		/**
		 * populate the template given for this email notification 
		 *  with the name value pairs to customize the email content
		 */
		private function populateTemplateVars () {
            $this->verifyTemplate = new \Content\Template\Shell();
            $this->verifyTemplate->content($this->message->body());
            foreach ($this->templateVars as $key => $value) $this->verifyTemplate->addParam($key, $value);
            app_log("Message: ".$this->verifyTemplate->output(),'trace',__FILE__,__LINE__);
		}
		
		/**
		 * send email notification to and from senders
		 * @ param $to
		 * @ param $from
		 */
		public function send($to, $from) {
		
		    // check all params are set
		    if (empty($to)) $this->addError('at least one email recipient is required');
		    if (empty($from)) $this->addError('email from address required');

            if (!empty($this->errors)) {
                $this->addError("Couldn't send email notification, errors are present",__FILE__,__LINE__);
                return false;
            }
            
            // send message
        	$this->message->html(true);
        	$this->message->to($to);
        	$this->message->from($from);
        	$this->message->body($this->verifyTemplate->output());

        	app_log("Sending Email Notification: ".$this->message->subject(), 'debug',__FILE__,__LINE__);
        	$this->transport = \Email\Transport::Create(array('provider' => $GLOBALS['_config']->email->provider));
        	$this->transport->hostname($GLOBALS['_config']->email->hostname);
        	$this->transport->token($GLOBALS['_config']->email->token);
        	$this->transport->deliver($this->message);
        	if ($this->transport->error) {
        		$this->addError("Error sending email notification " . $this->message->subject() . ", please contact us at service@spectrosinstruments.com",__FILE__,__LINE__);
        		return;
        	}
            return true;
		}
		
		/**
		 * get message body of the notification to be sent
		 */
		public function getMessageBody() {
		    return $this->verifyTemplate->output();
		}
		
		/**
		 * add error message to this object to keep track of issues sending email notifications
		 *
		 * @ param string $errorMsg
		 * @ param string $fileName, name of the php file setting this error message
		 * @ param $lineNumber, line number of the file setting this error
		 */
		public function addError($errorMsg = '', $fileName = __FILE__, $lineNumber = __LINE__) {
		    $this->errors[] = $errorMsg;
		    app_log($errorMsg, 'error', $fileName, $lineNumber);		    
		}

		public function error() {
			return join("<br>",$this->errors);
		}
	}
