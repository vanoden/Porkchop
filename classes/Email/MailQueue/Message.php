<?php
	namespace Email\MailQueue;

	class Message Extends Record {
		public $id = 0;
		public $code = '';
		public $date_queued = '';
		public $user_id = 0;
		public $user_code = '';
		public $from_address = '';
		public $subject = '';
		public $mime_type = 'text/plain';
		public $content = '';
		public $count_attempt = 0;

		public function enqueue(array $parameters) {
			$this->clearError();

			$from = self::singleLine($parameters['from_address'] ?? '', 255);
			if (!$this->validEmail($from)) {
				$this->error("A valid from address is required");
				return false;
			}

			$subject = self::singleLine($parameters['subject'] ?? '', 255);
			if ($subject === '') {
				$this->error("A subject is required");
				return false;
			}

			$mime = self::singleLine($parameters['mime_type'] ?? 'text/plain', 255);
			if ($mime === '') $mime = 'text/plain';
			if (!preg_match('#^[\w.+-]+/[\w.+-]+$#', $mime)) {
				$this->error("Invalid mime type");
				return false;
			}

			$content = (string)($parameters['content'] ?? '');
			if (strlen($content) > 1000000) {
				$this->error("Message content is too large");
				return false;
			}

			$userId = isset($parameters['user_id']) ? (int)$parameters['user_id'] : 0;
			if ($userId < 0) {
				$this->error("Invalid user");
				return false;
			}

			$deliveries = $parameters['deliveries'] ?? array();
			if (!is_array($deliveries) || count($deliveries) < 1) {
				$this->error("At least one recipient is required");
				return false;
			}
			if (count($deliveries) > 500) {
				$this->error("Too many recipients");
				return false;
			}

			$normalized = array();
			foreach ($deliveries as $delivery) {
				$type = strtoupper(trim((string)($delivery['type'] ?? 'TO')));
				if (!in_array($type, array('TO', 'CC', 'BCC'), true)) {
					$this->error("Invalid delivery type");
					return false;
				}
				$address = self::singleLine($delivery['address'] ?? '', 255);
				if (!$this->validEmail($address)) {
					$this->error("Invalid recipient address");
					return false;
				}
				$normalized[] = array(
					'type' => $type,
					'name' => self::singleLine($delivery['name'] ?? '', 255),
					'address' => $address,
					'code' => self::newCode(),
				);
			}

			$database = $this->database();
			$code = self::newCode();
			$database->AddParam($code);
			$database->AddParam($userId);
			$database->AddParam($from);
			$database->AddParam($subject);
			$database->AddParam($mime);
			$database->AddParam($content);
			$inserted = $database->Execute("
				INSERT INTO mail_queue_messages
				(code, date_queued, user_id, from_address, subject, mime_type, content)
				VALUES (?, sysdate(), ?, ?, ?, ?, ?)
			");
			if (!$inserted) {
				$this->SQLError($database->ErrorMsg());
				return false;
			}

			$this->id = (int)$database->Insert_ID();
			$this->code = $code;
			$this->date_queued = date('Y-m-d H:i:s');
			$this->user_id = $userId;
			$this->from_address = $from;
			$this->subject = $subject;
			$this->mime_type = $mime;
			$this->content = $content;

			foreach ($normalized as $delivery) {
				$database->resetParams();
				$database->AddParam($delivery['code']);
				$database->AddParam($this->id);
				$database->AddParam($delivery['type']);
				$database->AddParam($delivery['name']);
				$database->AddParam($delivery['address']);
				$ok = $database->Execute("
					INSERT INTO mail_queue_deliveries
					(code, message_id, type, name, address, status)
					VALUES (?, ?, ?, ?, ?, 'QUEUED')
				");
				if (!$ok) {
					$this->SQLError($database->ErrorMsg());
					$database->resetParams();
					$database->AddParam($this->id);
					$database->Execute("DELETE FROM mail_queue_deliveries WHERE message_id = ?");
					$database->resetParams();
					$database->AddParam($this->id);
					$database->Execute("DELETE FROM mail_queue_messages WHERE id = ?");
					return false;
				}
			}
			return true;
		}

		public static function deliveriesFromAddresses($recipients, $type = 'TO') {
			$deliveries = array();
			if (!is_array($recipients)) $recipients = array($recipients);
			foreach ($recipients as $recipient) {
				if (is_array($recipient)) {
					foreach (self::deliveriesFromAddresses($recipient, $type) as $delivery) {
						$deliveries[] = $delivery;
					}
					continue;
				}
				$parsed = self::parseAddress((string)$recipient, $type);
				if ($parsed) $deliveries[] = $parsed;
			}
			return $deliveries;
		}

		public static function parseAddress($value, $type = 'TO') {
			$value = trim((string)$value);
			if ($value === '') return null;
			$name = '';
			$address = $value;
			if (preg_match('/^(.*)<([^>]+)>$/', $value, $matches)) {
				$name = trim($matches[1], " \t\"'");
				$address = trim($matches[2]);
			}
			return array(
				'type' => $type,
				'name' => $name,
				'address' => $address,
			);
		}

		public function find(array $parameters = array()) {
			$this->clearError();
			$database = $this->database();
			$query = "
				SELECT	m.id,
						m.code,
						m.date_queued,
						m.user_id,
						m.from_address,
						m.subject,
						m.mime_type,
						m.content,
						(SELECT COUNT(*) FROM mail_queue_attempts a WHERE a.message_id = m.id) AS count_attempt,
						(SELECT login FROM register_users u WHERE u.id = m.user_id) AS user_code
				FROM	mail_queue_messages m
				WHERE	m.id = m.id
			";

			if (!empty($parameters['user_code'])) {
				$query .= "
				AND		EXISTS (
							SELECT	1
							FROM	register_users u
							WHERE	u.id = m.user_id
							AND		u.login = ?
						)";
				$database->AddParam(self::singleLine($parameters['user_code'], 64));
			}

			if (!empty($parameters['from_address'])) {
				$query .= "
				AND		m.from_address = ?";
				$database->AddParam(self::singleLine($parameters['from_address'], 255));
			}

			if (!empty($parameters['mime_type'])) {
				$query .= "
				AND		m.mime_type = ?";
				$database->AddParam(self::singleLine($parameters['mime_type'], 255));
			}

			$queuedStart = $this->normalizeDate($parameters['date_queued_start'] ?? null, 'date_queued_start');
			if ($queuedStart === false) return null;
			if ($queuedStart) {
				$query .= "
				AND		m.date_queued > ?";
				$database->AddParam($queuedStart);
			}

			$queuedEnd = $this->normalizeDate($parameters['date_queued_end'] ?? null, 'date_queued_end');
			if ($queuedEnd === false) return null;
			if ($queuedEnd) {
				$query .= "
				AND		m.date_queued < ?";
				$database->AddParam($queuedEnd);
			}

			$attemptStart = $this->normalizeDate($parameters['date_attempt_start'] ?? null, 'date_attempt_start');
			if ($attemptStart === false) return null;
			if ($attemptStart) {
				$query .= "
				AND		EXISTS (
							SELECT	1
							FROM	mail_queue_attempts a
							WHERE	a.message_id = m.id
							AND		a.date_attempt > ?
						)";
				$database->AddParam($attemptStart);
			}

			$attemptEnd = $this->normalizeDate($parameters['date_attempt_end'] ?? null, 'date_attempt_end');
			if ($attemptEnd === false) return null;
			if ($attemptEnd) {
				$query .= "
				AND		(
							NOT EXISTS (
								SELECT	1
								FROM	mail_queue_attempts a0
								WHERE	a0.message_id = m.id
							)
							OR EXISTS (
								SELECT	1
								FROM	mail_queue_attempts a1
								WHERE	a1.message_id = m.id
								AND		a1.date_attempt < ?
							)
						)";
				$database->AddParam($attemptEnd);
			}

			if (isset($parameters['count_attempt_more']) && $parameters['count_attempt_more'] !== '') {
				if (!is_numeric($parameters['count_attempt_more'])) {
					$this->error("Invalid count_attempt_more");
					return null;
				}
				$query .= "
				AND		(SELECT COUNT(*) FROM mail_queue_attempts a WHERE a.message_id = m.id) >= ?";
				$database->AddParam((int)$parameters['count_attempt_more']);
			}

			if (isset($parameters['count_attempt_less']) && $parameters['count_attempt_less'] !== '') {
				if (!is_numeric($parameters['count_attempt_less'])) {
					$this->error("Invalid count_attempt_less");
					return null;
				}
				$query .= "
				AND		(SELECT COUNT(*) FROM mail_queue_attempts a WHERE a.message_id = m.id) <= ?";
				$database->AddParam((int)$parameters['count_attempt_less']);
			}

			$query .= "
				ORDER BY m.date_queued DESC, m.id DESC";

			$rs = $database->Execute($query);
			if (!$rs) {
				$this->SQLError($database->ErrorMsg());
				return null;
			}

			$messages = array();
			while ($row = $rs->FetchNextObject(false)) {
				if (empty($row->id)) continue;
				$messages[] = self::fromRow($row);
			}
			return $messages;
		}

		public static function fromRow($row) {
			$message = new self();
			$message->id = (int)self::field($row, 'id', 0);
			$message->code = (string)self::field($row, 'code');
			$message->date_queued = (string)self::field($row, 'date_queued');
			$message->user_id = (int)self::field($row, 'user_id', 0);
			$message->user_code = (string)self::field($row, 'user_code');
			$message->from_address = (string)self::field($row, 'from_address');
			$message->subject = (string)self::field($row, 'subject');
			$message->mime_type = (string)self::field($row, 'mime_type');
			$message->content = (string)self::field($row, 'content');
			$message->count_attempt = (int)self::field($row, 'count_attempt', 0);
			return $message;
		}

		public function apiRecord() {
			$record = new \stdClass();
			$record->code = $this->code;
			$record->user_code = $this->user_code;
			$record->date_queued = $this->date_queued;
			$record->count_attempt = $this->count_attempt;
			$record->from_address = $this->from_address;
			$record->subject = $this->subject;
			$record->content = $this->content;
			$record->mime_type = $this->mime_type;
			return $record;
		}
	}
