<?php
	namespace Contact;

	class Schema Extends \Database\BaseSchema {
		public function __construct() {
			$this->module = "Contact";
			parent::__construct();
		}

		public function upgrade() {
			$this->clearError();
			$database = new \Database\Service();

			if ($this->version() < 1) {
				app_log("Upgrading schema to version 1",'notice',__FILE__,__LINE__);

				# Start Transaction
				if (! $database->BeginTrans())
					app_log("Transactions not supported",'warning',__FILE__,__LINE__);

				$create_table_query = "
					CREATE TABLE IF NOT EXISTS `contact_events` (
					  `id` int(11) NOT NULL AUTO_INCREMENT,
					  `date_event` datetime NOT NULL,
					  `content` TEXT NOT NULL,
					  `status` enum('NEW','OPEN','CLOSED') DEFAULT 'NEW',
					  PRIMARY KEY (`id`)
					)
				";
				if (! $database->Execute($create_table_query)) {
					$this->SQLError("Error creating contact_events table in ".$this->module."::Schema::upgrade(): ".$database->ErrorMsg());
					app_log($this->error(), 'error');
					return false;
				}

				$this->setVersion(1);
				$database->CommitTrans();
			}
			if ($this->version() < 2) {
				app_log("Upgrading schema to version 2",'notice',__FILE__,__LINE__);
				$this->setVersion(2);
			}

			return true;
		}
	}
