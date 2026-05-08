<?php
	namespace Calendar;

	use IntlGregorianCalendar;

	class Calendar Extends \BaseModel {
		public string $code = "";
		public string $name = "";
		public string $description = "";
		public int $owner_id = 0;
		public int $timestamp_created = 0;
		public bool $public = false;
		protected IntlGregorianCalendar $_timezone;

		public function __construct($id = null) {
			$this->_tableName = 'calendar_calendars';
			$this->_metaTableName = 'calendar_metadata';
			$this->_tableMetaFKColumn = 'calendar_id';
			$this->_addFields(['id','code','name','description','owner_id','timestamp_created','public']);
            parent::__construct($id);
		}

		public function add($parameters = []): bool {
			$this->timestamp_created = time();
			if (empty($parameters['code'])) {
				$porkchop = new \Porkchop();
				$parameters['code'] = $porkchop->biguuid();
			}
			return parent::add($parameters);
		}

		/** @method public owner()
		 * Get the User object for the owner of this Calendar
		 * @return \Register\User - Owner User object
		 */
		public function owner(): \Register\User {
			$user = new \Register\User($this->owner_id);
			return $user;
		}

		/** @method public users()
		 * Get an array of User objects who have access to this Calendar
		 * @return array - Array of User objects
		 */
		public function users(): array {
			$calendarUserList = new \Calendar\CalendarUserList();
			return $calendarUserList->usersForCalendar($this->id);
		}

		/** @method function addUser(user)
		 * Add a User to this Calendar
		 * @param \Register\User user - User object to add
		 * @return bool - True on success, false on failure
		 */
		public function addUser(\Register\User $user): bool {
			$calendarUser = new \Calendar\CalendarUser();
			$calendarUser->calendar_id = $this->id;
			$calendarUser->user_id = $user->id;
			return $calendarUser->save();
		}

		/** @method public upcomingEvents(): array
		 * Get an array of upcoming Event objects for this Calendar
		 * @return array - Array of Event objects
		 */
		public function upcomingEvents(): array {
			$eventList = new \Calendar\EventList();

			$parameters = [
				"calendar_id" => $this->id,
				"timestamp_start" => time()
			];
			$controls = [
				'limit' => 20,
				'sort' => 'timestamp_start',
				'order' => 'asc'
			];
			return $eventList->findAdvanced($parameters,[],$controls);
		}
	}
