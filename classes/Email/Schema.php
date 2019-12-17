<?
	namespace Email;

	class Schema Extends \Database\BaseSchema {
		public $module = "Email";
		
		public function upgrade() {
			$this->error = null;

			if ($this->version() < 2) {
				app_log("Upgrading schema to version 2",'notice',__FILE__,__LINE__);

				$create_table_query = "
					CREATE TABLE IF NOT EXISTS email_messages (
						`id`	int(11) not null AUTO_INCREMENT,
						`date_created` datetime not null,
						`date_tried` datetime,
						`tries` int(2) not null default 0,
						`status` enum('QUEUED','SENDING','ERROR','CANCELLED','SENT','FAILED') not null default 'QUEUED',
						`to` varchar(255) not null,
						`from` varchar(255) not null,
						`subject` varchar(255) not null,
						`body` text,
						`html` int(1) not null default 1,
						`process_id` int(11) not null default 0,
						PRIMARY KEY `pk_id` (`id`),
						INDEX `idx_status` (`status`,`date_created`),
						INDEX `idx_date` (`date_created`),
						INDEX `idx_to` (`to`)
					)
				";
				if (! $this->executeSQL($create_table_query)) {
					$this->error = "SQL Error creating email_messages table in ".$this->module."::Schema::upgrade(): ".$this->error;
					app_log($this->error, 'error');
					return false;
				}

				$create_table_query = "
					CREATE TABLE IF NOT EXISTS email_history (
						`email_id`	int(11) not null,
						`date_event` datetime not null,
						`new_status` enum('QUEUED','SENDING','ERROR','CANCELLED','SENT','FAILED') not null default 'QUEUED',
						`response_code` int(3),
						`host` varchar(255),
						`result` text,
						INDEX `idx_id` (`email_id`),
						INDEX `idx_date` (`date_event`),
						INDEX `idx_host` (`host`),
						INDEX `idx_code` (`response_code`)
					)
				";
				if (! $this->executeSQL($create_table_query)) {
					$this->error = "SQL Error creating email_history table in ".$this->module."::Schema::upgrade(): ".$this->error;
					app_log($this->error, 'error');
					return false;
				}

				$this->setVersion(2);
				$GLOBALS['_database']->CommitTrans();
			}

			$this->addRoles(array(
				'email manager'	=> 'Can trigger emails via api'
			));
			return true;
		}
	}
?>
