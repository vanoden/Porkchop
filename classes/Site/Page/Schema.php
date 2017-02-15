<?
	namespace Site\Page;
	
	class Schema {
		###################################################
		### Database Schema Setup						###
		###################################################
		public function __construct() {
			# See if Schema is Available
			$schema_list = $GLOBALS['_database']->MetaTables();

			if (! in_array("page__info",$schema_list)) {
				# Create company__info table
				$create_table_query = "
					CREATE TABLE page__info (
						label	varchar(100) not null primary key,
						value	varchar(255)
					)
				";
				$GLOBALS['_database']->Execute($create_table_query);
				if ($GLOBALS['_database']->ErrorMsg())
				{
					$this->error = "SQL Error creating info table in Site::Page::Schema::_construct: ".$GLOBALS['_database']->ErrorMsg();
					return null;
				}
			}

			# Check Current Schema Version
			$get_version_query = "
				SELECT	value
				FROM	page__info
				WHERE	label = 'schema_version'
			";

			$rs = $GLOBALS['_database']->Execute($get_version_query);
			if (! $rs) {
				$this->error = "SQL Error in Site::Page::Schema::_construct: ".$GLOBALS['_database']->ErrorMsg();
				return 0;
			}

			list($current_schema_version) = $rs->FetchRow();

			if ($current_schema_version < 1) {
				$update_schema_query = "
					INSERT
					INTO	page__info
					VALUES	('schema_version',1)
					ON DUPLICATE KEY UPDATE
							value = 1
				";
				$GLOBALS['_database']->Execute($update_schema_query);
				if ($GLOBALS['_database']->ErrorMsg()) {
					$this->error = "SQL Error creating _info table in Site::Page::Schema::_construct: ".$GLOBALS['_database']->ErrorMsg();
					return 0;
				}
				$current_schema_version = 1;
				$update_schema_version = "
					UPDATE	page__info
					SET		value = $current_schema_version
					WHERE	label = 'schema_version'
				";
				$GLOBALS['_database']->Execute($update_schema_version);
				if ($GLOBALS['_database']->ErrorMsg()) {
					$this->error = "SQL Error in Site::Page::Schema::_construct: ".$GLOBALS['_database']->ErrorMsg();
					return 0;
				}
			}
			if ($current_schema_version < 2) {
				$create_table_query = "
					CREATE TABLE IF NOT EXISTS `page_metadata` (
					  `id`		int(5) NOT NULL AUTO_INCREMENT,
					  `module`	varchar(100) NOT NULL,
					  `view`	varchar(100) NOT NULL,
					  `index`	varchar(100) NOT NULL DEFAULT '',
					  `format`	enum('application/json','application/xml') DEFAULT 'application/json',
					  `content` text,
					  PRIMARY KEY `pk_page_views` (`id`),
					  UNIQUE KEY `uk_page_views` (`module`,`view`,`index`)
					)
				";
				$GLOBALS['_database']->Execute($create_table_query);
				if ($GLOBALS['_database']->ErrorMsg()) {
					$this->error = "SQL Error creating page views table in Site::Page::Schema::_construct: ".$GLOBALS['_database']->ErrorMsg();
					return 0;
				}
				$create_table_query = "
					CREATE TABLE IF NOT EXISTS `page_widget_types` (
					  `id` int(5) NOT NULL AUTO_INCREMENT,
					  `name` varchar(100) NOT NULL,
					  PRIMARY KEY `pk_widget_type` (`id`),
					  UNIQUE KEY `uk_name` (`name`)
					)
				";
				$GLOBALS['_database']->Execute($create_table_query);
				if ($GLOBALS['_database']->ErrorMsg()) {
					$this->error = "SQL Error creating page widgets table in Site::Page::Schema::_construct: ".$GLOBALS['_database']->ErrorMsg();
					return 0;
				}
				$create_table_query = "
					CREATE TABLE IF NOT EXISTS `page_widgets` (
					  `id` int(10) NOT NULL AUTO_INCREMENT,
					  `page_view_id` int(5) NOT NULL,
					  `type_id` int(10) NOT NULL DEFAULT '0',
					  PRIMARY KEY (`id`),
					  FOREIGN KEY `fk_page_view` (`page_view_id`) REFERENCES `page_metadata` (`id`),
					  FOREIGN KEY `fk_widget_type` (`type_id`) REFERENCES `page_widget_types` (`id`)
					)
				";
				$GLOBALS['_database']->Execute($create_table_query);
				if ($GLOBALS['_database']->ErrorMsg()) {
					$this->error = "SQL Error creating page widgets table in Site::Page::Schema::_construct: ".$GLOBALS['_database']->ErrorMsg();
					return 0;
				}
				$current_schema_version = 2;
				$update_schema_version = "
					UPDATE	page__info
					SET		value = $current_schema_version
					WHERE	label = 'schema_version'
				";
				$GLOBALS['_database']->Execute($update_schema_version);
				if ($GLOBALS['_database']->ErrorMsg()) {
					$this->error = "SQL Error in Site::Page::Schema::_construct: ".$GLOBALS['_database']->ErrorMsg();
					return 0;
				}
			}
			if ($current_schema_version < 3) {
				$create_table_query = "
					CREATE TABLE IF NOT EXISTS `page_pages` (
					  `id` int(5) NOT NULL AUTO_INCREMENT,
					  `module` varchar(100) NOT NULL,
					  `view` varchar(100) NOT NULL,
					  `index` varchar(100) NOT NULL DEFAULT '',
					  PRIMARY KEY (`id`),
					  UNIQUE KEY `uk_page_views` (`module`,`view`,`index`)
					)
				";
				$GLOBALS['_database']->Execute($create_table_query);
				if ($GLOBALS['_database']->ErrorMsg()) {
					$this->error = "SQL Error creating page pages table in Site::Page::Schema::_construct: ".$GLOBALS['_database']->ErrorMsg();
					return 0;
				}
				$current_schema_version = 3;
				$update_schema_version = "
					UPDATE	page__info
					SET		value = $current_schema_version
					WHERE	label = 'schema_version'
				";
				$GLOBALS['_database']->Execute($update_schema_version);
				if ($GLOBALS['_database']->ErrorMsg()) {
					$this->error = "SQL Error in Site::Page::Schema::_construct: ".$GLOBALS['_database']->ErrorMsg();
					return 0;
				}
			}
		}
	}
?>