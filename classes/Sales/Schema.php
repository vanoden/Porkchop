<?php
	namespace Sales;

	class Schema {
		public $errno;
		public $error;
		public $module = "sales";

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

				# Sales Orders
				$create_table_query = "
					CREATE TABLE IF NOT EXISTS `sales_orders` (
						id INT(4) NOT NULL AUTO_INCREMENT,
						salesperson_id INT(11) NOT NULL,
						status enum('NEW','QUOTE','CANCELLED','APPROVED','COMPLETE') NOT NULL DEFAULT 'NEW',
						customer_id INT(11) NOT NULL,
						PRIMARY KEY `pk_id` (`id`),
						FOREIGN KEY `fk_sp_id` (`salesperson_id`) REFERENCES `register_users` (`id`),
						FOREIGN KEY `fk_cust_id` (`customer_id`) REFERENCES `register_users` (`id`)
					)
				";
				$GLOBALS['_database']->Execute($create_table_query);
				if ($GLOBALS['_database']->ErrorMsg()) {
					$this->error = "SQL Error creating sales_orders table in Sales::Schema::upgrade(): ".$GLOBALS['_database']->ErrorMsg();
					app_log($this->error,'error',__FILE__,__LINE__);
					$GLOBALS['_database']->RollbackTrans();
					return false;
				}

				# Sales Order Events
				$create_table_query = "
					CREATE TABLE IF NOT EXISTS `sales_order_events` (
						id INT(11) NOT NULL AUTO_INCREMENT,
						order_id INT(11) NOT NULL,
						result_status enum('NEW','QUOTE','CANCELLED','APPROVED','COMPLETE') NOT NULL,
						user_id INT(11) NOT NULL,
						PRIMARY KEY `pk_id` (`id`),
						FOREIGN KEY `fk_order_id` (`order_id`) REFERENCES `sales_orders` (`id`),
						FOREIGN KEY `fk_user_id` (`user_id`) REFERENCES `register_users` (`id`)
					)
				";
				$GLOBALS['_database']->Execute($create_table_query);
				if ($GLOBALS['_database']->ErrorMsg()) {
					$this->error = "SQL Error creating sales_order_events table in Sales::Schema::upgrade(): ".$GLOBALS['_database']->ErrorMsg();
					app_log($this->error,'error',__FILE__,__LINE__);
					$GLOBALS['_database']->RollbackTrans();
					return false;
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
						FOREIGN KEY `fk_order_id` (`order_id`) REFERENCES `sales_orders` (`id`),
						FOREIGN KEY `fk_user_id` (`user_id`) REFERENCES `register_users` (`id`)
					)
				";
				$GLOBALS['_database']->Execute($create_table_query);
				if ($GLOBALS['_database']->ErrorMsg()) {
					$this->error = "SQL Error creating sales_order_events table in Sales::Schema::upgrade(): ".$GLOBALS['_database']->ErrorMsg();
					app_log($this->error,'error',__FILE__,__LINE__);
					$GLOBALS['_database']->RollbackTrans();
					return false;
				}

				$current_schema_version = 1;
				$update_schema_version = "
					INSERT
					INTO    ".$this->module."__info
					VALUES  ('schema_version',$current_schema_version)
					ON DUPLICATE KEY UPDATE
						value = $current_schema_version
				";
				$GLOBALS['_database']->Execute($update_schema_version);
				if ($GLOBALS['_database']->ErrorMsg()) {
					$this->error = "SQL Error in Sales::Schema::upgrade(): ".$GLOBALS['_database']->ErrorMsg();
					app_log($this->error,'error',__FILE__,__LINE__);
					$GLOBALS['_database']->RollbackTrans();
					return undef;
				}
				$GLOBALS['_database']->CommitTrans();
			}
		}
	}
