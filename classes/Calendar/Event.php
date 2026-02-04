<?php
	namespace Calendar;

	class Event Extends \BaseModel {
		public \DateTime $timestamp_start;
		public \DateTime $timestamp_end;
		public \DateTime $timestamp_created;
		public string $name = "";
		public string $description = "";
		protected int $_calendar_id = 0;
		protected int $_location_id = 0;
		protected int $_user_created_id = 0;

		public function __construct($id = null) {
			$this->_tableName = 'calendar_events';
			$this->_metaTableName = 'calendar_event_metadata';
			$this->_tableMetaFKColumn = 'event_id';
			//$this->_addTypes();
            parent::__construct($id);
		}

		/** @method public calendar()
		 * Return Calendar Object associated with Event
		 * @return \Calendar\Calendar
		 */
		public function calendar() {
			return new \Calendar\Calendar($this->_calendar_id);
		}

		/** @method public userCreated()
		 * Return User Object Identified as Event Creator
		 * @return \Register\User
		 */
		public function userCreated() {
			return new \Register\Customer($this->_user_created_id);
		}

		/** @method public users()
		 * Return array of User Objects Associated with Event
		 * @return array \Register\Customer
		 */
		public function users() {
			$customerList = new \Calendar\Event\UserList();
			$parameters = [
				'event_id'	=> $this->id
			];
			$customers = $customerList->find($parameters);
			return $customers;
		}

		/** @method public addUser(user)
		 * Associate User with Event
		 * @param \Register\Person $user
		 * @return bool
		 */
		public function addUser(int $user_id, ?bool $optional = false): bool{
			if (!$user_id) {
				$this->error("User not found");
				return false;
			}

			$userList = new \Calendar\Event\UserList();
			$userList->add($this->id, $user_id, $optional);
			return true;
		}

		/** @method public location()
		 * Get/Set Location Object associated with Event
		 * @param int $location_id
		 * @return \Register\Location
		 */
		public function location(?int $location_id = null) {
			if ($location_id !== null) {
				$this->_location_id = $location_id;
			}
			return new \Register\Location($this->_location_id);
		}
	}
