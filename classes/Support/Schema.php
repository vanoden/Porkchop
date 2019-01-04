<?php
	namespace Support;

	class Schema {
		public $errno;
		public $error;
		public $module = "support";
		private $roles = array(
			'support manager'	=> 'Full control over requests, actions, etc',
			'support user'		=> 'Can work with support requests'
		);

		public function __construct() {
			$this->upgrade();
		}
		public function version() {
			# See if Schema is Available
			$schema_list = $GLOBALS['_database']->MetaTables();
			$info_table = strtolower($this->module)."__info";

			if (! in_array($info_table,$schema_list)) {
                # Create __info table
                $create_table_query = "
                    CREATE TABLE `$info_table` (
                        label   varchar(100) not null primary key,
                        value   varchar(255)
                    )
                ";
                $GLOBALS['_database']->Execute($create_table_query);
                if ($GLOBALS['_database']->ErrorMsg()) {
                    $this->error = "SQL Error creating info table in ".$this->module."Schema::version: ".$GLOBALS['_database']->ErrorMsg();
                    return null;
                }
            }

            # Check Current Schema Version
            $get_version_query = "
                SELECT  value
                FROM    `$info_table`
                WHERE   label = 'schema_version'
            ";

            $rs = $GLOBALS['_database']->Execute($get_version_query);
            if (! $rs) {
                $this->error = "SQL Error in ".$this->module."::version: ".$GLOBALS['_database']->ErrorMsg();
                return null;
            }

            list($version) = $rs->FetchRow();
            if (! $version) $version = 0;
            return $version;
		}
		public function upgrade() {
			$this->error = '';
			$info_table = strtolower($this->module)."__info";

			# See if Schema is Available
			$schema_list = $GLOBALS['_database']->MetaTables();

			if (! in_array($info_table,$schema_list)) {
				# Create company__info table
				$create_table_query = "
					CREATE TABLE `$info_table` (
						label	varchar(100) not null primary key,
						value	varchar(255)
					)
				";
				$GLOBALS['_database']->Execute($create_table_query);
				if ($GLOBALS['_database']->ErrorMsg()) {
					$this->error = "SQL Error creating info table in ".$this->module."Schema::upgrade: ".$GLOBALS['_database']->ErrorMsg();
					return null;
				}
			}

			# Check Current Schema Version
			$get_version_query = "
				SELECT	value
				FROM	`$info_table`
				WHERE	label = 'schema_version'
			";

			$rs = $GLOBALS['_database']->Execute($get_version_query);
			if (! $rs) {
				$this->error = "SQL Error in ".$this->module."Schema::upgrade: ".$GLOBALS['_database']->ErrorMsg();
				return null;
			}

			list($current_schema_version) = $rs->FetchRow();
            if ($current_schema_version < 1) {
                app_log("Upgrading schema to version 1",'notice',__FILE__,__LINE__);

                # Start Transaction
                if (! $GLOBALS['_database']->BeginTrans())
                    app_log("Transactions not supported",'warning',__FILE__,__LINE__);

				# Collection of Items Requiring Action
                $create_table_query = "
                    CREATE TABLE IF NOT EXISTS `support_requests` (
						id INT(11) NOT NULL AUTO_INCREMENT,
						code varchar(255) NOT NULL,
                        customer_id INT(11) NOT NULL,
                        organization_id INT(11) NOT NULL,
                        date_request datetime,
						type enum('ORDER','SERVICE') NOT NULL,
                        status enum('NEW','CANCELLED','OPEN','COMPLETE','CLOSED') NOT NULL DEFAULT 'NEW',
						PRIMARY KEY `pk_id` (`id`),
						UNIQUE KEY `uk_code` (`code`),
						FOREIGN KEY `fk_customer` (`customer_id`) REFERENCES `register_users` (`id`),
						INDEX `idx_date` (`date_request`),
						INDEX `idx_status` (`status`,`date_request`)
					)
                ";
				$GLOBALS['_database']->Execute($create_table_query);
				if ($GLOBALS['_database']->ErrorMsg()) {
					$this->error = "SQL Error creating support_requests table in Support::Schema::upgrade(): ".$GLOBALS['_database']->ErrorMsg();
					app_log($this->error,'error',__FILE__,__LINE__);
					$GLOBALS['_database']->RollbackTrans();
					return false;
				}

				# Items requiring action
                $create_table_query = "
                    CREATE TABLE IF NOT EXISTS `support_request_items` (
						id INT(11) NOT NULL AUTO_INCREMENT,
						request_id INT(11) NOT NULL,
						line INT(11) NOT NULL,
						product_id INT(11),
						serial_number varchar(255),
						quantity INT(11) NOT NULL DEFAULT 1,
                        description TEXT,
						status enum('NEW','ACTIVE','PENDING_VENDOR','PENDING_CUSTOMER','COMPLETE','CLOSED') NOT NULL DEFAULT 'NEW',
						assigned_id INT(11),
						PRIMARY KEY `pk_id` (`id`),
						UNIQUE KEY `uk_line` (`request_id`,`line`),
						INDEX `idx_serial` (`product_id`,`serial_number`)
					)
                ";
				$GLOBALS['_database']->Execute($create_table_query);
				if ($GLOBALS['_database']->ErrorMsg()) {
					$this->error = "SQL Error creating support_request_items table in Support::Schema::upgrade(): ".$GLOBALS['_database']->ErrorMsg();
					app_log($this->error,'error',__FILE__,__LINE__);
					$GLOBALS['_database']->RollbackTrans();
					return false;
				}

				# Things We Have To Do
				$create_table_query = "
					CREATE TABLE IF NOT EXISTS `support_item_actions` (
						id INT(11) NOT NULL AUTO_INCREMENT,
						item_id INT(11),
						type varchar(255) NOT NULL,
						date_entered datetime,
						entered_id INT(11) NOT NULL,
						date_requested datetime,
						requested_id INT(11) NOT NULL,
						date_assigned datetime,
						assigned_id INT(11),
						date_completed datetime,
						status enum('NEW','ASSIGNED','ACTIVE','PENDING CUSTOMER','PENDING VENDOR','CANCELLED','COMPLETE'),
						description TEXT,
						PRIMARY KEY `pk_id` (`id`),
						FOREIGN KEY `fk_item` (`item_id`) REFERENCES `support_request_items` (`id`),
						FOREIGN KEY `fk_requested` (`requested_id`) REFERENCES `register_users` (`id`),
						INDEX `idx_date_request` (`date_requested`,`requested_id`)
					)
				";
				$GLOBALS['_database']->Execute($create_table_query);
				if ($GLOBALS['_database']->ErrorMsg()) {
					$this->error = "SQL Error creating support_actions table in Support::Schema::upgrade(): ".$GLOBALS['_database']->ErrorMsg();
					app_log($this->error,'error',__FILE__,__LINE__);
					$GLOBALS['_database']->RollbackTrans();
					return false;
				}

				# Things we have done
				$create_table_query = "
					CREATE TABLE IF NOT EXISTS `support_action_events` (
						id INT(11) NOT NULL AUTO_INCREMENT,
						action_id INT(11) NOT NULL,
						type enum('BUILD','SHIP','RETURN','REPAIR') NOT NULL,
						user_id INT(11) NOT NULL,
						date_event datetime,
						description TEXT,
						hours DECIMAL(5,1) NOT NULL DEFAULT 0,
						PRIMARY KEY `pk_event` (`id`),
						FOREIGN KEY `fk_action` (`action_id`) REFERENCES `support_item_actions` (`id`),
						FOREIGN KEY `fk_user` (`user_id`) REFERENCES `register_users` (`id`)
					)
				";
				$GLOBALS['_database']->Execute($create_table_query);
				if ($GLOBALS['_database']->ErrorMsg()) {
					$this->error = "SQL Error creating support_events table in Support::Schema::upgrade(): ".$GLOBALS['_database']->ErrorMsg();
					app_log($this->error,'error',__FILE__,__LINE__);
					$GLOBALS['_database']->RollbackTrans();
					return false;
				}

				# Parts Required
				$create_table_query = "
					CREATE TABLE IF NOT EXISTS `support_parts` (
						id INT(11) NOT NULL AUTO_INCREMENT,
						event_id INT(11) NOT NULL,
						user_id INT(11) NOT NULL,
						product_id INT(11) NOT NULL,
						quantity INT(11) NOT NULL DEFAULT 1,
						description TEXT,
						PRIMARY KEY `pk_id` (`id`),
						FOREIGN KEY `fk_event` (`event_id`) REFERENCES `support_action_events` (`id`),
						FOREIGN KEY `fk_user` (`user_id`) REFERENCES `register_users` (`id`)
					)
				";
				$GLOBALS['_database']->Execute($create_table_query);
				if ($GLOBALS['_database']->ErrorMsg()) {
					$this->error = "SQL Error creating support_parts table in Support::Schema::upgrade(): ".$GLOBALS['_database']->ErrorMsg();
					app_log($this->error,'error',__FILE__,__LINE__);
					$GLOBALS['_database']->RollbackTrans();
					return false;
				}

                $current_schema_version = 1;
                $update_schema_version = "
                    INSERT
                    INTO    support__info
                    VALUES  ('schema_version',$current_schema_version)
                    ON DUPLICATE KEY UPDATE
                        value = $current_schema_version
                ";
                $GLOBALS['_database']->Execute($update_schema_version);
                if ($GLOBALS['_database']->ErrorMsg()) {
                    $this->error = "SQL Error in Support::Schema::upgrade(): ".$GLOBALS['_database']->ErrorMsg();
                    app_log($this->error,'error',__FILE__,__LINE__);
                    $GLOBALS['_database']->RollbackTrans();
                    return undef;
                }
                $GLOBALS['_database']->CommitTrans();
            }
			if ($current_schema_version < 2) {
                app_log("Upgrading schema to version 2",'notice',__FILE__,__LINE__);

                # Start Transaction
                if (! $GLOBALS['_database']->BeginTrans())
                    app_log("Transactions not supported",'warning',__FILE__,__LINE__);

				# Return Authorizations
                $create_table_query = "
                    CREATE TABLE IF NOT EXISTS `support_rmas` (
						id INT(11) NOT NULL AUTO_INCREMENT,
						code varchar(100) NOT NULL,
						item_id INT(11) NOT NULL,
                        approved_id INT(11) NOT NULL,
                        date_approved datetime,
						shipment_id INT(11),
                        status enum('NEW','SHIPPED','RECEIVED') NOT NULL DEFAULT 'NEW',
						PRIMARY KEY `pk_id` (`id`),
						FOREIGN KEY `fk_item` (`item_id`) REFERENCES `support_request_items` (`id`),
						FOREIGN KEY `fk_approver` (`approved_id`) REFERENCES `register_users` (`id`),
						INDEX `idx_date` (`date_approved`),
						INDEX `idx_status` (`status`,`date_approved`)
					)
                ";
				$GLOBALS['_database']->Execute($create_table_query);
				if ($GLOBALS['_database']->ErrorMsg()) {
					$this->error = "SQL Error creating support_rmas table in Support::Schema::upgrade(): ".$GLOBALS['_database']->ErrorMsg();
					app_log($this->error,'error',__FILE__,__LINE__);
					$GLOBALS['_database']->RollbackTrans();
					return false;
				}

				// Comments about items
                $create_table_query = "
                    CREATE TABLE IF NOT EXISTS `support_item_comments` (
						id INT(11) NOT NULL AUTO_INCREMENT,
						item_id INT(11) NOT NULL,
                        author_id INT(11) NOT NULL,
                        date_comment datetime,
						content text,
						PRIMARY KEY `pk_id` (`id`),
						FOREIGN KEY `fk_item` (`item_id`) REFERENCES `support_request_items` (`id`),
						FOREIGN KEY `fk_author` (`author_id`) REFERENCES `register_users` (`id`),
						INDEX `idx_date` (`date_comment`)
					)
                ";
				$GLOBALS['_database']->Execute($create_table_query);
				if ($GLOBALS['_database']->ErrorMsg()) {
					$this->error = "SQL Error creating support_item_comments table in Support::Schema::upgrade(): ".$GLOBALS['_database']->ErrorMsg();
					app_log($this->error,'error',__FILE__,__LINE__);
					$GLOBALS['_database']->RollbackTrans();
					return false;
				}

                $current_schema_version = 2;
                $update_schema_version = "
                    INSERT
                    INTO    support__info
                    VALUES  ('schema_version',$current_schema_version)
                    ON DUPLICATE KEY UPDATE
                        value = $current_schema_version
                ";
                $GLOBALS['_database']->Execute($update_schema_version);
                if ($GLOBALS['_database']->ErrorMsg()) {
                    $this->error = "SQL Error in Support::Schema::upgrade(): ".$GLOBALS['_database']->ErrorMsg();
                    app_log($this->error,'error',__FILE__,__LINE__);
                    $GLOBALS['_database']->RollbackTrans();
                    return undef;
                }
                $GLOBALS['_database']->CommitTrans();
            }
            
            if ($current_schema_version < 3) {
            
                app_log("Upgrading schema to version 3",'notice',__FILE__,__LINE__);

                # Start Transaction
                if (! $GLOBALS['_database']->BeginTrans())
                    app_log("Transactions not supported",'warning',__FILE__,__LINE__);

				// new support search page
                $create_table_query = "
                    INSERT INTO page_pages (`module`, `view`) VALUES ('support', 'search');
                ";
				$GLOBALS['_database']->Execute($create_table_query);
				if ($GLOBALS['_database']->ErrorMsg()) {
					$this->error = "SQL Error creating support search page in Support::Schema::upgrade(): ".$GLOBALS['_database']->ErrorMsg();
					app_log($this->error,'error',__FILE__,__LINE__);
					$GLOBALS['_database']->RollbackTrans();
					return false;
				}

				// new support search page
                $create_table_query = "
                    INSERT INTO page_metadata (`page_id`, `key`, `value`) VALUES ('222','template','admin.html');
                ";
				$GLOBALS['_database']->Execute($create_table_query);
				if ($GLOBALS['_database']->ErrorMsg()) {
					$this->error = "SQL Error creating support search page in Support::Schema::upgrade(): ".$GLOBALS['_database']->ErrorMsg();
					app_log($this->error,'error',__FILE__,__LINE__);
					$GLOBALS['_database']->RollbackTrans();
					return false;
				}

                $current_schema_version = 3;
                $update_schema_version = "
                    INSERT
                    INTO    support__info
                    VALUES  ('schema_version',$current_schema_version)
                    ON DUPLICATE KEY UPDATE
                        value = $current_schema_version
                ";
                $GLOBALS['_database']->Execute($update_schema_version);
                if ($GLOBALS['_database']->ErrorMsg()) {
                    $this->error = "SQL Error in Support::Schema::upgrade(): ".$GLOBALS['_database']->ErrorMsg();
                    app_log($this->error,'error',__FILE__,__LINE__);
                    $GLOBALS['_database']->RollbackTrans();
                    return undef;
                }
                $GLOBALS['_database']->CommitTrans();
            }

			# Add Roles
			foreach ($this->roles as $name => $description) {
				$role = new \Register\Role();
				if (! $role->get($name)) {
					app_log("Adding role '$name'");
					$role->add(array('name' => $name,'description' => $description));
				}
				if ($role->error) {
					$this->_error = "Error adding role '$name': ".$role->error;
					return false;
				}
				return true;
			}
		}
	}
?>
