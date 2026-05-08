<?php
	namespace Content;

	class Block Extends \BaseModel {

		public ?int $company_id = null;
		public string $target = "";
		public int $view_order = 0;
		public bool $active = true;
		public bool $deleted = false;
		public string $title = "";
		public ?int $menu_id = null;
		public string $name = "";		
		public string $content = "";
		public string $date_modified = "";

        public function __construct($id = 0) {
			$this->_tableName = 'content_messages';
			$this->_tableUKColumn = 'target';
			$this->_cacheKeyPrefix = 'content';
			parent::__construct($id);
        }

		public function get($target = ''): bool {
			// Clear any existing errors
			$this->clearError();

			// Initialize Database Service
			$database = new \Database\Service();

			// Prepare Query
			$get_contents_query = "
				SELECT	id
				FROM	content_messages
				WHERE	target = ?
			";

			// Bind Parameters
			$database->AddParam($target);

			// Execute Query
			$rs = $database->Execute($get_contents_query);

			// Check for SQL Error
			if (! $rs) {
				$this->SQLError($database->ErrorMsg());
				return false;
			}
			list($id) = $rs->FetchRow();
			if ($id) {
				$this->id = $id;
			}
			else {
				$this->error("Message '".$target."' not found");
				return false;
			}
			return $this->details();
		}

		public function getByCompanyIdTargetDeleted ($company_id, $target, $deleted = 0) {
			// Clear any existing errors
			$this->clearError();

			// Initialize Database Service
			$database = new \Database\Service();

			// Prepare Query
			$get_contents_query = "
				SELECT	id
				FROM	content_messages
				WHERE	company_id = ?
				AND		target = ?
				AND		deleted = ?
			";

			// Bind Parameters
			$database->AddParam($company_id);
			$database->AddParam($target);
			$database->AddParam($deleted);

			$rs = $database->Execute($get_contents_query);
			if (! $rs) {
				$this->SQLError($database->ErrorMsg());
				return false;
			}
			list($id) = $rs->FetchRow();
			if ($id) {
				$this->id = $id;
			} else {
				$this->error("Message not found");
				return false;
			}
			return $this->details();
		}

		public function add($parameters = []) {
			$this->clearError();
			$database = new \Database\Service();

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
			$database->AddParam($parameters['target']);
			$database->AddParam($GLOBALS['_SESSION_']->company->id);

            $rs = $database->Execute($insert_content_query);
            if ($database->ErrorMsg()) {
                $this->SQLError($database->ErrorMsg());
				app_log($this->error(),'error',__FILE__,__LINE__);
                return null;
            }

			$id = $database->Insert_ID();

			// add audit log
			$this->recordAuditEvent($id, 'Added new content message');

			$this->id = $id;
			return $this->update($parameters);
		}

        public function update($parameters = []): bool {
			// Clear any existing errors and cache
			$this->clearError();
			$this->clearCache();

			if (! $GLOBALS['_SESSION_']->customer->can('edit content messages')) {
				$this->error("You do not have permission to update content");
				app_log("Denied access in Content::Message::update(), 'content operator' required",'notice');
				return false;
			}

			if (! $this->id) {
				$this->error("id parameter required to update content");
				return false;
			}

			// Initialize Database Service
			$database = new \Database\Service();

			$ok_params = array(
				"name"		=> "name",
				"content"	=> "content",
				"title"		=> "title",
			);

			// Prepare Update Query
            $update_content_query = "
                UPDATE	content_messages
				SET		date_modified = sysdate()";

			$audit_messages = [];

			foreach ($ok_params as $parameter) {
				if (isset($parameters[$parameter]) && $parameters[$parameter] !== $this->$parameter) {
					$update_content_query .= ",
						$parameter = ?";
					$database->AddParam($parameters[$parameter]);
					if ($parameter == 'content') $audit_messages[] = "Updated content";
					else $audit_messages[] = "Changed $parameter to '".$parameters[$parameter]."'";
				}
			}

			$update_content_query .= "
				WHERE   id = ?";
			$database->AddParam($this->id);

			if (empty($audit_messages)) {
				// Nothing to update
				return true;
			}

            $rs = $database->Execute(
				$update_content_query
			);
            if (! $rs) {
                $this->SQLError($database->ErrorMsg());
                return false;
            }

			// audit the update event
			$this->recordAuditEvent($this->id, implode("; ", $audit_messages));

            return $this->details();
        }

		public function drop() {
			$this->clearError();
			$database = new \Database\Service();
			$delete_object_query = "
				DELETE
				FROM	content_messages
				WHERE	target = ?";
			$database->AddParam($this->id);
			$database->Execute($delete_object_query);
			if ($database->ErrorMsg()) {
				$this->SQLError($database->ErrorMsg());
				return false;
			}
			else {
				$this->recordAuditEvent($this->id, 'Deleted content message');
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
