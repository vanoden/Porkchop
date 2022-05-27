<?php
	namespace Content;

	class Message {
	
        public $id;
        public $name;
		public $error;
		public $cached = 0;

        public function __construct($id = 0) {
			if ($id > 0) {
				$this->id = $id;
	            $this->details();
			}
        }

		public function get($target = '') {
			$this->error = NULL;

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
				$this->error = "SQL Error in Content::Message::get(): ".$GLOBALS['_database']->ErrorMsg();
				return 0;
			}
			list($id) = $rs->FetchRow();
			if ($id) {
				$this->id = $id;
			} elseif(false) {
				# Make Sure User Has Privileges
				app_log("No match found for message '$target', adding",'info',__FILE__,__LINE__);
				if (! $GLOBALS['_SESSION_']->customer->can('edit content messages')) {
					$this->error = "Sorry, insufficient privileges. Role 'content developer' required.";
					return null;
				}
				$this->add(array("target" => $target));
				if ($this->error) return null;
			}
			else {
				$this->error = "Message not found";
				return false;
			}
			return $this->details();
		}

        public function details() {
			$this->error = NULL;
			
			if (! isset($this->id)) {
				$this->error = "ID Required for Content Details";
				debug_print_backtrace();
				return null;
			}

			# Cached Content Object, Yay!	
			if ($result = cache_get("content[".$this->id."]")) {
				$this->name		= $result->name;
				$this->target	= $result->target;
				$this->title	= $result->title;
				$this->content	= $result->content;
				$this->cached	= 1;
				return 1;
			}

            $get_content_query = "
                SELECT  p.id,
						p.name,
                        p.target,
                        p.title,
                        p.content
                FROM    content_messages p
                WHERE   p.company_id = '".$GLOBALS['_SESSION_']->company->id."'
                AND     p.id = ?
            ";
            $rs = $GLOBALS['_database']->Execute(
				$get_content_query,
				array($this->id)
			);
            if (! $rs) {
				error_log(print_r(debug_backtrace(),true));
                $this->error = "SQL Error in Content::Message::details(): ".$GLOBALS['_database']->ErrorMsg();
                return 0;
            }

            $result = $rs->FetchNextObject(false);
			if (! isset($result->id)) {
				return 0;
			}
			$this->id = $result->id;
            $this->name		= $result->name;
            $this->target	= $result->target;
            $this->title	= $result->title;
			$this->content	= $result->content;

			cache_set("content[".$this->id."]",$result);
			return 1;
		}

		public function add($parameters = array()) {
			$this->error = NULL;
			$_customer = new \Register\Customer();
			if (! $GLOBALS['_SESSION_']->customer->has_role('edit content messages')) {
				$this->error = "You do not have permission to add content";
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
                $this->error = "SQL Error in Content::Message::add(): ".$GLOBALS['_database']->ErrorMsg();
				app_log($this->error,'error',__FILE__,__LINE__);
                return null;
            }

			$id = $GLOBALS['_database']->Insert_ID();
			$this->id = $id;
			return $this->update($parameters);
		}
        public function update($parameters = array()) {
			$this->error = NULL;
			if (! $GLOBALS['_SESSION_']->customer->has_role('edit content messages')) {
				$this->error = "You do not have permission to update content";
				app_log("Denied access in Content::Message::update(), 'content operator' required",'notice');
				return null;
			}

			if (! $this->id) {
				$this->error = "id parameter required to update content";
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
                $this->error = "SQL Error in Content::Message::update(): ".$GLOBALS['_database']->ErrorMsg();
                return 0;
            }

            return $this->details();
        }
		public function purge_cache() {
			$this->error = NULL;
			if (! role('content operator')) {
				$this->error = "You do not have permission to update content";
				app_log("Denied access in Content::purge_cache, 'content operator' required",'info',__FILE__,__LINE__);
				return false;
			}

			if (! $this->id) {
				$this->error = "id parameter required to update users";
				return false;
			}

			cache_unset("content[".$this->id."]");
			return true;
		}

		public function error($message = null) {
			if (!empty($message)) $this->error = $message;
			return $this->error;
		}
    }
