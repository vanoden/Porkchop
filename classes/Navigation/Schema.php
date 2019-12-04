<?
	namespace Navigation;

	class Schema Extends \Database\Schema {
		public $module = "Navigation";

		public function upgrade() {
			$this->error = null;

			if ($this->version() < 2) {
				app_log("Upgrading schema to version 2",'notice',__FILE__,__LINE__);

				# Start Transaction
				if (! $GLOBALS['_database']->BeginTrans())
					app_log("Transactions not supported",'warning',__FILE__,__LINE__);

				$create_table_query = "
                    CREATE TABLE IF NOT EXISTS `navigation_menus` (
                      `id` int(5) NOT NULL AUTO_INCREMENT,
                      `code` varchar(100) NOT NULL,
                      `title` varchar(100) NOT NULL DEFAULT '',
                      PRIMARY KEY (`id`),
                      UNIQUE KEY `uk_code` (`code`)
                    )
				";
				if (! $this->executeSQL($create_table_query)) {
					$this->error = "SQL Error creating navigation_menus table in ".$this->module."::Schema::upgrade(): ".$this->error;
					app_log($this->error, 'error');
					return false;
				}

				$create_table_query = "
                    CREATE TABLE IF NOT EXISTS `navigation_menu_items` (
                      `id` int(8) NOT NULL AUTO_INCREMENT,
                      `menu_id` int(11) NOT NULL DEFAULT '0',
                      `title` varchar(100) NOT NULL DEFAULT '',
                      `target` varchar(200) NOT NULL DEFAULT '',
                      `view_order` int(3) DEFAULT NULL,
                      `alt` varchar(255),
					  `description` text,
                      `parent_id` int(5) NOT NULL DEFAULT '0',
                      `external` int(1) NOT NULL DEFAULT '0',
                      `ssl` int(11) NOT NULL DEFAULT '0',
                      PRIMARY KEY (`id`),
                      KEY `parent_id` (`parent_id`),
                      KEY `view_order` (`view_order`),
                      FOREIGN KEY `fk_menu_id` (`menu_id`) REFERENCES `navigation_menus` (`id`)
                    )
				";
				if (! $this->executeSQL($create_table_query)) {
					$this->error = "SQL Error creating navigation_menu_items table in ".$this->module."::Schema::upgrade(): ".$this->error;
					app_log($this->error, 'error');
					return false;
				}

				$this->setVersion(1);
				$GLOBALS['_database']->CommitTrans();
			}

			return true;
		}
	}
?>