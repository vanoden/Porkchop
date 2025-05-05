<?php
namespace Media;

class Schema extends \Database\BaseSchema {
	public function __construct() {
		$this->module = "media";
		parent::__construct();
	}

	public function upgrade() {
		$this->clearError();
		$database = new \Database\Service();

		if ($this->version() < 1) {
			app_log("Upgrading schema to version 1", 'notice', __FILE__, __LINE__);

			# Start Transaction
			if (!$database->BeginTrans())
				app_log("Transactions not supported", 'warning', __FILE__, __LINE__);

			$create_table_query = "
					CREATE TABLE IF NOT EXISTS `media_items` (
						id				int(11) NOT NULL AUTO_INCREMENT,
						type			enum('raw','audio','video','document','image') NOT NULL default 'raw',
						date_created	datetime,
						date_updated	datetime,
						owner_id		int(11),
						code			varchar(150),
						deleted			int(1) DEFAULT 0,
						PRIMARY KEY (`id`),
						UNIQUE KEY `uk_item_code` (`code`),
						FOREIGN KEY `FK_OWNER_ID` (`owner_id`) REFERENCES register_users (`id`)
					)
				";
			if (! $database->Execute($create_table_query)) {
				$this->SQLError("Error creating media_items table in " . $this->module . "::Schema::upgrade(): " . $database->ErrorMsg());
				app_log($this->error(), 'error');
				return false;
			}

			$create_table_query = "
					CREATE TABLE IF NOT EXISTS `media_metadata` (
						`item_id`		int(11) NOT NULL,
						`label`			varchar(100) NOT NULL,
						`value`			text,
						UNIQUE KEY `UK_ID_LABEL` (`item_id`,`label`),
						INDEX `IDX_LABEL_VALUE` (`label`,`value`(32)),
						FOREIGN KEY `FK_ITEM_ID` (`item_id`) REFERENCES `media_items` (`id`)
					)
				";
			if (! $database->Execute($create_table_query)) {
				$this->SQLError("Error creating media_metadata table in " . $this->module . "::Schema::upgrade(): " . $database->ErrorMsg());
				app_log($this->error(), 'error');
				return false;
			}

			$create_table_query = "
					CREATE TABLE IF NOT EXISTS `media_files` (
						id				int(11) NOT NULL AUTO_INCREMENT,
						`item_id`		int(11) NOT NULL,
						`code`			varchar(100) NOT NULL,
						`index`			varchar(100) NOT NULL DEFAULT '',
						`size`			int(11) NOT NULL DEFAULT 0,
						`timestamp`		timestamp,
						`mime_type`		varchar(100) NOT NULL DEFAULT 'text/plain',
						`original_file`	varchar(100) DEFAULT '',
						`date_uploaded`	datetime,
						`owner_id`		int(11) NOT NULL,
						PRIMARY KEY `PK_ID`(`id`),
						UNIQUE KEY `UK_ITEM_INDEX` (`item_id`,`index`),
						UNIQUE KEY `UK_MEDIA_FILE_CODE` (`code`),
						FOREIGN KEY `FK_MEDIA_ITEM_ID` (`item_id`) REFERENCES `media_items` (`id`),
						FOREIGN KEY `FK_MEDIA_OWNER_ID` (`owner_id`) REFERENCES `register_users` (`id`)
					)
				";
			if (! $database->Execute($create_table_query)) {
				$this->SQLError("Error creating media_metadata table in " . $this->module . "::Schema::upgrade(): " . $database->ErrorMsg());
				app_log($this->error(), 'error');
				return false;
			}

			$this->setVersion(1);
			$database->CommitTrans();
		}
		if ($this->version() < 2) {
			app_log("Upgrading schema to version 2", 'notice', __FILE__, __LINE__);

			# Start Transaction
			if (! $database->BeginTrans())
				app_log("Transactions not supported", 'warning', __FILE__, __LINE__);

			$create_table_query = "
					ALTER TABLE `media_files` ADD disposition enum('inline','attachment','form-data','signal','alert','icon','render','notification') default 'inline'
				";

			if (! $database->Execute($create_table_query)) {
				$this->SQLError("Error altering media_files table in " . $this->module . "::Schema::upgrade(): " . $database->ErrorMsg());
				app_log($this->error(), 'error');
				return false;
			}

			$this->setVersion(2);
			$database->CommitTrans();
		}
		if ($this->version() < 3) {
			app_log("Upgrading schema to version 3", 'notice', __FILE__, __LINE__);

			# Start Transaction
			if (! $database->BeginTrans())
				app_log("Transactions not supported", 'warning', __FILE__, __LINE__);

			$create_table_query = "
					CREATE TABLE media_privileges (
						item_id			int(11),
						customer_id		int(11),
						organization_id	int(11),
						`read`			int(1) default 0,
						`write`			int(1) default 0
					)
				";

			if (! $database->Execute($create_table_query)) {
				$this->SQLError("Error altering media_privileges table in " . $this->module . "::Schema::upgrade(): " . $database->ErrorMsg());
				app_log($this->error(), 'error');
				return false;
			}

			$this->setVersion(3);
			$database->CommitTrans();
		}
		return true;
	}
}
