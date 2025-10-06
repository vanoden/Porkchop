<?php
	namespace Sales;

	class Schema Extends \Database\BaseSchema {
		public function __construct() {
			$this->module = 'sales';
			parent::__construct();
		}

		public function upgrade($max_version = 999) {
			$this->clearError();

			if ($this->version() < 1) {
				app_log("Upgrading schema to version 1",'notice',__FILE__,__LINE__);

				# Start Transaction
				if (! $GLOBALS['_database']->BeginTrans())
					app_log("Transactions not supported",'warning',__FILE__,__LINE__);

				# Sales Document Items
				$create_table_query = "
					CREATE TABLE IF NOT EXISTS `sales_currencies` (
						id INT(11) NOT NULL AUTO_INCREMENT,
						name varchar(256) NOT NULL,
						symbol char(1),
						PRIMARY KEY `pk_id` (`id`),
						UNIQUE KEY `uk_sales_currency_name` (`name`)
					)
				";
				if (! $this->executeSQL($create_table_query)) {
					$this->SQLError("Creating sales_currencies table in ".$this->module."::Schema::upgrade(): ".$this->error());
					return false;
				}
				else {
					app_log("Created sales_currencies table",'info');
				}

				# Sales Documents
				$create_table_query = "
					CREATE TABLE IF NOT EXISTS `sales_documents` (
						id INT(4) NOT NULL AUTO_INCREMENT,
						code VARCHAR(255) NOT NULL,
						salesperson_id INT(11),
						status enum('NEW','QUOTE','CANCELLED','APPROVED','COMPLETE') NOT NULL DEFAULT 'NEW',
						type enum('SALES_ORDER','PURCHASE_ORDER','RETURN_ORDER','INVENTORY_ADJUSTMENT') NOT NULL DEFAULT 'SALES_ORDER',
						local_order_number VARCHAR(255),
						remote_order_number VARCHAR(255),
						customer_id INT(11) NOT NULL,
						vendor_id INT(11),
						currency_id INT(11),
						PRIMARY KEY `pk_id` (`id`),
						UNIQUE KEY `uk_code` (`code`),
						FOREIGN KEY `fk_sp_id` (`salesperson_id`) REFERENCES `register_users` (`id`),
						FOREIGN KEY `fk_cust_id` (`customer_id`) REFERENCES `register_users` (`id`)
					)
				";
				if (! $this->executeSQL($create_table_query)) {
					$this->SQLError("Creating sales_orders table in ".$this->module."::Schema::upgrade(): ".$this->error());
					return false;
				}
				else {
					app_log("Created sales_orders table",'info');
				}

				# Sales Document Events
				$create_table_query = "
					CREATE TABLE IF NOT EXISTS `sales_document_events` (
						id INT(11) NOT NULL AUTO_INCREMENT,
						document_id INT(11) NOT NULL,
						type enum('CREATE','UPDATE','CANCEL','APPROVE','COMPLETE') NOT NULL,
						new_status enum('NEW','QUOTE','CANCELLED','APPROVED','COMPLETE') NOT NULL,
						`user_id` INT(11) NOT NULL,
						PRIMARY KEY `pk_id` (`id`),
						INDEX `idx_sales_order_user_id` (`user_id`,`type`),
						FOREIGN KEY `fk_sales_order_id` (`document_id`) REFERENCES `sales_documents` (`id`),
						FOREIGN KEY `fk_sales_order_user_id` (`user_id`) REFERENCES `register_users` (`id`)
					)
				";
				if (! $this->executeSQL($create_table_query)) {
					$this->SQLError("Creating sales_document_events table in ".$this->module."::Schema::upgrade(): ".$this->error());
					return false;
				}
				else {
					app_log("Created sales_document_events table",'info');
				}

				# Sales Document Items
				$create_table_query = "
					CREATE TABLE IF NOT EXISTS `sales_document_items` (
						id INT(11) NOT NULL AUTO_INCREMENT,
						document_id INT(11) NOT NULL,
						line_number INT(3) NOT NULL,
						product_id INT(11) NOT NULL,
						serial_number varchar(255),
						description text,
						quantity decimal(5,2) NOT NULL DEFAULT 1,
						unit_price decimal(11,2) NOT NULL DEFAULT 0,
						PRIMARY KEY `pk_id` (`id`),
						UNIQUE KEY `uk_sales_document_line` (`document_id`,`line_number`),
						FOREIGN KEY `fk_sales_document_item_id` (`document_id`) REFERENCES `sales_documents` (`id`),
						FOREIGN KEY `fk_sales_document_product_id` (`product_id`) REFERENCES `product_products` (`id`)
					)
				";
				if (! $this->executeSQL($create_table_query)) {
					$this->SQLError("Creating sales_document_items table in ".$this->module."::Schema::upgrade(): ".$this->error());
					return false;
				}
				else {
					app_log("Created sales_document_items table",'info');
				}

				app_log("Update version",'info');
				$this->setVersion(2);
				$GLOBALS['_database']->CommitTrans();
			}
			if ($this->version() < 3) {
				app_log("Upgrading schema to version 3",'notice',__FILE__,__LINE__);

				# Start Transaction
				if (! $GLOBALS['_database']->BeginTrans())
					app_log("Transactions not supported",'warning',__FILE__,__LINE__);

				# Sales Document Items
				$alter_table_query = "
					ALTER TABLE `sales_document_items`
					ADD	COLUMN `status` enum('OPEN','VOID','FULFILLED','RETURNED') NOT NULL DEFAULT 'OPEN',
					ADD COLUMN `cost` decimal(10,4),
					ADD INDEX `idx_document_item_status` (`status`)
				";
				if (! $this->executeSQL($alter_table_query)) {
					$this->SQLError("Altering sales_document_items table in ".$this->module."::Schema::upgrade(): ".$this->error());
					return false;
				}
				else {
					app_log("Updated sales_document_items table",'info');
				}

				app_log("Update version",'info');
				$this->setVersion(3);
				$GLOBALS['_database']->CommitTrans();
			}
			if ($this->version() < 4) {
				app_log("Upgrading schema to version 4",'notice',__FILE__,__LINE__);

				# Start Transaction
				if (! $GLOBALS['_database']->BeginTrans())
					app_log("Transactions not supported",'warning',__FILE__,__LINE__);

				# Sales Document Items
				$alter_table_query = "
					ALTER TABLE `sales_documents`
					ADD	COLUMN `customer_order_number` varchar(255),
					ADD INDEX `idx_customer_order_num` (`customer_id`,`customer_order_number`)
				";
				if (! $this->executeSQL($alter_table_query)) {
					$this->SQLError("Altering sales_orders table in ".$this->module."::Schema::upgrade(): ".$this->error());
					return false;
				}
				else {
					app_log("Updated sales_documents table",'info');
				}

				app_log("Update version",'info');
				$this->setVersion(4);
				$GLOBALS['_database']->CommitTrans();
			}
			if ($this->version() < 5) {
				app_log("Upgrading schema to version 5",'notice',__FILE__,__LINE__);

				# Start Transaction
				if (! $GLOBALS['_database']->BeginTrans())
					app_log("Transactions not supported",'warning',__FILE__,__LINE__);

				# Sales Document Items
				$alter_table_query = "
					ALTER TABLE `sales_documents`
					ADD	COLUMN `organization_id` int(11),
					ADD INDEX `idx_order_org_id` (`organization_id`,`status`)
				";
				if (! $this->executeSQL($alter_table_query)) {
					$this->SQLError("Altering sales_documents table in ".$this->module."::Schema::upgrade(): ".$this->error());
					return false;
				}
				else {
					app_log("Updated sales_documents table",'info');
				}

				app_log("Update version",'info');
				$this->setVersion(5);
				$GLOBALS['_database']->CommitTrans();
			}
			if ($this->version() < 6) {
				app_log("Upgrading schema to version 6",'notice',__FILE__,__LINE__);

				# Start Transaction
				if (! $GLOBALS['_database']->BeginTrans())
					app_log("Transactions not supported",'warning',__FILE__,__LINE__);

				# Sales Document Items
				$alter_table_query = "
					ALTER TABLE `sales_document_events`
					ADD	COLUMN `message` text(512)
				";
				if (! $this->executeSQL($alter_table_query)) {
					$this->SQLError("Altering sales_document_events table in ".$this->module."::Schema::upgrade(): ".$this->error());
					return false;
				}
				else {
					app_log("Updated sales_document_events table",'info');
				}

				app_log("Update version",'info');
				$this->setVersion(6);
				$GLOBALS['_database']->CommitTrans();
			}
			if ($this->version() < 7) {
				app_log("Upgrading schema to version 7",'notice',__FILE__,__LINE__);

				# Start Transaction
				if (! $GLOBALS['_database']->BeginTrans())
					app_log("Transactions not supported",'warning',__FILE__,__LINE__);

				# Sales Document Items
				$alter_table_query = "
					ALTER TABLE `sales_document_events`
					ADD	COLUMN `date_event` datetime
				";
				if (! $this->executeSQL($alter_table_query)) {
					$this->SQLError("Altering sales_document_events table in ".$this->module."::Schema::upgrade(): ".$this->error());
					return false;
				}
				else {
					app_log("Updated sales_document_events table",'info');
				}

				app_log("Update version",'info');
				$this->setVersion(7);
				$GLOBALS['_database']->CommitTrans();
			}
			
            if ($this->version() < 8) {

				app_log("Upgrading schema to version 8",'notice',__FILE__,__LINE__);

				// Start Transaction
				if (! $GLOBALS['_database']->BeginTrans()) app_log("Transactions not supported",'warning',__FILE__,__LINE__);

				$table = new \Database\Schema\Table('sales_documents');
				
				if (! $table->has_column('billing_location_id')) {
					$alter_table_query = "ALTER TABLE `sales_documents` ADD COLUMN `billing_location_id` int NULL";
					if (! $this->executeSQL($alter_table_query)) {
						$this->SQLError("Altering `sales_documents` table in ".$this->module."::Schema::upgrade(): ".$this->error());
						return false;
					}

					$alter_table_query = "ALTER TABLE `sales_documents` ADD CONSTRAINT `sales_documents_ibfk_3` FOREIGN KEY (`billing_location_id`) REFERENCES `register_locations` (`id`);";
					if (! $this->executeSQL($alter_table_query)) {
						$this->SQLError("Altering `sales_documents` table in ".$this->module."::Schema::upgrade(): ".$this->error());
						return false;
					}
				}

				if (! $table->has_column('shipping_location_id')) {
					$alter_table_query = "ALTER TABLE `sales_documents` ADD COLUMN `shipping_location_id` int NULL;";
					if (! $this->executeSQL($alter_table_query)) {
						$this->SQLError("Altering `sales_documents` table in ".$this->module."::Schema::upgrade(): ".$this->error());
						return false;
					}

					$alter_table_query = "ALTER TABLE `sales_documents` ADD CONSTRAINT `sales_documents_ibfk_4` FOREIGN KEY (`shipping_location_id`) REFERENCES `register_locations` (`id`);";
					if (! $this->executeSQL($alter_table_query)) {
						$this->SQLError("Altering `sales_documents` table in ".$this->module."::Schema::upgrade(): ".$this->error());
						return false;
					}
				}
				
				$this->setVersion(8);
				$GLOBALS['_database']->CommitTrans();
			}

            if ($this->version() < 9) {

				app_log("Upgrading schema to version 9",'notice',__FILE__,__LINE__);

				// Start Transaction
				if (! $GLOBALS['_database']->BeginTrans()) app_log("Transactions not supported",'warning',__FILE__,__LINE__);
			    $table = new \Database\Schema\Table('sales_documents');				    
                $alter_table_query = "ALTER TABLE `sales_documents` MODIFY COLUMN `status` ENUM('NEW','QUOTE','CANCELLED','APPROVED','ACCEPTED','COMPLETE');";
                if (! $this->executeSQL($alter_table_query)) {
                    $this->SQLError("Altering `sales_documents` table in ".$this->module."::Schema::upgrade(): ".$this->error());
                    return false;
                }
				$this->setVersion(9);
				$GLOBALS['_database']->CommitTrans();
			}

            if ($this->version() < 10) {

				app_log("Upgrading schema to version 10",'notice',__FILE__,__LINE__);

				// Start Transaction
				if (! $GLOBALS['_database']->BeginTrans()) app_log("Transactions not supported",'warning',__FILE__,__LINE__);

                $alter_table_query = "ALTER TABLE `sales_documents` ADD `shipping_vendor_id` int(11)";
                if (! $this->executeSQL($alter_table_query)) {
                    $this->SQLError("Altering `sales_documents` table in ".$this->module."::Schema::upgrade(): ".$this->error());
                    return false;
                }
				$this->setVersion(10);
				$GLOBALS['_database']->CommitTrans();

			}

			if ($this->version() < 11) {
				app_log("Upgrading schema to version " . ($this->version()+1),'notice',__FILE__,__LINE__);

				# Start Transaction
				if (! $GLOBALS['_database']->BeginTrans())
					app_log("Transactions not supported",'warning',__FILE__,__LINE__);

				$alter_table_query = "
					ALTER TABLE `sales_documents`
					ADD	COLUMN `document_type` enum('SALES_ORDER','PURCHASE_ORDER','RETURN_ORDER','INVENTORY_ADJUSTMENT') NOT NULL DEFAULT 'SALES_ORDER'
				";

				if (! $this->executeSQL($alter_table_query)) {
					$this->SQLError("Altering sales_documents table in ".$this->module."::Schema::upgrade(): ".$this->error());
					return false;
				}
				else {
					app_log("Updated sales_documents table",'info');
				}
				app_log("Update version",'info');
				$this->setVersion($this->version()+1);
				$GLOBALS['_database']->CommitTrans();
			}
			return true;
		}
	}
