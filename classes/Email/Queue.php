<?php
	namespace Email;

	class Queue extends \BaseModel {
	
		public function __construct($id = 0) {
			$this->_tableName = 'email_messages';
			parent::__construct($id);
		}

		public function addMessage($email) {
			if ($email->html()) $html = 1;
			else ($html = 0);
			$body = $email->body();
			$insert_message_query = "
				INSERT
				INTO	email_messages
				(		`date_created`,`status`,`to`,`from`,`subject`,`body`,`html`)
				VALUES
				(		sysdate(),'QUEUED',?,?,?,?,?)
			";
			$GLOBALS['_database']->Execute(
				$insert_message_query,
				array(
					$email->to(),
					$email->from(),
					$email->subject(),
					$body,
					$html
				)
			);
			if ($GLOBALS['_database']->ErrorMsg()) {
				$this->SQLError($GLOBALS['_database']->ErrorMsg());
				return false;
			}
			else {
				return true;
			}
		}

		public function takeNextUnsent() {
			$get_object_query = "
				SELECT	id
				FROM	email_messages
				WHERE	status in ('QUEUED','ERROR')
				AND		(date_tried is null or date_tried < sysdate() - power(2,tries) * 300)
				AND		date_created > sysdate() - 604800
				ORDER BY date_created
				LIMIT 1
			";

			$rs = $GLOBALS['_database']->Execute($get_object_query);
			if (! $rs) {
				$this->SQLError($GLOBALS['_database']->ErrorMsg());
				return null;
			}
			list($id) = $rs->FetchRow();
			if ($id > 0) {
				app_log("Taking message '$id'",'notice');
				$message = new \Email\Queue\Message($id);
				if ($message->lockForSend()) {
					return $message;
				}
				else {
					$this->SQLError("Could not lock message for delivery");
					return null;
				}
			}
			else {
				app_log("No messages ready");
				return null;
			}
		}

		public function messages($parameters = array()) {
			$get_objects_query = "
				SELECT	id
				FROM	email_messages
				WHERE	id = id
			";
			$bind_params = array();

			if (isset($parameters['status'])) {
				$get_objects_query .= "
				AND		status = ?";
				array_push($bind_params,$parameters['status']);
			}

			if (isset($parameters['to'])) {
				$get_objects_query .= "
				AND		`to` = ?";
				array_push($bind_params,$parameters['to']);
			}

			if (isset($parameters['from'])) {
				$get_objects_query .= "
				AND		`from` = ?";
				array_push($bind_params,$parameters['from']);
			}
			$rs = $GLOBALS['_database']->Execute($get_objects_query,$bind_params);

			if (! $rs) {
				$this->SQLError($GLOBALS['_database']->ErrorMsg());
				return null;
			}

			$messages = array();
			while (list($id) = $rs->FetchRow()) {
				$message = new \Email\Queue\Message($id);
				array_push($messages,$message);
			}
			return $messages;
		}
	}
