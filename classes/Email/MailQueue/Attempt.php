<?php
	namespace Email\MailQueue;

	class Attempt Extends Record {
		public $id = 0;
		public $message_id = 0;
		public $delivery_id = 0;
		public $message_code = '';
		public $delivery_code = '';
		public $date_attempt = '';
		public $successful = false;
		public $server_response = '';
		public $response_code = '';

		public function find(array $parameters = array()) {
			$this->clearError();
			$database = $this->database();
			$query = "
				SELECT	a.id,
						a.message_id,
						a.delivery_id,
						a.date_attempt,
						a.successful,
						a.server_response,
						a.response_code,
						m.code AS message_code,
						d.code AS delivery_code
				FROM	mail_queue_attempts a
				INNER JOIN mail_queue_messages m
				ON		m.id = a.message_id
				INNER JOIN mail_queue_deliveries d
				ON		d.id = a.delivery_id
				WHERE	a.id = a.id
			";

			if (!empty($parameters['message_code'])) {
				$query .= "
				AND		m.code = ?";
				$database->AddParam(self::singleLine($parameters['message_code'], 64));
			}

			if (!empty($parameters['delivery_code'])) {
				$query .= "
				AND		d.code = ?";
				$database->AddParam(self::singleLine($parameters['delivery_code'], 64));
			}

			if (isset($parameters['successful']) && $parameters['successful'] !== '') {
				$query .= "
				AND		a.successful = ?";
				$database->AddParam(self::truthy($parameters['successful']) ? 1 : 0);
			}

			$start = $this->normalizeDate($parameters['date_attempt_start'] ?? null, 'date_attempt_start');
			if ($start === false) return null;
			if ($start) {
				$query .= "
				AND		a.date_attempt > ?";
				$database->AddParam($start);
			}

			$end = $this->normalizeDate($parameters['date_attempt_end'] ?? null, 'date_attempt_end');
			if ($end === false) return null;
			if ($end) {
				$query .= "
				AND		a.date_attempt < ?";
				$database->AddParam($end);
			}

			$query .= "
				ORDER BY a.date_attempt DESC, a.id DESC";

			$rs = $database->Execute($query);
			if (!$rs) {
				$this->SQLError($database->ErrorMsg());
				return null;
			}

			$attempts = array();
			while ($row = $rs->FetchNextObject(false)) {
				if (empty($row->id)) continue;
				$attempt = new self();
				$attempt->id = (int)self::field($row, 'id', 0);
				$attempt->message_id = (int)self::field($row, 'message_id', 0);
				$attempt->delivery_id = (int)self::field($row, 'delivery_id', 0);
				$attempt->message_code = (string)self::field($row, 'message_code');
				$attempt->delivery_code = (string)self::field($row, 'delivery_code');
				$attempt->date_attempt = (string)self::field($row, 'date_attempt');
				$attempt->successful = ((int)self::field($row, 'successful', 0)) === 1;
				$attempt->server_response = (string)self::field($row, 'server_response');
				$attempt->response_code = (string)self::field($row, 'response_code');
				$attempts[] = $attempt;
			}
			return $attempts;
		}
	}
