<?php
	namespace Support;

	class Schema Extends \Database\BaseSchema {
		public $module = "support";

		public function upgrade() {
			$this->error = null;
			
			if ($this->version() < 1) {
                app_log("Upgrading schema to version 1", 'notice', __FILE__, __LINE__);

                # Start Transaction
                if (!$GLOBALS['_database']->BeginTrans())
                    app_log("Transactions not supported", 'warning', __FILE__, __LINE__);

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
				if (! $this->executeSQL($create_table_query)) {
					$this->error = "SQL Error creating support_requests table in ".$this->module."::Schema::upgrade(): ".$this->error;
					app_log($this->error, 'error');
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
				if (! $this->executeSQL($create_table_query)) {
					$this->error = "SQL Error creating support_request_items table in ".$this->module."::Schema::upgrade(): ".$this->error;
					app_log($this->error, 'error');
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
				if (! $this->executeSQL($create_table_query)) {
					$this->error = "SQL Error creating support_item_actions table in ".$this->module."::Schema::upgrade(): ".$this->error;
					app_log($this->error, 'error');
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
				if (! $this->executeSQL($create_table_query)) {
					$this->error = "SQL Error creating support_action_events table in ".$this->module."::Schema::upgrade(): ".$this->error;
					app_log($this->error, 'error');
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
				if (! $this->executeSQL($create_table_query)) {
					$this->error = "SQL Error creating support_parts table in ".$this->module."::Schema::upgrade(): ".$this->error;
					app_log($this->error, 'error');
					return false;
				}

				$this->setVersion(1);
				$GLOBALS['_database']->CommitTrans();
            }
            
			if ($this->version() < 2) {
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
				if (! $this->executeSQL($create_table_query)) {
					$this->error = "SQL Error creating support_rmas table in ".$this->module."::Schema::upgrade(): ".$this->error;
					app_log($this->error, 'error');
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
				if (! $this->executeSQL($create_table_query)) {
					$this->error = "SQL Error creating support_item_comments table in ".$this->module."::Schema::upgrade(): ".$this->error;
					app_log($this->error, 'error');
					return false;
				}

				$this->setVersion(2);
				$GLOBALS['_database']->CommitTrans();
            }
            
            // update to schema 4 (new product registration -> /_support/register_product)
            if ($this->version() < 4) {
            
                app_log("Upgrading schema to version 4",'notice',__FILE__,__LINE__);

                // Start Transaction
                if (! $GLOBALS['_database']->BeginTrans()) app_log("Transactions not supported",'warning',__FILE__,__LINE__);
                
				// product warranty page table
                $create_table_query = "	
                    CREATE TABLE `product_registration_queue` (
                      `id` int(11) NOT NULL AUTO_INCREMENT,
                      `customer_id` int(11) DEFAULT NULL,
                      `product_id` int(11) NOT NULL,
                      `serial_number` varchar(255) DEFAULT NULL,
                      `status` enum('PENDING','APPROVED','DENIED') DEFAULT 'PENDING',
                      `date_created` datetime DEFAULT NULL,
                      `date_purchased` datetime NOT NULL,
                      `distributor_name` varchar(255) DEFAULT NULL,
                      `notes` text,
                      PRIMARY KEY (`id`),
                      KEY `FK_CUSTOMER_ID` (`customer_id`),
                      KEY `idx_serial` (`product_id`,`serial_number`),
                      CONSTRAINT `product_registration_queue_ibfk_1` FOREIGN KEY (`product_id`) REFERENCES `product_products` (`id`),
                      CONSTRAINT `product_registration_queue_ibfk_2` FOREIGN KEY (`customer_id`) REFERENCES `register_users` (`id`)
                    ) ENGINE=InnoDB DEFAULT CHARSET=latin1;
                ";
				if (! $this->executeSQL($create_table_query)) {
					$this->error = "SQL Error creating product_registration_queue table in ".$this->module."::Schema::upgrade(): ".$this->error;
					app_log($this->error, 'error');
					return false;
				}

				$this->setVersion(4);
				$GLOBALS['_database']->CommitTrans();
            }

            if ($this->version() < 5) {
                app_log("Upgrading schema to version 5",'notice',__FILE__,__LINE__);

                // Start Transaction
                if (! $GLOBALS['_database']->BeginTrans()) app_log("Transactions not supported",'warning',__FILE__,__LINE__);

				// product warranty page table
                $alter_table_query = "	
                    ALTER TABLE `support_rmas` add document_id int(11)
                ";
				if (! $this->executeSQL($alter_table_query)) {
					$this->error = "SQL Error altering support_rmas table in ".$this->module."::Schema::upgrade(): ".$this->error;
					app_log($this->error, 'error');
					return false;
				}

				$this->setVersion(5);
				$GLOBALS['_database']->CommitTrans();
            }

            if ($this->version() < 6) {
                app_log("Upgrading schema to version 6",'notice',__FILE__,__LINE__);

                // Start Transaction
                if (! $GLOBALS['_database']->BeginTrans()) app_log("Transactions not supported",'warning',__FILE__,__LINE__);

				// product warranty page table
                $alter_table_query = "	
                    ALTER TABLE `support_rmas` modify status enum ('NEW','ACCEPTED','PRINTED','CLOSED')
                ";
				if (! $this->executeSQL($alter_table_query)) {
					$this->error = "SQL Error altering support_rmas table in ".$this->module."::Schema::upgrade(): ".$this->error;
					app_log($this->error, 'error');
					return false;
				}

				$this->setVersion(6);
				$GLOBALS['_database']->CommitTrans();
            }

			if ($this->version() < 7) {
				app_log("Upgrading schema to version 7",'notice',__FILE__,__LINE__);

				// Start Transaction
				if (! $GLOBALS['_database']->BeginTrans()) app_log("Transactions not supported",'warning',__FILE__,__LINE__);

				// product warranty page table
				$alter_table_query = "	
					ALTER TABLE `support_rmas` add column billing_contact_id int(11)
				";
				if (! $this->executeSQL($alter_table_query)) {
					$this->error = "SQL Error altering support_rmas table in ".$this->module."::Schema::upgrade(): ".$this->error;
					app_log($this->error, 'error');
					return false;
				}

				$this->setVersion(6);
				$GLOBALS['_database']->CommitTrans();
			}

			$this->addRoles(array(
				'support manager'	=> 'Full control over requests, actions, etc',
				'support user'		=> 'Can work with support requests'
			));
		}
	}
