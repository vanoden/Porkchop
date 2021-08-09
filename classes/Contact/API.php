<?php
	namespace Contact;

	/* Base Class for APIs */
	class API extends \API {

		public function __construct() {
			$this->_name = 'contact';
			$this->_version = '0.1.2';
			$this->_release = '2021-08-08';
			$this->_schema = new Schema();
			$this->_admin_role = 'contact manager';
			parent::__construct();
		}

        ###################################################
        ### Add an Event								###
        ###################################################
        public function addEvent() {
            $event = new \Contact\Event();
            if ($event->error) app_error("Error initializing ContactEvent: ".$event->error);
            
            $parameters = array();
            if ($_REQUEST['status']) $parameters['status'] = $_REQUEST['status'];
            else $_REQUEST['status'] = 'NEW';
            $_REQUEST['content'] = $_REQUEST['content'];

            $event->add($parameters);
            if ($event->error) error("Error adding event: ".$event->error);
            $this->response->success = 1;
            $this->response->event = $event;

			print $this->formatOutput($this->response);
        }

        ###################################################
        ### Find matching Events						###
        ###################################################
        function findEvents() {
            $eventlist = new \Contact\EventList();
            if ($eventlist->error) app_error("Error finding events: ".$eventlist->error,__FILE__,__LINE__);
            
            if (in_array($_REQUEST['status'],array('NEW','OPEN','CLOSED'))) $parameters['status'] = $_REQUEST['status'];
            elseif($_REQUEST['status']) error("Invalid status for events");
            
            $events = $eventlist->find($parameters);
            if ($eventlist->error) app_error("Error finding events: ".$eventlist->error,__FILE__,__LINE__);
            $this->response->success = 1;
            $this->response->event = $events;

			print $this->formatOutput($this->response);
        }

		public function _methods() {
			return array(
				'ping'	=> array(),
				'addEvent'	=> array(
					'status'			=> array('required' => true),
					'content'		=> array('required' => true)
				)
			);
		}
	}
