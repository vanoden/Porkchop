<?php
	class LibraryInit
	{
		public $error;

		public function __contruct()
		{
			# See if Schema is Available
			$schema_list = $GLOBALS['_database']->MetaTables();

			if (! in_array("library__info",$schema_list))
			{
				# Create company__info table
				$create_table_query = "
					CREATE TABLE library__info (
						label	varchar(100) not null primary key,
						value	varchar(255)
					)
				";
				$GLOBALS['_database']->Execute($create_table_query);
				if ($GLOBALS['_database']->ErrorMsg())
				{
					$this->error = "SQL Error creating info table in LibraryInit::construct: ".$GLOBALS['_database']->ErrorMsg();
					return 0;
				}
			}

			# Check Current Schema Version
			$get_version_query = "
				SELECT	value
				FROM	library__info
				WHERE	label = 'schema_version'
			";

			$rs = $GLOBALS['_database']->Execute($get_version_query);
			if (! $rs)
			{
				$this->error = "SQL Error in LibraryInit::construct ".$GLOBALS['_database']->ErrorMsg();
				return 0;
			}

			list($current_schema_version) = $rs->FetchRow();

			if ($current_schema_version < 1)
			{
				$create_table_query = "
					CREATE TABLE IF NOT EXISTS `library_files` (
					  `id` int(11) NOT NULL AUTO_INCREMENT,
					  `date_created` datetime NOT NULL,
					  `owner_id` int(11),
					  `name` varchar(255) NOT NULL,
					  `path` varchar(255) NOT NULL,
					  PRIMARY KEY (`id`)
					)
				";
				$GLOBALS['_database']->Execute($create_table_query);
				if ($GLOBALS['_database']->ErrorMsg())
				{
					$this->error = "SQL Error creating library files table in ContactInit::construct ".$GLOBALS['_database']->ErrorMsg();
					return 0;
				}
				$create_table_query = "
					CREATE TABLE IF NOT EXISTS `library_tags` (
					  `id` int(11) NOT NULL AUTO_INCREMENT,
					  `name` varchar(255) NOT NULL,
					  PRIMARY KEY (`id`),
					  UNIQUE KEY `uk_name` (`name`)
					)
				";
				$GLOBALS['_database']->Execute($create_table_query);
				if ($GLOBALS['_database']->ErrorMsg())
				{
					$this->error = "SQL Error creating library tags table in ContactInit::construct ".$GLOBALS['_database']->ErrorMsg();
					return 0;
				}
				$create_table_query = "
					CREATE TABLE IF NOT EXISTS `library_file_tags` (
					  `file_id` int(11) NOT NULL AUTO_INCREMENT,
					  `tag_id` int(11) NOT NULL,
					  PRIMARY KEY (`file_id`,`tag_id`),
					  FOREIGN KEY `fk_file_id` (`file_id`) REFERENCES `library_files` (`id`),
					  FOREIGN KEY `fk_tag_id` (`tag_id`) REFERENCES `library_tags` (`id`)
					)
				";
				$GLOBALS['_database']->Execute($create_table_query);
				if ($GLOBALS['_database']->ErrorMsg())
				{
					$this->error = "SQL Error creating library file tags table in ContactInit::construct ".$GLOBALS['_database']->ErrorMsg();
					return 0;
				}
				$current_schema_version = 1;

				$update_schema_query = "
					INSERT
					INTO	library__info
					VALUES	('schema_version',$current_schema_version)
					ON DUPLICATE KEY UPDATE
							value = 1
				";
				$GLOBALS['_database']->Execute($update_schema_query);
				if ($GLOBALS['_database']->ErrorMsg())
				{
					$this->error = "SQL Error updating schema_version table in ContactInit::construct ".$GLOBALS['_database']->ErrorMsg();
					return 0;
				}
			}
		}
	}

	class LibraryFile extends LibraryInit
	{

	}
