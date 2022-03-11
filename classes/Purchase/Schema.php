<?php
	namespace Purchase;

	class Schema Extends \Database\BaseSchema {
		public $module = "Purchase";

		public function upgrade() {
			$this->error = null;

			if ($this->version() < 1) {
				app_log("Upgrading schema to version 1",'notice',__FILE__,__LINE__);

				# Start Transaction
				if (! $GLOBALS['_database']->BeginTrans())
					app_log("Transactions not supported",'warning',__FILE__,__LINE__);

				$create_table_query = "
					CREATE TABLE `purchase_orders` (
						`id` int(11) NOT NULL AUTO_INCREMENT,
						`code` varchar(32) NOT NULL,
						`vendor_id` int(11) NOT NULL,
						`vendor_contact_id` int(11) NOT NULL,
						`user_created` int(11) NOT NULL,
						`date_created` datetime NOT NULL,
						`status` enum('OPEN','APPROVED','CANCELLED','PAID') NOT NULL DEFAULT 'OPEN',
						PRIMARY KEY (`id`),
						UNIQUE KEY `uk_purchaseorder_code` (`code`),
						FOREIGN KEY `fk_vendor_id` (`vendor_id`) REFERENCES `register_organizations`(`id`),
						FOREIGN KEY `fk_vendor_contact` (`vendor_contact_id`) REFERENCES `register_users`(`id`),
						INDEX `idx_purchaseorder` (`vendor_id`,`vendor_contact_id`,`date_created`),
						INDEX `idx_purchaseorder_report` (`status`,`date_created`,`vendor_id`),
						INDEX `idx_purchaseorder_report2` (`date_created`,`status`,`vendor_id`)
					)
				";

				if (! $this->executeSQL($create_table_query)) {
					$this->error = "SQL Error creating purchase_orders table in ".$this->module."::Schema::upgrade(): ".$this->error;
					app_log($this->error, 'error');
					return false;
				}

				$create_table_query = "
					CREATE TABLE `purchase_order_payments` (
						`id` int(11) NOT NULL AUTO_INCREMENT,
						`order_id` int(11) NOT NULL,
						`date_paid` datetime NOT NULL,
						`amount_paid` decimal(10,2) NOT NULL,
						`payment_number` varchar(32) NOT NULL,
						`status` enum('OPEN','CLOSED','CANCELLED') NOT NULL DEFAULT 'OPEN',
						PRIMARY KEY (`id`),
						INDEX `idx_purchaseorder_date` (`date_paid`,`order_id`),
						INDEX `idx_purchaseorder_number` (`payment_number`),
						FOREIGN KEY `idx_purchaseorder_payment` (`order_id`) REFERENCES `purchase_order` (`id`),
					)
				";

				if (! $this->executeSQL($create_table_query)) {
					$this->error = "SQL Error creating purchase_order_payments table in ".$this->module."::Schema::upgrade(): ".$this->error;
					app_log($this->error, 'error');
					return false;
				}

				$this->setVersion(1);
				$GLOBALS['_database']->CommitTrans();
			}
			return true;
		}
	}
