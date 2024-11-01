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
			if (!$this->validCSRFToken()) $this->error("Invalid Request");

            $event = new \Contact\Event();
            if ($event->error()) app_error("Error initializing ContactEvent: ".$event->error());
            
            $parameters = array();
            if ($_REQUEST['status']) $parameters['status'] = $_REQUEST['status'];
            else $_REQUEST['status'] = 'NEW';
            $_REQUEST['content'] = $_REQUEST['content'];

            $event->add($parameters);
            if ($event->error()) error("Error adding event: ".$event->error());

			$response = new \APIResponse();
            $response->AddElement('event',$event);
			$response->print();
        }

        ###################################################
        ### Find matching Events						###
        ###################################################
        function findEvents() {
            $eventlist = new \Contact\EventList();
            if ($eventlist->error()) app_error("Error finding events: ".$eventlist->error());
            
			$parameters = [];
			if (!empty($_REQUEST['status'])) $parameters['status'] = $_REQUEST['status'];

            $events = $eventlist->find($parameters);
            if ($eventlist->error()) app_error("Error finding events: ".$eventlist->error(),__FILE__,__LINE__);

			$response = new \APIResponse();
            $response->AddElement('event',$events);
			$response->print();
        }

		public function _methods() {
			return array(
				'ping'	=> array(),
				'addEvent'	=> array(
					'description'	=> 'Add a contact event',
					'privilege_required'	=> 'manage contact events',
					'parameters'	=> array(
						'status'		=> array(
							'required' => true,
							'validation_method'	=> 'Contact::Event::validStatus()'
						),
						'content'		=> array(
							'required' => true,
							'validation_method'	=> 'Contact::Event::safeString()'
						)
					)
				),
				'findEvents'	=> array(
					'description'	=> 'Find events matching criteria',
					'privilege_required'	=> 'browse contact events',
					'parameters'	=> array(
						'status'	=> array(
							'validation_method'	=> 'Contact::Event::validStatus()'
						)
					)
				)
			);
		}
	}
