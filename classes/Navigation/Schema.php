<?
	namespace Navigation;

	class Schema {
		public $error;
		public $errno;

		public function __construct() {
			$this->upgrade();
		}

		public function version() {
			# See if Schema is Available
			$schema_list = $GLOBALS['_database']->MetaTables();
			$info_table = "navigation__info";

			if (! in_array($info_table,$schema_list)) {
				# Create __info table
				$create_table_query = "
					CREATE TABLE `$info_table` (
						label	varchar(100) not null primary key,
						value	varchar(255)
					)
				";
				$GLOBALS['_database']->Execute($create_table_query);
				if ($GLOBALS['_database']->ErrorMsg()) {
					$this->error = "SQL Error creating info table in MonitorSchema::version: ".$GLOBALS['_database']->ErrorMsg();
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
				$this->error = "SQL Error in Navigation::Schema::version(): ".$GLOBALS['_database']->ErrorMsg();
				return null;
			}

			list($version) = $rs->FetchRow();
			if (! $version) $version = 0;
			return $version;
		}

		public function upgrade() {
			$current_schema_version = $this->version();

			if ($current_schema_version < 1) {
				$update_schema_query = "
					INSERT
					INTO	navigation__info
					VALUES	('schema_version',1)
					ON DUPLICATE KEY UPDATE
							value = 1
				";
				$GLOBALS['_database']->Execute($update_schema_query);
				if ($GLOBALS['_database']->ErrorMsg()) {
					$this->error = "SQL Error creating _info table in navigation::Menu::schema_manager: ".$GLOBALS['_database']->ErrorMsg();
					return null;
				}
				$current_schema_version = 1;
			}
			if ($current_schema_version < 2) {
				$create_table_query = "
                    CREATE TABLE IF NOT EXISTS `navigation_menus` (
                      `id` int(5) NOT NULL AUTO_INCREMENT,
                      `code` varchar(100) NOT NULL,
                      `title` varchar(100) NOT NULL DEFAULT '',
                      PRIMARY KEY (`id`),
                      UNIQUE KEY `uk_code` (`code`)
                    )
				";
				$GLOBALS['_database']->Execute($create_table_query);
				if ($GLOBALS['_database']->ErrorMsg()) {
					$this->error = "SQL Error creating navigation menus table in navigation::Menu::schema_manager: ".$GLOBALS['_database']->ErrorMsg();
					return null;
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
				$GLOBALS['_database']->Execute($create_table_query);
				if ($GLOBALS['_database']->ErrorMsg()) {
					$this->error = "SQL Error creating navigation menu items table in navigation::Menu::schema_manager: ".$GLOBALS['_database']->ErrorMsg();
					return null;
				}
				$current_schema_version = 2;
			}

			$update_schema_version = "
				UPDATE	navigation__info
				SET		value = $current_schema_version
				WHERE	label = 'schema_version'
			";
			$GLOBALS['_database']->Execute($update_schema_version);
			if ($GLOBALS['_database']->ErrorMsg()) {
				$this->error = "SQL Error in navigation::Menu::schema_manager: ".$GLOBALS['_database']->ErrorMsg();
				return null;
			}
		}
	}
?>