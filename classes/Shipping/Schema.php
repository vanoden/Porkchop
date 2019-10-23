<?php
	namespace Shipping;

	class Schema {
		public $errno;
		public $error;
		public $module = "shipping";

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
					CREATE TABLE IF NOT EXISTS `shipping_shipments` (
						id INT(11) NOT NULL AUTO_INCREMENT,
						document_id varchar(255) NOT NULL,
						date_entered datetime,
						date_shipped datetime,
						status enum('NEW','SHIPPED','LOST','RECEIVED','RETURNED')
						send_contact_id INT(11) NOT NULL,
						send_location_id INT(11) NOT NULL,
						rec_contact_id INT(11) NOT NULL,
						rec_location_id INT(11) NOT NULL,
						vendor_id INT(11) NOT NULL,
						PRIMARY KEY `pk_id` (`id`),
						KEY `idx_document` (`document_id`),
						FOREIGN KEY `fk_sender` (`send_contact_id`) REFERENCES `register_users` (`id`),
						FOREIGN KEY `fk_receiver` (`rec_contact_id`) REFERENCES `register_users` (`id`),
						FOREIGN KEY `fk_send_from` (`send_location_id`) REFERENCES `register_locations` (`id`),
						FOREIGN KEY `fk_send_to` (`rec_location_id`) REFERENCES `register_locations` (`id`),
						FOREIGN KEY `fk_vendor_id` (`vendor_id`) REFERENCES `product_vendors` (`id`),
						INDEX `idx_date` (`date_request`),
						INDEX `idx_status` (`status`,`date_request`)
					)
				";
				$GLOBALS['_database']->Execute($create_table_query);
				if ($GLOBALS['_database']->ErrorMsg()) {
					$this->error = "SQL Error creating shipping_shipments table in Shipping::Schema::upgrade(): ".$GLOBALS['_database']->ErrorMsg();
					app_log($this->error,'error',__FILE__,__LINE__);
					$GLOBALS['_database']->RollbackTrans();
					return false;
				}

				# Items requiring action
				$create_table_query = "
					CREATE TABLE IF NOT EXISTS `shipping_packages` (
						id INT(11) NOT NULL AUTO_INCREMENT,
						shipment_id INT(11) NOT NULL,
						number INT(11) NOT NULL,
						tracking_code varchar(255) NOT NULL,
						status enum('SHIPPED','RECEIVED','RETURNED'),
						condition enum('OK','DAMAGED'),
						height decimal(6,2) NOT NULL DEFAULT 0,
						width decimal(6,2) NOT NULL DEFAULT 0,
						depth decimal(6,2) NOT NULL DEFAULT 0,
						weight decimal(6,2) NOT NULL DEFAULT 0,
						shipping_cost decimal(6,2 NOT NULL DEFAULT 0,
						date_received datetime(),
						user_received int(11),
						PRIMARY KEY `pk_id` (`id`),
						UNIQUE KEY `uk_line` (`shipment_id`,`number`),
						FOREIGN KEY `fk_shipment_id` (`shipment_id`) REFERENCES `shipping_shipments` (`id`),
						KEY `idx_tracking_code` (`shipment_id`,`tracking_code`)
					)
				";
				$GLOBALS['_database']->Execute($create_table_query);
				if ($GLOBALS['_database']->ErrorMsg()) {
					$this->error = "SQL Error creating shipping_packages table in Shipping::Schema::upgrade(): ".$GLOBALS['_database']->ErrorMsg();
					app_log($this->error,'error',__FILE__,__LINE__);
					$GLOBALS['_database']->RollbackTrans();
					return false;
				}

				# Things We Have To Do
				$create_table_query = "
					CREATE TABLE IF NOT EXISTS `shipping_items` (
						id INT(11) NOT NULL AUTO_INCREMENT,
						package_id INT(11),
						product_id INT(11) NOT NULL,
						serial_number VARCHAR(255),
						condition enum('OK','DAMAGED'),
						quantity INT(11) NOT NULL,
						PRIMARY KEY `pk_id` (`id`),
						FOREIGN KEY `fk_product` (`product_id`) REFERENCES `product_products` (`id`)
					)
				";
				$GLOBALS['_database']->Execute($create_table_query);
				if ($GLOBALS['_database']->ErrorMsg()) {
					$this->error = "SQL Error creating shipping_items table in Shipping::Schema::upgrade(): ".$GLOBALS['_database']->ErrorMsg();
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
					$this->error = "SQL Error in Shipping::Schema::upgrade(): ".$GLOBALS['_database']->ErrorMsg();
					app_log($this->error,'error',__FILE__,__LINE__);
					$GLOBALS['_database']->RollbackTrans();
					return undef;
				}
				$GLOBALS['_database']->CommitTrans();
			}
		}
	}
