<?php
	namespace Sales;

	class Schema Extends \Database\BaseSchema {
		public $module = 'sales';
	
		public function upgrade($max_version = 999) {
			$this->error = null;

			if ($this->version() < 1) {
				app_log("Upgrading schema to version 1",'notice',__FILE__,__LINE__);

				# Start Transaction
				if (! $GLOBALS['_database']->BeginTrans())
					app_log("Transactions not supported",'warning',__FILE__,__LINE__);

				# Sales Orders
				$create_table_query = "
					CREATE TABLE IF NOT EXISTS `sales_orders` (
						id INT(4) NOT NULL AUTO_INCREMENT,
						code VARCHAR(255) NOT NULL,
						salesperson_id INT(11),
						status enum('NEW','QUOTE','CANCELLED','APPROVED','COMPLETE') NOT NULL DEFAULT 'NEW',
						customer_id INT(11) NOT NULL,
						PRIMARY KEY `pk_id` (`id`),
						UNIQUE KEY `uk_code` (`code`),
						FOREIGN KEY `fk_sp_id` (`salesperson_id`) REFERENCES `register_users` (`id`),
						FOREIGN KEY `fk_cust_id` (`customer_id`) REFERENCES `register_users` (`id`)
					)
				";
				if (! $this->executeSQL($create_table_query)) {
					$this->error = "SQL Error creating sales_orders table in ".$this->module."::Schema::upgrade(): ".$this->error;
					app_log($this->error, 'error');
					return false;
				}
				else {
					app_log("Created sales_orders table",'info');
				}

				# Sales Order Events
				$create_table_query = "
					CREATE TABLE IF NOT EXISTS `sales_order_events` (
						id INT(11) NOT NULL AUTO_INCREMENT,
						order_id INT(11) NOT NULL,
						type enum('CREATE','UPDATE','CANCEL','APPROVE','COMPLETE') NOT NULL,
						new_status enum('NEW','QUOTE','CANCELLED','APPROVED','COMPLETE') NOT NULL,
						`user_id` INT(11) NOT NULL,
						PRIMARY KEY `pk_id` (`id`),
						INDEX `idx_sales_order_user_id` (`user_id`,`type`),
						FOREIGN KEY `fk_sales_order_id` (`order_id`) REFERENCES `sales_orders` (`id`),
						FOREIGN KEY `fk_sales_order_user_id` (`user_id`) REFERENCES `register_users` (`id`)
					)
				";
				if (! $this->executeSQL($create_table_query)) {
					$this->error = "SQL Error creating sales_order_events table in ".$this->module."::Schema::upgrade(): ".$this->error;
					app_log($this->error, 'error');
					return false;
				}
				else {
					app_log("Created sales_order_events table",'info');
				}

				# Sales Order Items
				$create_table_query = "
					CREATE TABLE IF NOT EXISTS `sales_order_items` (
						id INT(11) NOT NULL AUTO_INCREMENT,
						order_id INT(11) NOT NULL,
						line_number INT(3) NOT NULL,
						product_id INT(11) NOT NULL,
						serial_number varchar(255),
						description text,
						quantity decimal(5,2) NOT NULL DEFAULT 1,
						unit_price decimal(11,2) NOT NULL DEFAULT 0,
						PRIMARY KEY `pk_id` (`id`),
						UNIQUE KEY `uk_sales_order_line` (`order_id`,`line_number`),
						FOREIGN KEY `fk_sales_order_item_id` (`order_id`) REFERENCES `sales_orders` (`id`),
						FOREIGN KEY `fk_sales_order_product_id` (`product_id`) REFERENCES `product_products` (`id`)
					)
				";
				if (! $this->executeSQL($create_table_query)) {
					$this->error = "SQL Error creating sales_order_items table in ".$this->module."::Schema::upgrade(): ".$this->error;
					app_log($this->error, 'error');
					return false;
				}
				else {
					app_log("Created sales_order_items table",'info');
				}

				app_log("Update version",'info');
				$this->setVersion(1);
				$GLOBALS['_database']->CommitTrans();
			}
			if ($this->version() < 2) {
				app_log("Upgrading schema to version 2",'notice',__FILE__,__LINE__);

				# Start Transaction
				if (! $GLOBALS['_database']->BeginTrans())
					app_log("Transactions not supported",'warning',__FILE__,__LINE__);

				# Sales Order Items
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
					$this->error = "SQL Error creating sales_currencies table in ".$this->module."::Schema::upgrade(): ".$this->error;
					app_log($this->error, 'error');
					return false;
				}
				else {
					app_log("Created sales_currencies table",'info');
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

				# Sales Order Items
				$alter_table_query = "
					ALTER TABLE `sales_order_items`
					ADD	COLUMN `status` enum('OPEN','VOID','FULFILLED','RETURNED') NOT NULL DEFAULT 'OPEN',
					ADD COLUMN `cost` decimal(10,4),
					ADD INDEX `idx_order_item_status` (`status`)
				";
				if (! $this->executeSQL($alter_table_query)) {
					$this->error = "SQL Error altering sales_order_items table in ".$this->module."::Schema::upgrade(): ".$this->error;
					app_log($this->error, 'error');
					return false;
				}
				else {
					app_log("Updated sales_order_items table",'info');
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

				# Sales Order Items
				$alter_table_query = "
					ALTER TABLE `sales_orders`
					ADD	COLUMN `customer_order_number` varchar(255),
					ADD INDEX `idx_customer_order_num` (`customer_id`,`customer_order_number`)
				";
				if (! $this->executeSQL($alter_table_query)) {
					$this->error = "SQL Error altering sales_orders table in ".$this->module."::Schema::upgrade(): ".$this->error;
					app_log($this->error, 'error');
					return false;
				}
				else {
					app_log("Updated sales_orders table",'info');
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

				# Sales Order Items
				$alter_table_query = "
					ALTER TABLE `sales_orders`
					ADD	COLUMN `organization_id` int(11),
					ADD INDEX `idx_order_org_id` (`organization_id`,`status`)
				";
				if (! $this->executeSQL($alter_table_query)) {
					$this->error = "SQL Error altering sales_orders table in ".$this->module."::Schema::upgrade(): ".$this->error;
					app_log($this->error, 'error');
					return false;
				}
				else {
					app_log("Updated sales_orders table",'info');
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

				# Sales Order Items
				$alter_table_query = "
					ALTER TABLE `sales_order_events`
					ADD	COLUMN `message` text(512)
				";
				if (! $this->executeSQL($alter_table_query)) {
					$this->error = "SQL Error altering sales_order_events table in ".$this->module."::Schema::upgrade(): ".$this->error;
					app_log($this->error, 'error');
					return false;
				}
				else {
					app_log("Updated sales_order_events table",'info');
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

				# Sales Order Items
				$alter_table_query = "
					ALTER TABLE `sales_order_events`
					ADD	COLUMN `date_event` datetime
				";
				if (! $this->executeSQL($alter_table_query)) {
					$this->error = "SQL Error altering sales_order_events table in ".$this->module."::Schema::upgrade(): ".$this->error;
					app_log($this->error, 'error');
					return false;
				}
				else {
					app_log("Updated sales_order_events table",'info');
				}

				app_log("Update version",'info');
				$this->setVersion(7);
				$GLOBALS['_database']->CommitTrans();
			}
			
            if ($this->version() < 8) {

				app_log("Upgrading schema to version 8",'notice',__FILE__,__LINE__);

				// Start Transaction
				if (! $GLOBALS['_database']->BeginTrans()) app_log("Transactions not supported",'warning',__FILE__,__LINE__);

				$table = new \Database\Schema\Table('sales_orders');
				
				if (! $table->has_column('billing_location_id')) {
					$alter_table_query = "ALTER TABLE `sales_orders` ADD COLUMN `billing_location_id` int NULL";
					if (! $this->executeSQL($alter_table_query)) {
						$this->error = "SQL Error altering `sales_orders` table in ".$this->module."::Schema::upgrade(): ".$this->error;
						app_log($this->error, 'error');
						return false;
					}
					
					$alter_table_query = "ALTER TABLE `sales_orders` ADD CONSTRAINT `sales_orders_ibfk_3` FOREIGN KEY (`billing_location_id`) REFERENCES `register_locations` (`id`);";
					if (! $this->executeSQL($alter_table_query)) {
						$this->error = "SQL Error altering `sales_orders` table in ".$this->module."::Schema::upgrade(): ".$this->error;
						app_log($this->error, 'error');
						return false;
					}
				}

				if (! $table->has_column('shipping_location_id')) {
					$alter_table_query = "ALTER TABLE `sales_orders` ADD COLUMN `shipping_location_id` int NULL;";
					if (! $this->executeSQL($alter_table_query)) {
						$this->error = "SQL Error altering `sales_orders` table in ".$this->module."::Schema::upgrade(): ".$this->error;
						app_log($this->error, 'error');
						return false;
					}
					$alter_table_query = "ALTER TABLE `sales_orders` ADD CONSTRAINT `sales_orders_ibfk_4` FOREIGN KEY (`shipping_location_id`) REFERENCES `register_locations` (`id`);";
					if (! $this->executeSQL($alter_table_query)) {
						$this->error = "SQL Error altering `sales_orders` table in ".$this->module."::Schema::upgrade(): ".$this->error;
						app_log($this->error, 'error');
						return false;
					}
				}
				
				$this->setVersion(8);
				$GLOBALS['_database']->CommitTrans();
			}
			return true;
		}
	}
