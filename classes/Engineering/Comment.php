<?php
	namespace Engineering;

	class Comment {
	
		private $_error;
		public $id;		
		public $date_comment;
		public $user_id;
		public $content;
		public $code;

		public function __construct($id = 0) {
			if (is_numeric($id) && $id > 0) {
				$this->id = $id;
				$this->details();
			}
		}

		public function add($parameters = array()) {
		
		    // make sure the code is valid
			if (isset($parameters['code']) && strlen($parameters['code']) && preg_match('/^[\w\-\.\_\s]+$/',$parameters['code'])) {
				$code = $parameters['code'];
			    $engineeringTask = new \Engineering\Task();
			    $engineeringTask->get($code);
			    if (!$engineeringTask->id) {
				    $this->_error = "Invalid code";
				    return false;				    
			    }
			} else {
					$this->_error = "Invalid code";
					return false;
			}

            // get a correct date added field
			if (get_mysql_date($parameters['date_comment'])) {
				$date_comment = get_mysql_date($parameters['date_comment']);
			} else {
				$date_comment = date('Y-m-d H:i:s');
			}

			// check if content set, required in database
			if (empty($parameters['content'])) {
				$this->_error = "Please enter content";
				return false;
			}

            // check valid user
			if (isset($parameters['user_id'])) {
				$tech = new \Register\Customer($parameters['user_id']);
				if (!$tech->id) {
                    $this->_error = "User not found";
                    return false;
				}
			}

			$add_object_query = "
				INSERT
				INTO engineering_task_comments
				(date_comment,user_id,content,code)
				VALUES
				(?,?,?,?)";
				
			$GLOBALS['_database']->Execute (
				$add_object_query,
				array($date_comment, $parameters['user_id'], $parameters['content'], $code)
			);

			if ($GLOBALS['_database']->ErrorMsg()) {
				$this->_error = "SQL Error in Engineering::Comment::add(): ".$GLOBALS['_database']->ErrorMsg();
				return false;
			}

			$this->id = $GLOBALS['_database']->Insert_ID();
			return $this->update($parameters);
		}

		public function update($parameters = array()) {
		
			if (! is_numeric($this->id)) {
				$this->_error = "No comments identified to update";
				return false;
			}
			$bind_params = array();
			
			$update_object_query = "
				UPDATE	engineering_task_comments
				SET		id = id
			";

			if (isset($parameters['user_id'])) {
				$tech = new \Register\Customer($parameters['user_id']);
				if ($tech->id) {
					$update_object_query .= ",
						user_id = ".$tech->id;
				} else {
					$this->_error = "User not found";
					return false;
				}
			}
			
			if (get_mysql_date($parameters['date_comment'])) {
				$date_comment = get_mysql_date($parameters['date_comment']);
			} else {
				$date_comment = date('Y-m-d H:i:s');
			}

			$update_object_query .= " WHERE	id = ?";
			array_push($bind_params,$this->id);

			$GLOBALS['_database']->Execute($update_object_query,$bind_params);
			if ($GLOBALS['_database']->ErrorMsg()) {
				$this->_error = "SQL Error in Engineering::Comment::update(): ".$GLOBALS['_database']->ErrorMsg();
				return false;
			}
			return $this->details();
		}

		public function get($id) {

			$get_object_query = "
				SELECT	id
				FROM	engineering_task_comments
				WHERE	id = ?
			";
			
			$rs = $GLOBALS['_database']->Execute (
				$get_object_query,
				array($id)
			);
			
			if (! $rs) {
				$this->_error = "SQL Error in Engineering::Comment::get(): ".$GLOBALS['_database']->ErrorMsg();
				return null;
			}

			list($id) = $rs->FetchRow();			
			if ($id) {
				$this->id = $id;
				return $this->details();
			} else {
				return false;
			}
		}
		
		public function details() {

			$get_object_query = "
				SELECT	*, unix_timestamp(date_comment) timestamp_added
				FROM	engineering_task_comments
				WHERE	id = ?
			";

			$rs = $GLOBALS['_database']->Execute (
				$get_object_query,
				array($this->id)
			);

			if (! $rs) {
				$this->_error = "SQL Error in Engineering::Task::details(): ".$GLOBALS['_database']->ErrorMsg();
				return false;
			};

			$object = $rs->FetchNextObject(false);
			$this->date_comment = $object->date_comment;
			$this->user_id = $object->user_id;
			$this->content = $object->content;
			$this->code = $object->code;
			$this->timestamp_added = $object->timestamp_added;
			return true;
		}

		public function error() {
			return $this->_error;
		}
	}
