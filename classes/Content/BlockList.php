<?php
	namespace Content;
	
	/**
	 * MessageList Class
	 *
	 * This class extends BaseListClass and provides methods for finding, searching,
	 * and retrieving Content\Message objects based on various criteria.
	 */
	class BlockList Extends \BaseListClass {

		/** @var string|null Stores any error messages */
		protected $error = null;

		/**
		 * Find messages based on given parameters
		 *
		 * @param array $parameters Search parameters
		 * @return array|null Array of Content\Message objects or null on error
		 */
		public function findAdvanced($parameters, $advanced, $controls): array {
            $this->clearError();
            $this->resetCount();

			$get_contents_query = "
				SELECT	id
				FROM	content_messages
				WHERE	id = id";

			if (isset($parameters['target']) && strlen($parameters['target']))
				$get_contents_query .= "
				AND		target = ".$GLOBALS['_database']->qstr($parameters['target'],get_magic_quotes_gpc());

			$rs = $GLOBALS['_database']->Execute($get_contents_query);
			if (! $rs) {
				$this->SQLError($GLOBALS['_database']->ErrorMsg());
				return [];
			}

			$messages = array();
			while (list($id) = $rs->FetchRow()) {
				$message = new \Content\Message($id);
                $this->incrementCount();
				array_push($messages,$message);
			}
			return $messages;
		}

		/**
		 * Search for messages based on a search string
		 *
		 * @param array $parameters Search parameters
		 * @return array|int Array of Content\Message objects or 0 on error
		 */
		public function searchAdvanced($parameters, $advanced, $controls): array {
            $this->clearError();
            $this->resetCount();

			$database = new \Database\Service();

			$get_contents_query = "
				SELECT	id
				FROM	content_messages
				WHERE	id = id";
				
			if (isset($parameters['string']) && strlen($parameters['string'])) {
    			$searchString = $GLOBALS['_database']->qstr($parameters['string'],get_magic_quotes_gpc());
    			$searchString = preg_replace("/'$/", "%'", $searchString);
                $searchString = preg_replace("/^'/", "'%", $searchString);
    			$get_contents_query .= " AND (`target` LIKE " . $searchString . " OR `title` LIKE " . $searchString . " OR `name` LIKE " . $searchString . " OR `content` LIKE " . $searchString . ")";
			} else {
			    $this->error = "Error: Search 'string' Parameter is Required.";
			    return 0;
			}
        
			$rs = $database->Execute($get_contents_query);
			if (! $rs) {
				$this->SQLError($database->ErrorMsg());
				return 0;
			}

			$messages = array();
			while (list($id) = $rs->FetchRow()) {
				$message = new \Content\Message($id);
				if (!isset($parameters['is_user_search']) || empty($parameters['is_user_search'])) {
    				unset($message->content);
				} else {
    				$message->content = substr(strip_tags($message->content), 0, 150) . '...';
				}
                $this->incrementCount();
				array_push($messages,$message);
			}

			// Join to the existing query
			$get_contents_query = "
				SELECT DISTINCT(stx.object_id)
				FROM search_tags_xref stx
				INNER JOIN search_tags st ON stx.tag_id = st.id
				WHERE st.class = 'Content::Message'
				AND (
					st.category LIKE $searchString
					OR st.value LIKE $searchString
				)
			";

			$rs = $GLOBALS['_database']->Execute($get_contents_query);
			if (! $rs) {
				$this->SQLError($GLOBALS['_database']->ErrorMsg());
				return 0;
			}

			while (list($id) = $rs->FetchRow()) {
				$message = new \Content\Message($id);
				if (!isset($parameters['is_user_search']) || empty($parameters['is_user_search'])) {
    				unset($message->content);
				} else {
    				$message->content = substr(strip_tags($message->content), 0, 150) . '...';
				}
                $this->incrementCount();
				array_push($messages,$message);
			}

			return $messages;
		}

		/**
		 * Get messages by problem type and product
		 *
		 * @param string $problemType The type of problem
		 * @param string $product The product code
		 * @return array|null Array of Content\Message objects or null on error
		 */
		public function getByProblemAndProduct($problemType, $product) {
			$this->clearError();
			$this->resetCount();

			$query = "
				SELECT cm.object_id as id
				FROM search_tags_xref cm
				JOIN search_tags st ON cm.tag_id = st.id
				WHERE st.class = 'Content::Message'
				AND (
					(st.category = 'problem_type' AND st.value = ?)
					OR (st.category = 'product_code' AND st.value = ?)
				)
				GROUP BY cm.object_id
				HAVING COUNT(DISTINCT st.category) >= 2
			";

			$stmt = $GLOBALS['_database']->Prepare($query);
			$result = $GLOBALS['_database']->Execute($stmt, array($problemType, $product));

			if (!$result) {
				$this->SQLError($GLOBALS['_database']->ErrorMsg());
				return null;
			}

			$messages = array();
			while ($row = $result->FetchRow()) {
				$message = new \Content\Message($row['id']);
				$this->incrementCount();
				array_push($messages, $message);
			}

			return $messages;
		}
	}
