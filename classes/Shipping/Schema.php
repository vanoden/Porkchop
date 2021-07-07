<?php
	namespace Shipping;

	class Schema Extends \Database\BaseSchema  {
		public $module = "shipping";

		public function upgrade() {
			$this->error = null;

			if ($this->version() < 1) {
				app_log("Upgrading schema to version 1",'notice',__FILE__,__LINE__);

				# Start Transaction
				if (! $GLOBALS['_database']->BeginTrans())
					app_log("Transactions not supported",'warning',__FILE__,__LINE__);

				# Shipping Vendors
				$create_table_query = "
					CREATE TABLE IF NOT EXISTS `shipping_vendors` (
						id INT(11) NOT NULL AUTO_INCREMENT,
						name VARCHAR(150) NOT NULL,
						account_number VARCHAR(255),
						PRIMARY KEY `pk_vendor_id` (`id`),
						UNIQUE KEY `uk_name` (`name`)
					)
				";
				if (! $this->executeSQL($create_table_query)) {
					$this->error = "SQL Error creating shipping_vendors table in ".$this->module."::Schema::upgrade(): ".$this->error;
					app_log($this->error, 'error');
					return false;
				}

				# Collection of Shipments
				$create_table_query = "
					CREATE TABLE IF NOT EXISTS `shipping_shipments` (
						id INT(11) NOT NULL AUTO_INCREMENT,
						code varchar(150) NOT NULL,
						document_number varchar(255) NOT NULL,
						date_entered datetime NOT NULL,
						date_shipped datetime,
						status enum('NEW','SHIPPED','LOST','RECEIVED','RETURNED') NOT NULL DEFAULT 'NEW',
						send_contact_id INT(11) NOT NULL,
						send_location_id INT(11) NOT NULL,
						rec_contact_id INT(11) NOT NULL,
						rec_location_id INT(11) NOT NULL,
						vendor_id INT(11) NOT NULL,
						instructions TEXT,
						PRIMARY KEY `pk_id` (`id`),
						UNIQUE KEY `uk_code` (`code`),
						KEY `idx_document` (`document_number`),
						KEY `idx_vendor` (`vendor_id`),
						FOREIGN KEY `fk_sender` (`send_contact_id`) REFERENCES `register_users` (`id`),
						FOREIGN KEY `fk_receiver` (`rec_contact_id`) REFERENCES `register_users` (`id`),
						FOREIGN KEY `fk_send_from` (`send_location_id`) REFERENCES `register_locations` (`id`),
						FOREIGN KEY `fk_send_to` (`rec_location_id`) REFERENCES `register_locations` (`id`),
						INDEX `idx_date` (`date_entered`),
						INDEX `idx_status` (`status`,`date_entered`)
					)
				";
				if (! $this->executeSQL($create_table_query)) {
					$this->error = "SQL Error creating shipping_shipments table in ".$this->module."::Schema::upgrade(): ".$this->error;
					app_log($this->error, 'error');
					return false;
				}

				# Items requiring action
				$create_table_query = "
					CREATE TABLE IF NOT EXISTS `shipping_packages` (
						id INT(11) NOT NULL AUTO_INCREMENT,
						shipment_id INT(11) NOT NULL,
						number INT(11) NOT NULL,
						tracking_code varchar(255),
						status enum('READY','SHIPPED','RECEIVED','RETURNED') NOT NULL DEFAULT 'READY',
						`condition` enum('OK','DAMAGED'),
						height decimal(6,2) NOT NULL DEFAULT 0,
						width decimal(6,2) NOT NULL DEFAULT 0,
						depth decimal(6,2) NOT NULL DEFAULT 0,
						weight decimal(6,2) NOT NULL DEFAULT 0,
						shipping_cost decimal(6,2) NOT NULL DEFAULT 0,
						date_received datetime,
						user_received_id int(11),
						PRIMARY KEY `pk_id` (`id`),
						UNIQUE KEY `uk_line` (`shipment_id`,`number`),
						FOREIGN KEY `fk_shipment_id` (`shipment_id`) REFERENCES `shipping_shipments` (`id`),
						KEY `idx_tracking_code` (`shipment_id`,`tracking_code`)
					)
				";
				if (! $this->executeSQL($create_table_query)) {
					$this->error = "SQL Error creating shipping_packages table in ".$this->module."::Schema::upgrade(): ".$this->error;
					app_log($this->error, 'error');
					return false;
				}

				# Things We Have To Do
				$create_table_query = "
					CREATE TABLE IF NOT EXISTS `shipping_items` (
						id INT(11) NOT NULL AUTO_INCREMENT,
						package_id INT(11),
						product_id INT(11) NOT NULL,
						serial_number VARCHAR(255),
						`condition` enum('OK','DAMAGED'),
						quantity INT(11) NOT NULL,
						description TEXT,
						PRIMARY KEY `pk_shipment_item_id` (`id`),
						FOREIGN KEY `fk_shipment_product` (`product_id`) REFERENCES `product_products` (`id`)
					)
				";
				if (! $this->executeSQL($create_table_query)) {
					$this->error = "SQL Error creating shipping_items table in ".$this->module."::Schema::upgrade(): ".$this->error;
					app_log($this->error, 'error');
					return false;
				}

				$this->setVersion(1);
				$GLOBALS['_database']->CommitTrans();
			}
			if ($this->version() < 2) {
				$table = new \Database\Schema\Table('shipping_items');
				if (! $table->has_column('shipment_id')) {
					# Things We Have To Do
					$alter_table_query = "
						ALTER TABLE `shipping_items` ADD `shipment_id` INT(11)
					";
					if (! $this->executeSQL($alter_table_query)) {
						$this->error = "SQL Error adding column to shipping_items table in ".$this->module."::Schema::upgrade(): ".$this->error;
						app_log($this->error, 'error');
						return false;
					}
				}

				$itemList = new \Shipping\ItemList();
				$items = $itemList->find();
				foreach ($items as $item) {
					if (empty($item->package_id)) $item->delete();
					if ($item->error()) {
						$this->_error = "Error deleting item: ".$item->error();
						return false;
					}
					$package = new \Shipping\Package($item->package_id);
					if ($item->package_id != $package->id) {
						$item->update(array('shipment_id' => $package->shipment_id));
						if ($item->error()) {
							$this->_error = "Error updating item: ".$item->error();
							return false;
						}
					}
				}

				$alter_table_query = "
					ALTER TABLE `shipping_items` ADD FOREIGN KEY `fk_shipping_items_shipment` (`shipment_id`) REFERENCES `shipping_shipments` (`id`)";

				if (! $this->executeSQL($alter_table_query)) {
					$this->error = "SQL Error adding foreign key to shipping_items table in ".$this->module."::Schema::upgrade(): ".$this->error;
					app_log($this->error, 'error');
					return false;
				}
				

				$this->setVersion(2);
				$GLOBALS['_database']->CommitTrans();
			}
			$this->addRoles(array(
				'shipping manager'	=> 'Can browse all shipments'
			));
			return true;
		}
	}
