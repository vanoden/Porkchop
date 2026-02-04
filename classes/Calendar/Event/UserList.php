<?php

	namespace Calendar\Event;

	class UserList Extends \BaseListClass {
		public function __construct() {
			parent::__construct();
		}

		/** @method public findAdvanced() 
		 * Find Users associated with Events based on advanced parameters
		 * @param array $parameters
		 * @param array $advanced - Extended search parameters
		 * @param array $controls - Control parameters (limit, offset, sort, etc)
		 * @return array \Calendar\Event\User
		*/
		public function findAdvanced(array $parameters, array $advanced, array $controls): array {
			$this->clearError();
			$this->resetCount();

			// Prepare Database Service
			$database = new \Database\Service();

			// Build Base Query
			$find_objects_query = "
				SELECT	 id, event_id, user_id, optional
				FROM	 calendar_event_users ceu
				WHERE	 1 = 1
			";

			// Build WHERE Clause from Parameters
			if (!empty($parameters['event_id'])) {
				$event = new \Calendar\Event($parameters['event_id']);
				if (! $event->exists()) {
					$this->error("Event not found");
					return [];
				}
				$find_objects_query .= "
				AND ceu.event_id = ?
				";
				$database->AddParam($parameters['event_id']);
			}

			if (!empty($parameters['optional'])) {
				$find_objects_query .= "
				AND ceu.optional = ?
				";
				$database->AddParam($parameters['optional'] ? 1 : 0);
			}

			// Add Sorting and Limits
			$controls_defaults = [
				'sort'		=> 'id',
				'direction'	=> 'ASC',
				'limit'		=> 0,
				'offset'	=> 0
			];
			$controls = array_merge($controls_defaults, $controls);

			$find_objects_query .= "
				ORDER BY ".$controls['sort']." ".$controls['direction']."
			";

			if ($controls['limit'] > 0) {
				$find_objects_query .= "
				LIMIT ".$controls['offset'].", ".$controls['limit']."
				";
			}

			$rs = $database->Execute($find_objects_query);

			// Check for SQL Error
			if ($database->error()) {
				$this->SQLError("Finding Event Users: ".$database->error());
				return [];
			}

			// Build Object List from Results
			$object_list = [];
			while ($record = $rs->FetchNextObject(false)) {
				$event_user = new \Calendar\Event\User($record->id);
				$event_user->optional = $record->optional ? true : false;
				$object_list[] = $event_user;
				$this->incrementCount();
			}
			return $object_list;
		}

		/** @method public add(event_id, user_id, optional)
		 * Associate User with Event
		 * @param int $event_id
		 * @param int $user_id
		 * @param bool|null $optional
		 * @return bool
		 */
		public function add(int $event_id, int $user_id, ?bool $optional = false): bool {
			// Clear Previous Errors
			$this->clearError();

			// Validate Parameters
			if (! $event_id || ! $user_id) {
				$this->error("Invalid Event or User ID");
				return false;
			}

			$event = new \Calendar\Event($event_id);
			if (! $event->exists()) {
				$this->error("Event not found");
				return false;
			}

			$user = new \Register\Customer($user_id);
			if (! $user->exists()) {
				$this->error("User not found");
				return false;
			}

			if ($optional === null) {
				$optional = false;
			}
			elseif ($optional !== true) {
				$optional = false;
			}

			// Prepare Database Service
			$database = new \Database\Service();

			// Prepare Queryh to Insert Record
			$insert_query = "
				INSERT INTO `calendar_event_users` (
					`event_id`,
					`user_id`,
					`optional`
				) VALUES (
					?,
					?,
					?
				)
			";

			// Bind Parameters
			$database->AddParam($event->id);
			$database->AddParam($user->id);
			$database->AddParam($optional ? 1 : 0);

			// Execute Query
			$database->Execute($insert_query);

			// Check for SQL Error
			if ($database->error()) {
				$this->SQLError("Adding User to Event: ".$database->error());
				return false;
			}
			return true;
		}
	}