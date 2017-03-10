<?php
	namespace Content;

	class Message {
        public $id;
        public $name;
		public $error;
		public $cached = 0;

        public function __construct($id = 0) {
			$this->schema_manager();
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
				$this->error = $GLOBALS['_database']->ErrorMsg();
				return 0;
			}
			list($id) = $rs->FetchRow();
			if ($id) {
				$this->id = $id;
			}
			else {
				# Make Sure User Has Privileges
				app_log("No match found for message '$code', adding",'info',__FILE__,__LINE__);
				if (! in_array("content developer",$GLOBALS['_SESSION_']->customer->roles)) {
					$this->error = "Sorry, insufficient privileges. Role 'content developer' required.";
					return null;
				}
				$this->add(array("target" => $target));
				if ($this->error) return null;
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
				$this->name		= $result["name"];
				$this->target	= $result["target"];
				$this->title	= $result["title"];
				$this->content	= $result["content"];
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
			//error_log(preg_replace("/(\n|\r)/","",preg_replace("/\t/"," ",$get_content_query)));
            $rs = $GLOBALS['_database']->Execute(
				$get_content_query,
				array($this->id)
			);
            if (! $rs) {
				error_log(print_r(debug_backtrace(),true));
                $this->error = $GLOBALS['_database']->ErrorMsg()." in content->details()";
                return 0;
            }

            $result = $rs->FetchRow();
			if (! isset($result['id'])) {
				return new stdClass();
			}

            $this->name		= $result["name"];
            $this->target	= $result["target"];
            $this->title	= $result["title"];
			$this->content	= $result["content"];

			cache_set("content[".$this->id."]",$result);
            return 1;
        }

		public function add($parameters = array()) {
			$this->error = NULL;
			$_customer = new \Register\Customer();
			if (! role('content operator')) {
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
						content
				)
				VALUES
				(		?,?,'&nbsp')
			";
            $rs = $GLOBALS['_database']->Execute(
				$insert_content_query,
				array(
					$parameters['target'],
					$GLOBALS['_SESSION_']->company
				)
			);
            if ($GLOBALS['_database']->ErrorMsg()) {
                $this->error = $GLOBALS['_database']->ErrorMsg();
				app_log($this->error,'error',__FILE__,__LINE__);
                return null;
            }

			$id = $GLOBALS['_database']->Insert_ID();
			$this->id = $id;
			return $this->update($parameters);
		}
        public function update($parameters = array()) {
			$this->error = NULL;
			if (! in_array('content operator',$GLOBALS['_SESSION_']->customer->roles)) {
				$this->error = "You do not have permission to update content";
				error_log("Denied access in Content::update, 'content operator' required");
				return 0;
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
				SET		id = id";

			foreach ($ok_params as $parameter) {
				if (isset($parameters[$parameter])) $update_content_query .= ",
						$parameter = ".$GLOBALS['_database']->qstr($parameters[$parameter],get_magic_quotes_gpc());
			}

			$update_content_query .= "
				WHERE   id = ?";
	
			//error_log(preg_replace("/(\n|\r)/","",preg_replace("/\t/"," ",$update_content_query)));
            $rs = $GLOBALS['_database']->Execute(
				$update_content_query,
				array($this->id)
			);
            if (! $rs) {
                $this->error = $GLOBALS['_database']->ErrorMsg();
                return 0;
            }

            return $this->details();
        }
		public function purge_cache($id) {
			$this->error = NULL;
			if (! role('content operator')) {
				$this->error = "You do not have permission to update content";
				app_log("Denied access in Content::purge_cache, 'content operator' required",'info',__FILE__,__LINE__);
				return null;
			}

			if (! $id) {
				$id = $this->id;
			}
			if (! $id) {
				$this->error = "id parameter required to update users";
				return null;
			}

            $id = preg_replace("/\D/",'',$id);

			cache_unset("content[".$id."]");
		}
		public function schema_manager() {
			$this->error = NULL;
			# See if Schema is Available
			$schema_list = $GLOBALS['_database']->MetaTables();

			if (! in_array("content__info",$schema_list)) {
				# Create company__info table
				$create_table_query = "
					CREATE TABLE content__info (
						label	varchar(100) not null primary key,
						value	varchar(255)
					)
				";
				$GLOBALS['_database']->Execute($create_table_query);
				if ($GLOBALS['_database']->ErrorMsg())
				{
					$this->error = "SQL Error creating info table in content::Content::schema_manager: ".$GLOBALS['_database']->ErrorMsg();
					return 0;
				}
			}

			# Check Current Schema Version
			$get_version_query = "
				SELECT	value
				FROM	content__info
				WHERE	label = 'schema_version'
			";

			$rs = $GLOBALS['_database']->Execute($get_version_query);
			if (! $rs) {
				$this->error = "SQL Error in content::Content::schema_manager: ".$GLOBALS['_database']->ErrorMsg();
				return 0;
			}

			list($current_schema_version) = $rs->FetchRow();

			if ($current_schema_version < 1) {
				$update_schema_query = "
					INSERT
					INTO	content__info
					VALUES	('schema_version',1)
					ON DUPLICATE KEY UPDATE
							value = 1
				";
				$GLOBALS['_database']->Execute($update_schema_query);
				if ($GLOBALS['_database']->ErrorMsg()) {
					$this->error = "SQL Error creating _info table in content::Content::schema_manager: ".$GLOBALS['_database']->ErrorMsg();
					return 0;
				}
				$current_schema_version = 1;
			}
			if ($current_schema_version < 2) {
				$create_table_query = "
					CREATE TABLE IF NOT EXISTS `content_messages` (
					  `id` int(6) NOT NULL AUTO_INCREMENT,
					  `company_id` int(5) NOT NULL DEFAULT '0',
					  `target` varchar(255) NOT NULL DEFAULT '',
					  `view_order` int(3) NOT NULL DEFAULT '500',
					  `active` int(1) NOT NULL DEFAULT '1',
					  `deleted` int(1) NOT NULL DEFAULT '0',
					  `title` varchar(80) NOT NULL DEFAULT '',
					  `menu_id` int(11) NOT NULL DEFAULT '0',
					  `name` varchar(255) NOT NULL DEFAULT '',
					  `date_modified` datetime NOT NULL,
					  `content` text,
					  PRIMARY KEY (`id`),
					  UNIQUE KEY `uk_target` (`company_id`,`target`),
					  KEY `idx_main` (`company_id`,`target`,`deleted`),
					  FOREIGN KEY `fk_company_id` (`company_id`) REFERENCES `company_companies` (`id`)
					)
				";
				$GLOBALS['_database']->Execute($create_table_query);
				if ($GLOBALS['_database']->ErrorMsg()) {
					$this->error = "SQL Error creating contact types table in content::Content::schema_manager: ".$GLOBALS['_database']->ErrorMsg();
					return 0;
				}
				
				# Add Roles for Content Module
				$insert_roles_query = "
					INSERT
					INTO	register_roles
					(		name,description)
					VALUES
					(		'content operator','Can edit web site content')
					ON DUPLICATE KEY UPDATE
							name = name
				";
				$GLOBALS['_database']->Execute($insert_roles_query);
				if ($GLOBALS['_database']->ErrorMsg()) {
					$this->error = "SQL Error adding roles in register::Person::schema_manager: ".$GLOBALS['_database']->ErrorMsg();
					return 0;
				}
				$current_schema_version = 2;
			}
			if ($current_schema_version < 3) {
				# Add Roles for Content Module
				$insert_roles_query = "
					INSERT
					INTO	register_roles
					(		name,description)
					VALUES
					(		'content developer','Can view api page')
					ON DUPLICATE KEY UPDATE
							name = name
				";
				$GLOBALS['_database']->Execute($insert_roles_query);
				if ($GLOBALS['_database']->ErrorMsg()) {
					$this->error = "SQL Error adding roles in register::Person::schema_manager: ".$GLOBALS['_database']->ErrorMsg();
					return 0;
				}
				$current_schema_version = 3;
			}
				

			$update_schema_version = "
				UPDATE	content__info
				SET		value = $current_schema_version
				WHERE	label = 'schema_version'
			";
			$GLOBALS['_database']->Execute($update_schema_version);
			if ($GLOBALS['_database']->ErrorMsg()) {
				$this->error = "SQL Error in content::Content::schema_manager: ".$GLOBALS['_database']->ErrorMsg();
				return 0;
			}
		}
    }
?>