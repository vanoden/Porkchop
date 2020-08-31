<?php
	namespace Engineering;

	/**
	 * list of engineering task comments
	 */
	class CommentList {

		private $_error;

		/**
		 * get current task comments based on params criteria array
		 * @param array $parameters
		 */
		public function find($parameters = array()) {

			$find_objects_query = "
				SELECT	id
				FROM	engineering_task_comments
				WHERE	id = id
			";

            // if search term, then constrain by that
            if (isset($parameters['searchTerm'])) {
                $find_objects_query = "
                SELECT	`id`
                FROM	`engineering_task_comments`
                    WHERE `code` LIKE '%".$parameters['searchTerm']."%' 
                    OR `content` LIKE '%".$parameters['searchTerm']."%";
            }

			if (isset($parameters['code'])) {
				$find_objects_query .= "
				AND		code = '" . $parameters['code'] . "'";
			}
			
			$find_objects_query .= "
				ORDER BY date_comment DESC
			";
			if (isset($parameters['_limit']) && is_numeric($parameters['_limit'])) {
				$find_objects_query .= "
				LIMIT ".$parameters['_limit'];
			}

            $rs = executeSQLByParams($find_objects_query,array());
			if (! $rs) {
				$this->_error = "SQL Error in Engineering::CommentList::find(): ".$GLOBALS['_database']->ErrorMsg();
				return null;
			}
			
			$comments = array();
			while (list($id) = $rs->FetchRow()) {		
				$comment = new \Engineering\Comment($id);
				array_push($comments,$comment);
			}
			return $comments;
		}

		public function error() {
			return $this->_error;
		}		
	}
