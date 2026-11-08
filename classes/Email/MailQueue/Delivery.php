<?php
	namespace Email\MailQueue;

	class Delivery Extends Record {
		public $id = 0;
		public $code = '';
		public $message_id = 0;
		public $message_code = '';
		public $type = 'TO';
		public $name = '';
		public $address = '';
		public $status = 'QUEUED';
		public $date_delivered = '';
		public $from_address = '';
		public $date_queued = '';
		public $date_last_attempt = '';
		public $count_attempts = 0;
		public $subject = '';
		public $mime_type = '';
		public $content = '';

		public function get($code) {
			$this->clearError();
			$code = self::singleLine($code, 64);
			if ($code === '') {
				$this->error("Delivery not found");
				return false;
			}
			$database = $this->database();
			$database->AddParam($code);
			$rs = $database->Execute("
				SELECT	d.id
				FROM	mail_queue_deliveries d
				WHERE	d.code = ?
			");
			if (!$rs) {
				$this->SQLError($database->ErrorMsg());
				return false;
			}
			$row = $rs->FetchRow();
			if (empty($row[0])) {
				$this->error("Delivery not found");
				return false;
			}
			return $this->load((int)$row[0]);
		}

		public function load($id) {
			$this->clearError();
			$database = $this->database();
			$database->AddParam((int)$id);
			$rs = $database->Execute(self::selectSql()."
				AND		d.id = ?
			");
			if (!$rs) {
				$this->SQLError($database->ErrorMsg());
				return false;
			}
			$row = $rs->FetchNextObject(false);
			if (empty($row->id)) {
				$this->error("Delivery not found");
				return false;
			}
			$this->apply($row);
			return true;
		}

		public function find(array $parameters = array()) {
			$this->clearError();
			$database = $this->database();
			$query = self::selectSql();

			if (!empty($parameters['message_code'])) {
				$query .= "
				AND		m.code = ?";
				$database->AddParam(self::singleLine($parameters['message_code'], 64));
			}

			if (!empty($parameters['code'])) {
				$query .= "
				AND		d.code = ?";
				$database->AddParam(self::singleLine($parameters['code'], 64));
			}

			if (isset($parameters['status']) && $parameters['status'] !== '' && $parameters['status'] !== array()) {
				$statuses = self::statusList($parameters['status']);
				if ($statuses === null) {
					$this->error("Invalid status");
					return null;
				}
				if (count($statuses) > 0) {
					$placeholders = implode(',', array_fill(0, count($statuses), '?'));
					$query .= "
				AND		d.status IN ($placeholders)";
					foreach ($statuses as $status) $database->AddParam($status);
				}
			}

			if (!empty($parameters['from_address'])) {
				$query .= "
				AND		m.from_address = ?";
				$database->AddParam(self::singleLine($parameters['from_address'], 255));
			}

			if (!empty($parameters['to_address'])) {
				$query .= "
				AND		d.address = ?";
				$database->AddParam(self::singleLine($parameters['to_address'], 255));
			}

			$queuedStart = $this->normalizeDate($parameters['date_queued_start'] ?? null, 'date_queued_start');
			if ($queuedStart === false) return null;
			if ($queuedStart) {
				$query .= "
				AND		m.date_queued > ?";
				$database->AddParam($queuedStart);
			}

			$queuedEndValue = $parameters['date_queued_end'] ?? ($parameters['date_queue_end'] ?? null);
			$queuedEnd = $this->normalizeDate($queuedEndValue, 'date_queue_end');
			if ($queuedEnd === false) return null;
			if ($queuedEnd) {
				$query .= "
				AND		m.date_queued < ?";
				$database->AddParam($queuedEnd);
			}

			$lastStart = $this->normalizeDate($parameters['last_attempt_start'] ?? null, 'last_attempt_start');
			if ($lastStart === false) return null;
			if ($lastStart) {
				$query .= "
				AND		(
							SELECT	MAX(a.date_attempt)
							FROM	mail_queue_attempts a
							WHERE	a.delivery_id = d.id
						) > ?";
				$database->AddParam($lastStart);
			}

			$lastEnd = $this->normalizeDate($parameters['last_attempt_end'] ?? null, 'last_attempt_end');
			if ($lastEnd === false) return null;
			if ($lastEnd) {
				$query .= "
				AND		(
							(
								SELECT	MAX(a.date_attempt)
								FROM	mail_queue_attempts a
								WHERE	a.delivery_id = d.id
							) IS NULL
							OR (
								SELECT	MAX(a.date_attempt)
								FROM	mail_queue_attempts a
								WHERE	a.delivery_id = d.id
							) < ?
						)";
				$database->AddParam($lastEnd);
			}

			$query .= "
				ORDER BY m.date_queued DESC, d.id DESC";

			$rs = $database->Execute($query);
			if (!$rs) {
				$this->SQLError($database->ErrorMsg());
				return null;
			}

			$deliveries = array();
			while ($row = $rs->FetchNextObject(false)) {
				if (empty($row->id)) continue;
				$delivery = new self();
				$delivery->apply($row);
				$deliveries[] = $delivery;
			}
			return $deliveries;
		}

		public function recordAttempt(array $parameters) {
			$this->clearError();
			if (empty($this->id)) {
				$this->error("Delivery not found");
				return false;
			}
			if (!in_array($this->status, array('QUEUED', 'ERROR'), true)) {
				$this->error("Delivery cannot be attempted while ".$this->status);
				return false;
			}

			$when = $parameters['date_attempt'] ?? '';
			if ($when === '' || $when === null) $when = date('Y-m-d H:i:s');
			$when = $this->normalizeDate($when, 'date_attempt');
			if ($when === false || $when === null) {
				if (!$this->error()) $this->error("Invalid date_attempt");
				return false;
			}

			$success = self::truthy($parameters['success'] ?? false);
			$response = (string)($parameters['server_response'] ?? '');
			if (strlen($response) > 65000) $response = substr($response, 0, 65000);
			$code = self::singleLine($parameters['response_code'] ?? '', 16);

			$database = $this->database();
			$database->AddParam($this->id);
			$countRs = $database->Execute("
				SELECT	COUNT(*)
				FROM	mail_queue_attempts
				WHERE	delivery_id = ?
			");
			if (!$countRs) {
				$this->SQLError($database->ErrorMsg());
				return false;
			}
			list($previous) = $countRs->FetchRow();
			$previous = (int)$previous;

			$database->resetParams();
			$database->AddParam($this->message_id);
			$database->AddParam($this->id);
			$database->AddParam($when);
			$database->AddParam($success ? 1 : 0);
			$database->AddParam($response);
			$database->AddParam($code);
			$inserted = $database->Execute("
				INSERT INTO mail_queue_attempts
				(message_id, delivery_id, date_attempt, successful, server_response, response_code)
				VALUES (?, ?, ?, ?, ?, ?)
			");
			if (!$inserted) {
				$this->SQLError($database->ErrorMsg());
				return false;
			}

			if ($success) $status = 'DELIVERED';
			elseif ($previous >= self::FINAL_FAILURE_COUNT) $status = 'FAILED';
			else $status = 'ERROR';

			$database->resetParams();
			$database->AddParam($status);
			$update = "
				UPDATE	mail_queue_deliveries
				SET		status = ?";
			if ($success) {
				$update .= ",
						date_delivered = ?";
				$database->AddParam($when);
			}
			$update .= "
				WHERE	id = ?
				AND		status IN ('QUEUED','ERROR')";
			$database->AddParam($this->id);
			if (!$database->Execute($update)) {
				$this->SQLError($database->ErrorMsg());
				return false;
			}

			$this->status = $status;
			$this->date_last_attempt = $when;
			$this->count_attempts = $previous + 1;
			if ($success) $this->date_delivered = $when;
			return true;
		}

		public function apiRecord() {
			$record = new \stdClass();
			$record->message_code = $this->message_code;
			$record->code = $this->code;
			$record->status = $this->status;
			$record->from_address = $this->from_address;
			$record->to_address = $this->address;
			$record->date_queued = $this->date_queued;
			$record->date_last_attempt = $this->date_last_attempt;
			$record->count_attempts = $this->count_attempts;
			$record->subject = $this->subject;
			$record->mime_type = $this->mime_type;
			$record->content = $this->content;
			return $record;
		}

		public static function statusList($value) {
			$allowed = array('QUEUED', 'ERROR', 'CANCELLED', 'FAILED', 'DELIVERED');
			$raw = is_array($value) ? $value : preg_split('/\s*,\s*/', (string)$value);
			$statuses = array();
			foreach ($raw as $status) {
				$status = strtoupper(trim((string)$status));
				if ($status === '') continue;
				if (!in_array($status, $allowed, true)) return null;
				if (!in_array($status, $statuses, true)) $statuses[] = $status;
			}
			return $statuses;
		}

		private static function selectSql() {
			return "
				SELECT	d.id,
						d.code,
						d.message_id,
						d.type,
						d.name,
						d.address,
						d.status,
						d.date_delivered,
						m.code AS message_code,
						m.from_address,
						m.date_queued,
						m.subject,
						m.mime_type,
						m.content,
						(
							SELECT	MAX(a.date_attempt)
							FROM	mail_queue_attempts a
							WHERE	a.delivery_id = d.id
						) AS date_last_attempt,
						(
							SELECT	COUNT(*)
							FROM	mail_queue_attempts a
							WHERE	a.delivery_id = d.id
						) AS count_attempts
				FROM	mail_queue_deliveries d
				INNER JOIN mail_queue_messages m
				ON		m.id = d.message_id
				WHERE	d.id = d.id
			";
		}

		private function apply($row) {
			$this->id = (int)self::field($row, 'id', 0);
			$this->code = (string)self::field($row, 'code');
			$this->message_id = (int)self::field($row, 'message_id', 0);
			$this->message_code = (string)self::field($row, 'message_code');
			$this->type = (string)self::field($row, 'type', 'TO');
			$this->name = (string)self::field($row, 'name');
			$this->address = (string)self::field($row, 'address');
			$this->status = (string)self::field($row, 'status', 'QUEUED');
			$this->date_delivered = (string)self::field($row, 'date_delivered');
			$this->from_address = (string)self::field($row, 'from_address');
			$this->date_queued = (string)self::field($row, 'date_queued');
			$this->date_last_attempt = (string)self::field($row, 'date_last_attempt');
			$this->count_attempts = (int)self::field($row, 'count_attempts', 0);
			$this->subject = (string)self::field($row, 'subject');
			$this->mime_type = (string)self::field($row, 'mime_type');
			$this->content = (string)self::field($row, 'content');
		}
	}
