<?php
	namespace Support\Request;

	class note {
		private $_error;
		public $id;
		public $author_id;
		public $date_note;
		public $description;
		public $event_type = 'note';

		public function __construct($id = 0) {
			if ($id > 0) {
				$this->id = $id;
				$this->details();
			}
		}

		public function add($parameters = array()) {
			$author = new \Register\Customer($parameters['author_id']);
			if (! $author->id) {
				$this->_error = "Customer required";
				return false;
			}
			$request = new \Support\Request($parameters['request_id']);
			if (! $request->id) {
				$this->_error = "Request required";
				return false;
			}

			$add_object_query = "
				INSERT
				INTO	support_notes
				(		author_id,
						request_id,
						date_note,
						description
				)
				VALUES
				(		?,?,?,?)
			";
			$GLOBALS['_database']->Execute(
				$add_object_query,
				array(
					$author->id,
					$request->id,
					get_mysql_date($parameters['date_note']),
					$parameters['description']
				)
			);
			if ($GLOBALS['_database']->ErrorMsg()) {
				$this->_error = "SQL Error in Support::Request::Note::add(): ".$GLOBALS['_database']->ErrorMsg();
				return null;
			}
			$this->id = $GLOBALS['_database']->Insert_ID();
			return $this->details();
		}

		private function details() {
			# Get Request Details
			$get_request_query = "
				SELECT	id,
						request_id,
						author_id,
						date_note,
						description
				FROM	support_notes
				WHERE	id = ?
			";
			$rs = $GLOBALS['_database']->Execute(
				$get_request_query,
				array($this->id)
			);
			if (! $rs) {
				$this->_error = "SQL Error in SupportRequest::details: ".$GLOBALS['_database']->ErrorMsg();
				return null;
			}
			$record = $rs->FetchNextObject(false);
			$this->author = new \Register\Customer($record->author_id);
			$this->date_note = $record->date_note;
			$this->description = $record->description;

			return true;
		}

		public function error() {
			return $this->_error;
		}
	}
