<?php

	namespace Calendar;

	class API Extends \API {
		public function __construct() {
			$this->_name = 'calendar';
			$this->_version = '0.1.1';
			$this->_release = '2026-01-24';
			$this->_schema = new \Calendar\Schema();
			parent::__construct();
		}

		/** @method public addCalendar()
		 * Add a new calendar
		 */
		public function addCalendar() {
			$calendar = new \Calendar\Calendar();

			if (! $calendar->validName($_REQUEST['name'])) $this->error('Invalid calendar name');
			if (isset($_REQUEST['description']) && ! $calendar->safeString($_REQUEST['description'])) $this->error('Invalid calendar description');
			$parameters = array(
				'name' => $_REQUEST['name'],
				'description' => $_REQUEST['description'] ?? ''
			);
			if (! $calendar->add($parameters)) {
				$this->error('Could not create calendar: '.$calendar->error());
			}

			$response = new \APIResponse();
			$response->success(true);
			$response->AddElement('calendar',$calendar);
			$response->print();
		}

		/** @method public updateCalendar()
		 * Update an existing calendar
		 */
		public function updateCalendar() {
			$calendar = new \Calendar\Calendar();
			if (!empty($_REQUEST['id'])) {
				if (is_numeric($_REQUEST['id'])) {
					$calendar = new \Calendar\Calendar((int)$_REQUEST['id']);
					if (! $calendar->exists()) $this->notFound('Calendar not found');
				}
				else $this->invalidRequest('Invalid calendar ID');
			}
			elseif (!empty($_REQUEST['code'])) {
				if ($calendar->validCode($_REQUEST['code'])) {
					if (! $calendar->get($_REQUEST['code'])) {
						$this->notFound('Calendar not found');
					}
				}
				else {
					$this->invalidRequest('Invalid calendar code');
				}
			}
			else {
				$this->invalidRequest('No calendar identifier provided');
			}

			$parameters = array();
			if (!empty($_REQUEST['name'])) {
				if (! $calendar->validName($_REQUEST['name'])) {
					$this->error('Invalid calendar name');
				}
				$parameters['name'] = $_REQUEST['name'];
			}
			if (isset($_REQUEST['description'])) {
				if (! $calendar->safeString($_REQUEST['description'])) {
					$this->error('Invalid calendar description');
				}
				$parameters['description'] = $_REQUEST['description'];
			}
			if (isset($_REQUEST['code'])) {
				if (! $calendar->validCode($_REQUEST['code'])) {
					$this->error('Invalid calendar code');
				}
				$parameters['code'] = $_REQUEST['code'];
			}
			if (! $calendar->update($parameters)) {
				$this->error('Could not update calendar: '.$calendar->error());
			}

			$response = new \APIResponse();
			$response->success(true);
			$response->AddElement('calendar',$calendar);
			$response->print();
		}

		/** @method public getCalendar()
		 * Get details of a calendar
		 */
		public function getCalendar() {
			$calendar = new \Calendar\Calendar();

			if (!empty($_REQUEST['id'])) {
				if (is_numeric($_REQUEST['id'])) {
					$calendar = new \Calendar\Calendar((int)$_REQUEST['id']);
					if (! $calendar->exists()) $this->notFound('Calendar not found');
				}
				else $this->invalidRequest('Invalid calendar ID');
			}
			elseif (!empty($_REQUEST['code'])) {
				if ($calendar->validCode($_REQUEST['code'])) {
					if (! $calendar->get($_REQUEST['code'])) {
						$this->notFound('Calendar not found');
					}
				}
				else {
					$this->invalidRequest('Invalid calendar code');
				}
			}
			else {
				$this->invalidRequest('No calendar identifier provided');
			}

			$response = new \APIResponse();
			$response->success(true);
			$response->AddElement('calendar',$calendar);
			$response->print();
		}

		/** @method public dropCalendar()
		 * Delete a calendar
		 */
		public function dropCalendar() {
			$calendar = new \Calendar\Calendar();
			if (!empty($_REQUEST['id'])) {
				if (is_numeric($_REQUEST['id'])) {
					$calendar = new \Calendar\Calendar((int)$_REQUEST['id']);
					if (! $calendar->exists()) $this->notFound('Calendar not found');
				}
				else $this->invalidRequest('Invalid calendar ID');
			}
			elseif (!empty($_REQUEST['code'])) {
				if ($calendar->validCode($_REQUEST['code'])) {
					if (! $calendar->get($_REQUEST['code'])) {
						$this->notFound('Calendar not found');
					}
				}
				else {
					$this->invalidRequest('Invalid calendar code');
				}
			}
			else {
				$this->invalidRequest('No calendar identifier provided');
			}
			if (! $calendar->drop()) {
				$this->error('Could not delete calendar: '.$calendar->error());
			}

			$response = new \APIResponse();
			$response->success(true);
			$response->print();
		}

		/** @method public findCalendars()
		 * Find calendars based on parameters
		 */
		public function findCalendars() {
			$calendarList = new \Calendar\CalendarList();

			$parameters = array();
			if (!empty($_REQUEST['name'])) {
				$parameters['name'] = $_REQUEST['name'];
			}
			if (!empty($_REQUEST['owner_id'])) {
				if (is_numeric($_REQUEST['owner_id'])) {
					$owner = new \Register\Customer((int)$_REQUEST['owner_id']);
					if (! $owner->exists()) {
						$this->notFound('Owner not found');
					}
					$parameters['owner_id'] = (int)$_REQUEST['owner_id'];
				}
				else {
					$this->invalidRequest('Invalid owner ID');
				}
			}

			$calendars = $calendarList->find($parameters);

			if ($calendarList->error()) {
				$this->error('Could not find calendars: '.$calendarList->error());
			}
			$response = new \APIResponse();
			$response->success(true);
			$response->AddElement('calendar',$calendars);
			$response->print();
		}

		/** @method public addCalendarEvent()
		 * Add a new calendar event
		 */
		public function addCalendarEvent() {
			$event = new \Calendar\Event();

			if (!empty($_REQUEST['calendar_id'])) {
				$calendar = new \Calendar\Calendar($_REQUEST['calendar_id']);
				if ($calendar->error()) $this->error($calendar->error());
			}
			elseif (!empty($_REQUEST['calendar_code'])) {
				$calendar = new \Calendar\Calendar();
				if (! $calendar->validCode($_REQUEST['calendar_code'])) $this->invalidRequest("Invalid calendar code");
				if (! $calendar->get($_REQUEST['calendar_code'])) $this->notFound('Calendar not found');
			}

			$parameters = [
				'calendar_id'	=> $calendar->id,
				'name'			=> $_REQUEST['name'],
				'description'	=> $_REQUEST['description'],
				'timestamp_start'	=> get_mysql_date($_REQUEST['timestamp_start']),
				'timestamp_end'		=> get_mysql_date($_REQUEST['timestamp_end']),
			];
			$event = $calendar->addEvent($parameters);

			$response = new \APIResponse();
			$response->success(true);
			$response->AddElement('event',$event);
			$response->print();
		}

		/** @method public updateCalendarEvent()
		 * Update an existing calendar event
		 */
		public function updateCalendarEvent() {
			$event = new \Calendar\Event();

			$response = new \APIResponse();
			$response->success(true);
			$response->AddElement('event',$event);
			$response->print();
		}

		/** @method public getCalendarEvent()
		 * Get details of a calendar event
		 */
		public function getCalendarEvent() {
			$event = new \Calendar\Event();

			$response = new \APIResponse();
			$response->success(true);
			$response->AddElement('event',$event);
			$response->print();
		}

		/** @method public dropCalendarEvent()
		 * Delete a calendar event
		 */
		public function dropCalendarEvent() {
			$event = new \Calendar\Event();

			$response = new \APIResponse();
			$response->success(true);
			$response->print();
		}

		/** @method public findCalendarEvents()
		 * Find calendar events based on parameters
		 */
		public function findCalendarEvents() {
			$eventList = new \Calendar\EventList();
			$events = $eventList->findAdvanced(array(),[],array());

			$response = new \APIResponse();
			$response->success(true);
			$response->AddElement('event',$events);
			$response->print();
		}

		/** @method public addCalendarUser()
		 * Add a user to a calendar
		 */
		public function addCalendarUser() {
			$user = new \Register\Person();
			$calendar = new \Calendar\Calendar();

			$response = new \APIResponse();
			$response->success(true);
			$response->AddElement('user',$user);
			$response->print();
		}

		/** @method public dropCalendarUser()
		 * Remove a user from a calendar
		 */
		public function dropCalendarUser() {
			$user = new \Register\Person();
			$calendar = new \Calendar\Calendar();

			$response = new \APIResponse();
			$response->success(true);
			$response->AddElement('user',$user);
			$response->print();
		}

		/** @method public findCalendarUsers()
		 * Get users associated with a calendar
		 */
		public function findCalendarUsers() {
			$user = new \Register\Person();
			$calendar = new \Calendar\Calendar();

			$response = new \APIResponse();
			$response->success(true);
			$response->AddElement('user',$user);
			$response->print();
		}

		/** @method protected _methods
		 * Define available API methods
		 */
		public function _methods() {
			return array(
				'addCalendar' => array(
					'description'	=> 'Add a new calendar',
					'token_required'	=> true,
					'authentication_required' => true,
					'return_element' => 'calendar',
					'return_type' => '\Calendar\Calendar',
					'parameters' => array(
						'name' => array(
							'required' => true,
							'type' => 'string',
							'description' => 'Name of the calendar',
							'validation_method' => 'Calendar::Calendar::validName()'
						),
						'description' => array(
							'required' => false,
							'type' => 'string',
							'description' => 'Description of the calendar',
							'validation_method' => 'Calendar::Calendar::safeString()'
						)
					)
				),
				'updateCalendar' => array(
					'description'	=> 'Update an existing calendar',
					'token_required'	=> true,
					'authentication_required' => true,
					'return_element' => 'calendar',
					'return_type' => '\Calendar\Calendar',
					'parameters' => array(
						'id' => array(
							'required' => true,
							'type' => 'int',
							'description' => 'ID of the calendar to update',
							'requirement_group' => 0
						),
						'code' => array(
							'required' => true,
							'type' => 'string',
							'description' => 'Code of the calendar to update',
							'requirement_group' => 1
						),
						'name' => array(
							'required' => false,
							'type' => 'string',
							'description' => 'New name of the calendar',
							'validation_method' => 'Calendar::validName()'
						),
						'description' => array(
							'required' => false,
							'type' => 'string',
							'description' => 'New description of the calendar',
							'validation_method' => 'Calendar::safeString()'
						)
					)
				),
				'getCalendar' => array(
					'description'	=> 'Get details of a calendar',
					'token_required'	=> false,
					'return_element' => 'calendar',
					'return_type' => '\Calendar\Calendar',
					'parameters' => array(
						'id' => array(
							'required' => true,
							'type' => 'int',
							'description' => 'ID of the calendar to retrieve',
							'requirement_group' => 0
						),
						'code' => array(
							'required' => true,
							'type' => 'string',
							'description' => 'Code of the calendar to retrieve',
							'requirement_group' => 1
						)
					)
				),
				'dropCalendar' => array(
					'description'	=> 'Delete a calendar',
					'token_required'	=> true,
					'authentication_required' => true,
					'return_element' => 'calendar',
					'return_type' => '\Calendar\Calendar',
					'parameters' => array(
						'id' => array(
							'required' => true,
							'type' => 'int',
							'description' => 'ID of the calendar to delete',
							'requirement_group' => 0
						),
						'code' => array(
							'required' => true,
							'type' => 'string',
							'description' => 'Code of the calendar to delete',
							'requirement_group' => 1
						)
					)
				),
				'findCalendars' => array(
					'description'	=> 'Find calendars based on parameters',
					'token_required'	=> false,
					'return_element' => 'calendar',
					'return_type' => '\Calendar\Calendar',
					'parameters' => array(
						'name' => array(
							'required' => false,
							'type' => 'string',
							'description' => 'Name of the calendar to search for'
						),
						'owner_id' => array(
							'required' => false,
							'type' => 'int',
							'description' => 'Owner ID of the calendar to search for'
						)
					)
				),
				'addCalendarEvent' => array(
					'description'	=> 'Add a new calendar event',
					'token_required'	=> true,
					'authentication_required' => true,
					'return_element' => 'calendar_event',
					'return_type' => '\Calendar\Event',
					'parameters' => array(
						'calendar_id' => array(
							'requirement_group' => 0,
							'type' => 'int',
							'description' => 'ID of the calendar to add the event to'
						),
						'calendar_code' => array(
							'requirement_group'	=> 1,
							'description' => 'Unique identifier for calendar',
							'validation_method' => 'Calendar::Calendar::validCode()'
						),
						'name' => array(
							'required' => true,
							'type' => 'string',
							'description' => 'Name of the event'
						),
						'timestamp_start' => array(
							'required' => true,
							'type' => 'int',
							'description' => 'Start timestamp of the event'
						)
					)
				),
				'updateCalendarEvent' => array(
					'description'	=> 'Update an existing calendar event',
					'token_required'	=> true,
					'authentication_required' => true,
					'return_element' => 'calendar_event',
					'return_type' => '\Calendar\Event',
					'parameters' => array(
						'id' => array(
							'required' => true,
							'type' => 'int',
							'description' => 'ID of the event to update',
							'requirement_group' => 0
						),
						'code' => array(
							'required' => true,
							'type' => 'string',
							'description' => 'Code of the event to update',
							'requirement_group' => 1
						),
						'name' => array(
							'required' => false,
							'type' => 'string',
							'description' => 'New name of the event'
						),
						'timestamp_start' => array(
							'required' => false,
							'type' => 'int',
							'description' => 'New start timestamp of the event'
						)
					)
				),
				'getCalendarEvent' => array(
					'description'	=> 'Get details of a calendar event',
					'token_required'	=> false,
					'return_element' => 'calendar_event',
					'return_type' => '\Calendar\Event',
					'parameters' => array(
						'id' => array(
							'required' => true,
							'type' => 'int',
							'description' => 'ID of the event to retrieve',
							'requirement_group' => 0
						),
						'code' => array(
							'required' => true,
							'type' => 'string',
							'description' => 'Code of the event to retrieve',
							'requirement_group' => 1
						)
					)
				),
				'dropCalendarEvent' => array(
					'description'	=> 'Delete a calendar event',
					'token_required'	=> true,
					'authentication_required' => true,
					'parameters' => array(
						'id' => array(
							'required' => true,
							'type' => 'int',
							'description' => 'ID of the event to delete',
							'requirement_group' => 0
						),
						'code' => array(
							'required' => true,
							'type' => 'string',
							'description' => 'Code of the event to delete',
							'requirement_group' => 1
						)
					)
				),
				'findCalendarEvents' => array(
					'description'	=> 'Find calendar events based on parameters',
					'token_required'	=> false,
					'return_element' => 'calendar_event',
					'return_type' => '\Calendar\Event',
					'parameters' => array(
						'id' => array(
							'required' => false,
							'type' => 'int',
							'description' => 'ID of the calendar to search events in'
						),
						'code' => array(
							'required' => false,
							'type' => 'string',
							'description' => 'Code of the calendar to search events in'
						),
						'timestamp_start' => array(
							'required' => false,
							'type' => 'int',
							'description' => 'Start timestamp to search events from'
						),
						'timestamp_end' => array(
							'required' => false,
							'type' => 'int',
							'description' => 'End timestamp to search events to'
						),
						'name' => array(
							'required' => false,
							'type' => 'string',
							'description' => 'Name of the event to search for'
						),
						'user_id' => array(
							'required' => false,
							'type' => 'int',
							'description' => 'User ID associated with the event'
						)
					)
				),
				'addCalendarUser' => array(
					'description'	=> 'Add a user to a calendar',
					'token_required'	=> true,
					'authentication_required' => true,
					'parameters' => array(
						'calendar_id' => array(
							'required' => true,
							'type' => 'int',
							'description' => 'ID of the calendar to add the user to'
						),
						'user_id' => array(
							'required' => true,
							'type' => 'int',
							'description' => 'ID of the user to add to the calendar'
						)
					)
				),
				'dropCalendarUser' => array(
					'description'	=> 'Remove a user from a calendar',
					'token_required'	=> true,
					'authentication_required' => true,
					'parameters' => array(
						'calendar_id' => array(
							'required' => true,
							'type' => 'int',
							'description' => 'ID of the calendar to remove the user from'
						),
						'user_id' => array(
							'required' => true,
							'type' => 'int',
							'description' => 'ID of the user to remove from the calendar'
						)
					)
				),
				'findCalendarUsers' => array(
					'description'	=> 'Get users associated with a calendar',
					'token_required'	=> false,
					'return_element' => 'user',
					'return_type' => '\Register\User',
					'parameters' => array(
						'calendar_id' => array(
							'required' => true,
							'type' => 'int',
							'description' => 'ID of the calendar to get users for'
						)
					)
				)
			);
		}
	}