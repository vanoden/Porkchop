<?php
	namespace Product;

	class Schema Extends \Database\BaseSchema {

		public $module = 'product';

		public $error;
	
		public function upgrade($max_version = 999) {
			$this->clearError();

			if ($this->version() < 1) {
				app_log("Upgrading schema to version 1",'notice',__FILE__,__LINE__);

				# Start Transaction
				if (!$GLOBALS['_database']->BeginTrans())
					app_log("Transactions not supported",'warning',__FILE__,__LINE__);

				$create_table_query = "
					CREATE TABLE IF NOT EXISTS `product_products` (
						`id` 			int(11) NOT NULL AUTO_INCREMENT,
						`code` 			varchar(45) NOT NULL,
						`name`			varchar(255),
						`description`	text,
						`type`			enum('group','kit','inventory','unique') DEFAULT 'inventory',
						`status`		enum('ACTIVE','HIDDEN','DELETED') NOT NULL DEFAULT 'ACTIVE',
						PRIMARY KEY (`id`),
						UNIQUE KEY `uk_product_code` (`code`)
					)
				";
				if (! $this->executeSQL($create_table_query)) {
					$this->error("SQL Error creating product_products table in ".$this->module."::Schema::upgrade(): ".$this->error());
					app_log($this->error(), 'error');
					return false;
				}

				# Media Items Table Required
				$media_schema = new \Media\Schema;
				if (! $media_schema->upgrade()) {
					$this->error("Cannot upgrade Media Schema: ".$media_schema->error());
					return false;
				}
				$create_table_query = "
					CREATE TABLE IF NOT EXISTS `product_images` (
						`product_id`	int(11) NOT NULL,
						`image_id`		int(11) NOT NULL,
						`label`			varchar(100) NOT NULL,
						PRIMARY KEY `PK_PRODUCT_IMAGE` (`product_id`,`image_id`),
						FOREIGN KEY `FK_PRODUCT_IMAGE_ID` (`product_id`) REFERENCES `product_products` (`id`),
						FOREIGN KEY `FK_IMAGE_ID` (`image_id`) REFERENCES `media_items` (`id`)
					)
				";
				if (! $this->executeSQL($create_table_query)) {
					$this->error("SQL Error creating product_images table in ".$this->module."::Schema::upgrade(): ".$this->error());
					app_log($this->error(), 'error');
					return false;
				}

				$create_table_query = "
					CREATE TABLE IF NOT EXISTS `product_relations` (
						`product_id`	int(11) NOT NULL,
						`parent_id`		int(11) NOT NULL,
						`view_order`	int(3) NOT NULL DEFAULT 0,
						PRIMARY KEY (`product_id`,`parent_id`),
						FOREIGN KEY `FK_PRODUCT_ID` (`product_id`) REFERENCES `product_products` (`id`)
					)
				";
				if (! $this->executeSQL($create_table_query)) {
					$this->SQLError("Creating product_relations table: ".$this->error());
					return false;
				}

				$create_table_query = "
					CREATE TABLE IF NOT EXISTS `product_metadata` (
						`id`			int(11)	NOT NULL AUTO_INCREMENT,
						`product_id`	int(11) NOT NULL,
						`key`			varchar(32) NOT NULL,
						`value`			text,
						PRIMARY KEY `PK_ID` (`id`),
						UNIQUE KEY (`product_id`,`key`),
						FOREIGN KEY `FK_METADATA_PRODUCT_ID` (`product_id`) REFERENCES `product_products` (`id`)
					)
				";
				if (! $this->executeSQL($create_table_query)) {
					$this->SQLError("Creating product_metadata table: ".$this->error());
					return false;
				}

				$this->setVersion(1);
				$GLOBALS['_database']->CommitTrans();
			}
			if ($this->version() < 2 and $max_version >= 2) {
				app_log("Upgrading schema to version 2",'notice',__FILE__,__LINE__);

				# Start Transaction
				if (! $GLOBALS['_database']->BeginTrans())
					app_log("Transactions not supported",'warning',__FILE__,__LINE__);

				$create_table_query = "
					CREATE TABLE IF NOT EXISTS `product_vendors` (
						`id` 			int(11) NOT NULL AUTO_INCREMENT,
						`code` 			varchar(45) NOT NULL,
						`name`			varchar(255),
						`account_number`	varchar(255),
						`status`		enum('ACTIVE','HIDDEN','DELETED') NOT NULL DEFAULT 'ACTIVE',
						`notes`			text,
						PRIMARY KEY `pk_vendor_id` (`id`),
						UNIQUE KEY `uk_vendor_code` (`code`)
					)
				";
				if (! $this->executeSQL($create_table_query)) {
					$this->SQLError("Creating product_vendors table: ".$this->error());
					return false;
				}

				$create_table_query = "
					CREATE TABLE IF NOT EXISTS `product_vendor_locations` (
						`vendor_id`		int(11) NOT NULL,
						`location_id` 	int(11) NOT NULL,
						PRIMARY KEY `pk_vendor_location` (`vendor_id`,`location_id`),
						FOREIGN KEY `fk_vendor_id` (`vendor_id`) REFERENCES `product_vendors` (`id`),
						FOREIGN KEY `fk_location_id` (`location_id`) REFERENCES `register_locations` (`id`)
					)
				";
				if (! $this->executeSQL($create_table_query)) {
					$this->SQLError("Creating product_vendor_locations table: ".$this->error());
					return false;
				}

				$create_table_query = "
					CREATE TABLE IF NOT EXISTS `product_vendor_items` (
						`vendor_id`		int(11) NOT NULL,
						`product_id`	int(11) NOT NULL,
						`cost`			decimal(8,2) NOT NULL DEFAULT 0,
						`delivery_time`	int(11) NOT NULL DEFAULT 0,
						`minimum_order`	int(5) NOT NULL DEFAULT 0,
						`vendor_sku`	varchar(255),
						UNIQUE KEY `UK_VENDOR_ITEM` (`vendor_id`,`product_id`),
						KEY `IDX_PRODUCT` (`product_id`),
						FOREIGN KEY `FK_VENDOR_PRODUCT_ID` (`product_id`) REFERENCES `product_products` (`id`),
						FOREIGN KEY `FK_PRODUCT_VENDOR_ID` (`vendor_id`) REFERENCES `product_vendors` (`id`)
					)
				";
				if (! $this->executeSQL($create_table_query)) {
					$this->error("SQL Error creating product_vendor_items table in ".$this->module."::Schema::upgrade(): ".$this->error());
					app_log($this->error(), 'error');
					return false;
				}

				$alter_table_query = "
					ALTER TABLE `product_products`
					ADD 	`on_hand`			decimal(10,2) NOT NULL DEFAULT 0,
					ADD 	`default_vendor`	INT(11),
					ADD		`min_quantity`		decimal(10,2) NOT NULL DEFAULT 0,
					ADD		`max_quantity` 		decimal(10,2),
					ADD		`total_purchased`	decimal(10,2) NOT NULL DEFAULT 0,
					ADD		`total_cost`		decimal(10,2) NOT NULL DEFAULT 0
				";
				if (! $this->executeSQL($alter_table_query)) {
					$this->error("SQL Error altering product_products table in ".$this->module."::Schema::upgrade(): ".$this->error());
					app_log($this->error(), 'error');
					return false;
				}

				$this->setVersion(2);
				$GLOBALS['_database']->CommitTrans();
			}
			if ($this->version() < 3 and $max_version >= 3) {
				app_log("Upgrading schema to version 3",'notice',__FILE__,__LINE__);

				# Start Transaction
				if (! $GLOBALS['_database']->BeginTrans())
					app_log("Transactions not supported",'warning',__FILE__,__LINE__);

				// Sales Schema must be ready for Foreign Key
				$sales_schema = new \Sales\Schema();
				$sales_schema->upgrade();

				$create_table_query = "
					CREATE TABLE IF NOT EXISTS `product_prices` (
						id			int(11) NOT NULL,
						product_id	int(11) NOT NULL,
						amount		decimal(10,2) default 0,
						date_active	datetime NOT NULL,
						status		enum('ACTIVE','INACTIVE') NOT NULL default 'INACTIVE',
						currency_id	int(11) NOT NULL,
						PRIMARY KEY `pk_price_id` (`id`),
						KEY `idx_price` (`product_id`,`status`,`date_active`),
						FOREIGN KEY `FK_PRODUCT_PRICE` (`product_id`) REFERENCES `product_products` (`id`),
						FOREIGN KEY `FK_PRICE_CURRENCY` (`currency_id`) REFERENCES `sales_currencies` (`id`)
					)
				";
				if (! $this->executeSQL($create_table_query)) {
					$this->error("SQL Error creating product_prices table in ".$this->module."::Schema::upgrade(): ".$this->error());
					app_log($this->error(), 'error');
					return false;
				}

				$this->setVersion(3);
				$GLOBALS['_database']->CommitTrans();
			}
			if ($this->version() < 4 and $max_version >= 4) {
				app_log("Upgrading schema to version 4",'notice',__FILE__,__LINE__);

				# Start Transaction
				if (! $GLOBALS['_database']->BeginTrans())
					app_log("Transactions not supported",'warning',__FILE__,__LINE__);

				$create_table_query = "
					CREATE TABLE IF NOT EXISTS `product_instances` (
						id			int(11) NOT NULL AUTO_INCREMENT,
						product_id	int(11) NOT NULL,
						serial_number	varchar(256) NOT NULL,
						date_created	datetime NOT NULL,
						status		enum('ACTIVE','INACTIVE') NOT NULL default 'ACTIVE',
						PRIMARY KEY `pk_product_instance_id` (`id`),
						UNIQUE KEY `uk_product_instance_sn` (`product_id`,`serial_number`),
						FOREIGN KEY `fk_product_instance_id` (`product_id`) REFERENCES `product_products` (`id`)
					)
				";
				if (! $this->executeSQL($create_table_query)) {
					$this->error("SQL Error creating product_instances table in ".$this->module."::Schema::upgrade(): ".$this->error());
					app_log($this->error(), 'error');
					return false;
				}

				$this->setVersion(4);
				$GLOBALS['_database']->CommitTrans();
			}
			if ($this->version() < 5 and $max_version >= 5) {
				app_log("Upgrading schema to version 5",'notice',__FILE__,__LINE__);

				# Start Transaction
				if (! $GLOBALS['_database']->BeginTrans())
					app_log("Transactions not supported",'warning',__FILE__,__LINE__);

				$alter_table_query = "
					ALTER TABLE `product_prices`
					MODIFY COLUMN id int(11) NOT NULL AUTO_INCREMENT
				";
				if (! $this->executeSQL($alter_table_query)) {
					$this->error("SQL Error altering product_prices table in ".$this->module."::Schema::upgrade(): ".$this->error());
					app_log($this->error(), 'error');
					return false;
				}

				$this->setVersion(5);
				$GLOBALS['_database']->CommitTrans();
			}
			if ($this->version() < 6 and $max_version >= 6) {
				app_log("Upgrading schema to version 6",'notice',__FILE__,__LINE__);

				# Start Transaction
				if (! $GLOBALS['_database']->BeginTrans())
					app_log("Transactions not supported",'warning',__FILE__,__LINE__);

				$create_table_query = "
					CREATE TABLE IF NOT EXISTS `product_prices_audit` (
						`id`			    int(11) NOT NULL AUTO_INCREMENT,
						`product_price_id`	int(11) NOT NULL,
                        `user_id`           int(11) NOT NULL,
                        `date_updated`      timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,   
						`note`	text,     
						PRIMARY KEY (`id`),						                                        
						FOREIGN KEY `fk_product_price_id` (`product_price_id`) REFERENCES `product_prices` (`id`),
                        FOREIGN KEY `fk_product_price_user` (`user_id`) REFERENCES `register_users` (`id`)
					)
				"; 
				if (! $this->executeSQL($create_table_query)) {
					$this->error("SQL Error creating product_prices_audit table in ".$this->module."::Schema::upgrade(): ".$this->error());
					app_log($this->error(), 'error');
					return false;
				}

				$this->setVersion(6);
				$GLOBALS['_database']->CommitTrans();
			}
			if ($this->version() < 7 && $max_version >= 7) {
				app_log("Upgrading schema to version 7",'notice',__FILE__,__LINE__);

				# Start Transaction
				if (! $GLOBALS['_database']->BeginTrans())
					app_log("Transactions not supported",'warning',__FILE__,__LINE__);

				$alter_table_query = "
					ALTER TABLE `product_products`
					MODIFY COLUMN `type` enum('group','kit','inventory','unique','service') DEFAULT 'inventory'
				";
				if (! $this->executeSQL($alter_table_query)) {
					$this->error("SQL Error altering product_products table in ".$this->module."::Schema::upgrade(): ".$this->error());
					app_log($this->error(), 'error');
					return false;
				}

				$this->setVersion(7);
				$GLOBALS['_database']->CommitTrans();
			}

			if ($this->version() < 8 && $max_version >= 8) {
				app_log("Upgrading schema to version 8",'notice',__FILE__,__LINE__);

				# Start Transaction
				if (! $GLOBALS['_database']->BeginTrans())
					app_log("Transactions not supported",'warning',__FILE__,__LINE__);

				$create_table_query = "
					CREATE TABLE if not exists `product_tags` (
					  `id` int NOT NULL AUTO_INCREMENT,
					  `product_id` int DEFAULT NULL,
					  `name` varchar(255) NOT NULL,
					  PRIMARY KEY (`id`)
					) ENGINE=InnoDB DEFAULT CHARSET=latin1;
				";

				if (! $this->executeSQL($create_table_query)) {
					$this->error("SQL Error creating product_tags table in ".$this->module."::Schema::upgrade(): ".$this->error());
					app_log($this->error(), 'error');
					return false;
				}

				$this->setVersion(8);
				$GLOBALS['_database']->CommitTrans();
			}

			if ($this->version() < 9 && $max_version >= 9) {
				app_log("Upgrading schema to version 9", 'notice', __FILE__, __LINE__);

				# Start Transaction
				if (!$GLOBALS['_database']->BeginTrans())
					app_log("Transactions not supported", 'warning', __FILE__, __LINE__);

				// Truncate product_images table
				$table = new \Database\Schema\Table('product_images');
				if (!$table->truncate()) {
					$this->error = $table->error();
					return false;
				}

				if (! $table->has_column("view_order")) {
					// Add view_order column
					$alter_table_query = "
						ALTER TABLE `product_images`
						ADD COLUMN `view_order` INT(3) NOT NULL DEFAULT 999
					";
					if (!$this->executeSQL($alter_table_query)) {
						$this->error("SQL Error altering product_images table in " . $this->module . "::Schema::upgrade(): " . $this->error());
						app_log($this->error(), 'error');
						return false;
					}
				}

				if ($table->has_constraint("FK_IMAGE_ID")) {
					// Drop existing foreign key
					$drop_fk_query = "
						ALTER TABLE `product_images`
						DROP FOREIGN KEY `FK_IMAGE_ID`
					";
					if (!$this->executeSQL($drop_fk_query)) {
						$this->error("SQL Error dropping foreign key in " . $this->module . "::Schema::upgrade(): " . $this->error());
						app_log($this->error(), 'error');
						return false;
					}
				}

				// Add new foreign key
				$add_fk_query = "
					ALTER TABLE `product_images`
					ADD CONSTRAINT `FK_IMAGE_ID` FOREIGN KEY (`image_id`) REFERENCES `storage_files` (`id`)
				";
				if (!$this->executeSQL($add_fk_query)) {
					$this->error("SQL Error adding new foreign key in " . $this->module . "::Schema::upgrade(): " . $this->error());
					app_log($this->error(), 'error');
					return false;
				}

				$this->setVersion(9);
				$GLOBALS['_database']->CommitTrans();
			}

			return true;
		}
	}
