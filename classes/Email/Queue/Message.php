<?php
	namespace Email\Queue;

	class Message Extends \Email\Message {
		public $id;
		public $date_created;
		public $status;

		public function __construct($id = 0) {
			if (is_numeric($id) && $id > 0) {
				$this->id = $id;
				$this->load();
			}
		}

		public function load() {
			$get_email_query = "
				SELECT	*
				FROM	email_messages
				WHERE	id = ?
			";
			$rs = $GLOBALS['_database']->Execute($get_email_query,array($this->id));
			if (! $rs) {
				$this->_error = "SQL Error in Email::Queue::Message::load(): ".$GLOBALS['_database']->ErrorMsg();
				return false;
			}
			$object = $rs->FetchNextObject(false);
			if ($object->id > 0) {
				$this->to = $object->to;
				$this->from = $object->from;
				$this->subject = $object->subject;
				$this->body = $object->body;
				if ($object->html > 0) $this->html = true;
				else $this->html = false;
				$this->date_created = $object->date_created;
				$this->status = $object->status;
			}
			return true;
		}

		public function update($parameters = array()) {
			$update_message_query = "
				UPDATE	email_messages
				SET		id = id";
			$bind_params = array();

			if (isset($parameters['status'])) {
				$update_message_query .= ",
						status = ?";
				array_push($bind_params,$parameters['status']);
			}
			$update_message_query .= "
				WHERE	id = ?";
			array_push($bind_params,$this->id);
		}

		public function lockForSend() {
			$update_message_query = "
				UPDATE	email_messages
				SET		status = 'SENDING',
						date_tried = sysdate(),
						process_id = ?,
						tries = tries + 1
				WHERE	id = ?
			";
			$GLOBALS['_database']->Execute($update_message_query,array(getmypid(),$this->id));
			if ($GLOBALS['_database']->ErrorMsg()) {
				$this->_error = "SQL Error in Email::Queue::Message::lockForSend(): ".$GLOBALS['_database']->ErrorMsg();
				return false;
			}
			return true;
		}

		public function recordEvent($status,$code,$host,$response) {
			$add_history_query = "
				INSERT
				INTO	email_history
				VALUES	(?,sysdate(),?,?,?,?)
			";
			$GLOBALS['_database']->Execute($add_history_query,array($this->id,$status,$code,$host,$response));
			if ($GLOBALS['_database']->ErrorMsg()) {
				$this->_error = "SQL Error in Email::Queue::Message::recordEvent(): ".$GLOBALS['_database']->ErrorMsg();
				app_log($this->error,'error');
			}
			$update_status_query = "
				UPDATE	email_messages
				SET		status = ?
				WHERE	id = ?
			";
			$rs = $GLOBALS['_database']->Execute($update_status_query,array($status,$this->id));
			if ($GLOBALS['_database']->ErrorMsg()) {
				$this->_error = "SQL Error in Email::Queue::Message::recordEvent(): ".$GLOBALS['_database']->ErrorMsg();
				app_log($this->error,'error');
				return false;
			}
			return true;
		}
		public function recordSuccess($code,$host,$response) {
			return $this->recordEvent('SENT',$code,$host,$response);
		}
		public function recordError($code,$host,$response) {
			return $this->recordEvent('ERROR',$code,$host,$response);
		}
		public function recordFailure($code,$host,$response) {
			return $this->recordEvent('FAILED',$code,$host,$response);
		}
	}