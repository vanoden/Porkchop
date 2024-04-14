<?php
	namespace Register;

	class Notification Extends \BaseModel {
	
		public function add($params = array()) {

			$add_notification_query = "
				INSERT
				INTO	register_notifications
				VALUES (null,?)
			";
			$GLOBALS['_database']->Execute($add_notification_query,array($params['name']));
			if ($GLOBALS['_database']->ErrorMsg()) {
				$this->error("SQL Error in Register::Notification::add(): ".$GLOBALS['_database']->ErrorMsg());
				return false;
			}
			$this->id = $GLOBALS['_database']->Insert_ID();

			// audit the add event
			$auditLog = new \Site\AuditLog\Event();
			$auditLog->add(array(
				'instance_id' => $this->id,
				'description' => 'Added new '.$this->_objectName(),
				'class_name' => get_class($this),
				'class_method' => 'add'
			));

			return $this->update($params);
		}

		public function update($params = []): bool {

			$update_object_query = "
				UPDATE	register_notifications
				SET		id = id
			";
			$bind_params = array();
			return $this->details();
		}

		public function get($name) {

			$get_object_query = "
				SELECT	id
				FROM	register_notifications
				WHERE	name = ?
			";
			$bind_params = array($name);
			$rs = $GLOBALS['_database']->Execute($get_object_query,$bind_params);
			if (! $rs) {
				$this->error("SQL Error in Register::Notification::get(): ".$GLOBALS['_database']->ErrorMsg());
				return false;
			}
			list($id) = $rs->FetchRow();
			$this->id = $id;

			// audit the update event
			$auditLog = new \Site\AuditLog\Event();
			$auditLog->add(array(
				'instance_id' => $this->id,
				'description' => 'Updated '.$this->_objectName(),
				'class_name' => get_class($this),
				'class_method' => 'update'
			));	

			return $this->details();
		}

		public function details() {

			$get_object_query = "
				SELECT	*
				FROM	register_notifications
				WHERE	id = ?
			";

			$rs = $GLOBALS['_database']->Execute($get_object_query,array($this->id));
			if (! $rs) {
				$this->error("SQL Error in Register::Notification::details(): ".$GLOBALS['_database']->ErrorMsg());
				return false;
			}
			$object = $rs->FetchNextObject(false);
			$this->id = $object->id;
			$this->name = $object->name;
			return true;
		}

		# Get Notification Subscribers
		public function subscribers() {
			$get_objects_query = "
				SELECT	user_id
				FROM	register_roles_notifications
				WHERE	notification_id = ?
			";
			$rs = $GLOBALS['_database']->Execute($get_objects_query,array($this->id));
			if (! $rs) {
				$this->error("SQL Error in Register::Notification::subscribers(): ".$GLOBALS['_database']->ErrorMsg());
				return null;
			}
			$users = array();
			while (list($id) = $rs->FetchRow()) {
				$user = new \Register\Customer($id);
				array_push($users,$user);
			}
			return $users;
		}

		public function send($message) {
			if (! $this->id) {
				$this->error = "Notification not found";
				return null;
			}
			$members = $this->subscribers();
			foreach ($members as $member) {
				app_log("Sending notification to '".$member->code."' about ".$this->name,'debug',__FILE__,__LINE__);
				$member->notify($message);
				if ($member->error) {
					app_log("Error sending notification: ".$member->error,'error',__FILE__,__LINE__);
					$this->error = "Failed to send notification: ".$member->error;
					return false;
				}
			}
		}
	}
?>
