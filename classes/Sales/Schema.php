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
						salesperson_id INT(11) NOT NULL,
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
			return true;
		}
	}
