<?php
	namespace Content;

	class Block Extends \BaseModel {

        public $name;
		public $cached = 0;
		public $content;
		public $title;
		public $target;

        public function __construct($id = 0) {
			$this->_tableName = 'content_messages';
			$this->_tableUKColumn = 'target';
			$this->_cacheKeyPrefix = 'content';
			parent::__construct($id);
        }

		public function get($target = ''): bool {
			$this->clearError();

			$get_contents_query = "
				SELECT	id
				FROM	content_messages
				WHERE	target = ?
			";
			$rs = $GLOBALS['_database']->Execute(
				$get_contents_query,
				array(
					$target
				)
			);
			if (! $rs) {
				$this->SQLError($GLOBALS['_database']->ErrorMsg());
				return false;
			}
			list($id) = $rs->FetchRow();
			if ($id) {
				$this->id = $id;
			}
			elseif(false) {
				# Make Sure User Has Privileges
				app_log("No match found for message '$target', adding",'info',__FILE__,__LINE__);
				if (! $GLOBALS['_SESSION_']->customer->can('edit content messages')) {
					$this->error("Sorry, insufficient privileges. Role 'content developer' required.");
					return null;
				}
				$this->add(array("target" => $target));
				if ($this->error()) return null;
			}
			else {
				$this->error("Message not found");
				return false;
			}
			return $this->details();
		}

		public function add($parameters = []) {
			$this->clearError();
			$_customer = new \Register\Customer();
			if (! $GLOBALS['_SESSION_']->customer->can('edit content messages')) {
				$this->error("You do not have permission to add content");
				app_log("Denied access in Content::add, 'content operator' required to add message '".$parameters['target']."'",'notice',__FILE__,__LINE__);
				return null;
			}
			if (! $parameters['target']) $parameters['target'] = '';
			$insert_content_query = "
				INSERT
				INTO	content_messages
				(		target,
						company_id,
						content,
						date_modified
				)
				VALUES
				(		?,?,'&nbsp',sysdate())
			";
            $rs = $GLOBALS['_database']->Execute(
				$insert_content_query,
				array(
					$parameters['target'],
					$GLOBALS['_SESSION_']->company->id
				)
			);
            if ($GLOBALS['_database']->ErrorMsg()) {
                $this->SQLError($GLOBALS['_database']->ErrorMsg());
				app_log($this->error(),'error',__FILE__,__LINE__);
                return null;
            }

			$id = $GLOBALS['_database']->Insert_ID();
			$this->id = $id;
			return $this->update($parameters);
		}
        public function update($parameters = []): bool {
			$this->clearCache();
			if (! $GLOBALS['_SESSION_']->customer->can('edit content messages')) {
				$this->error("You do not have permission to update content");
				app_log("Denied access in Content::Message::update(), 'content operator' required",'notice');
				return null;
			}

			if (! $this->id) {
				$this->error("id parameter required to update content");
				return null;
			}

			cache_unset("content[".$this->id."]");

			$ok_params = array(
				"name"		=> "name",
				"content"	=> "content",
				"title"		=> "title",
			);

            $update_content_query = "
                UPDATE	content_messages
				SET		date_modified = sysdate()";

			$bind_params = array();

			foreach ($ok_params as $parameter) {
				if (isset($parameters[$parameter])) {
					$update_content_query .= ",
						$parameter = ?";
					array_push($bind_params,$parameters[$parameter]);
				}
			}

			$update_content_query .= "
				WHERE   id = ?";
			array_push($bind_params,$this->id);
	
			query_log($update_content_query,$bind_params);
            $rs = $GLOBALS['_database']->Execute(
				$update_content_query,$bind_params
			);
            if (! $rs) {
                $this->SQLError($GLOBALS['_database']->ErrorMsg());
                return false;
            }

            return $this->details();
        }

		public function drop() {
			$database = new \Database\Service();
			$delete_object_query = "
				DELETE
				FROM	content_messages
				WHERE	target = ?";
			$database->addParam($this->id);
			$database->Execute($delete_object_query);
			if ($database->ErrorMsg()) {
				$this->SQLError($database->ErrorMsg());
				return false;
			}
			else {
				return true;
			}
		}
		public function purge_cache() {
			$this->clearError();
			if (! $GLOBALS['_SESSION_']->customer->can('edit content messages')) {
				$this->error("You do not have permission to update content");
				app_log("Denied access in Content::purge_cache, 'content operator' required",'info',__FILE__,__LINE__);
				return false;
			}

			if (! $this->id) {
				$this->error("id parameter required to update users");
				return false;
			}

			cache_unset("content[".$this->id."]");
			return true;
		}

		public function validTarget($string) {
			if (preg_match('/^\w[\w\-\.\_]{0,31}$/',$string)) return true;
			else return false;
		}

		public function validName($string): bool {
			if (empty(urldecode($string))) return false;
			if (! preg_match('/[\<\>\%]/',urldecode($string))) return true;
			else return false;
		}

		public function validContent($string) {
			if (preg_match('/\<script/',urldecode($string))) return false;
			else return true;
		}
    }
