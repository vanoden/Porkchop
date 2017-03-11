<?
	namespace Product;

	class Schema {
		public $error;
		public $errno;

		public function __construct() {
			$this->upgrade();
		}

		public function version() {
			# See if Schema is Available
			$schema_list = $GLOBALS['_database']->MetaTables();
			$info_table = "product__info";

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
					$this->error = "SQL Error creating info table in Product::Schema::version(): ".$GLOBALS['_database']->ErrorMsg();
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
				$this->error = "SQL Error in Product::Schema::version(): ".$GLOBALS['_database']->ErrorMsg();
				return null;
			}

			list($version) = $rs->FetchRow();
			if (! $version) $version = 0;
			return $version;
		}
	
		public function upgrade() {
			$current_schema_version = $this->version();

			if ($current_schema_version < 1) {
				app_log("Upgrading schema to version 1",'notice',__FILE__,__LINE__);

				# Start Transaction
				if (! $GLOBALS['_database']->BeginTrans())
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
				$GLOBALS['_database']->Execute($create_table_query);
				if ($GLOBALS['_database']->ErrorMsg()) {
					$this->error = "SQL Error creating products table in ProductInit::__construct: ".$GLOBALS['_database']->ErrorMsg();
					app_log($this->error,'error',__FILE__,__LINE__);
					$GLOBALS['_database']->RollbackTrans();
					return null;
				}

				$create_table_query = "
					CREATE TABLE IF NOT EXISTS `product_images` (
						`product_id`	int(11) NOT NULL,
						`image_id`		int(11) NOT NULL,
						`label`			varchar(100) NOT NULL,
						PRIMARY KEY `PK_PRODUCT_IMAGE` (`product_id`,`image_id`),
						FOREIGN KEY `FK_PRODUCT_ID` (`product_id`) REFERENCES `product_products` (`id`),
						FOREIGN KEY `FK_IMAGE_ID` (`image_id`) REFERENCES `media_items` (`id`)
					)
				";
				$GLOBALS['_database']->Execute($create_table_query);
				if ($GLOBALS['_database']->ErrorMsg()) {
					$this->error = "SQL Error creating product_images table in ProductInit::__construct: ".$GLOBALS['_database']->ErrorMsg();
					app_log($this->error,'error',__FILE__,__LINE__);
					$GLOBALS['_database']->RollbackTrans();
					return 0;
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
				$GLOBALS['_database']->Execute($create_table_query);
				if ($GLOBALS['_database']->ErrorMsg()) {
					$this->error = "SQL Error creating product_types table in ProductInit::__construct: ".$GLOBALS['_database']->ErrorMsg();
					app_log($this->error,'error',__FILE__,__LINE__);
					$GLOBALS['_database']->RollbackTrans();
					return 0;
				}

				$create_table_query = "
					CREATE TABLE IF NOT EXISTS `product_metadata` (
						`id`			int(11)	NOT NULL AUTO_INCREMENT,
						`product_id`	int(11) NOT NULL,
						`key`			varchar(32) NOT NULL,
						`value`			text,
						PRIMARY KEY `PK_ID` (`id`),
						UNIQUE KEY (`product_id`,`key`),
						FOREIGN KEY `FK_PRODUCT_ID` (`product_id`) REFERENCES `product_products` (`id`)
					)
				";
				$GLOBALS['_database']->Execute($create_table_query);
				if ($GLOBALS['_database']->ErrorMsg()) {
					$this->error = "SQL Error creating product_metadata table in ProductInit::__construct: ".$GLOBALS['_database']->ErrorMsg();
					app_log($this->error,'error',__FILE__,__LINE__);
					$GLOBALS['_database']->RollbackTrans();
					return 0;
				}

				$add_roles_query = "
					INSERT
					INTO	register_roles
					VALUES	(null,'product manager','Can view/edit products'),
							(null,'product reporter','Can view products')
				";
				$GLOBALS['_database']->Execute($add_roles_query);
				if ($GLOBALS['_database']->ErrorMsg()) {
					$this->error = "SQL Error adding product roles in ProductInit::__construct: ".$GLOBALS['_database']->ErrorMsg();
					app_log($this->error,'error',__FILE__,__LINE__);
					$GLOBALS['_database']->RollbackTrans();
					return 0;
				}

				$current_schema_version = 1;
				$update_schema_version = "
					INSERT
					INTO	product__info
					VALUES	('schema_version',$current_schema_version)
					ON DUPLICATE KEY UPDATE
						value = $current_schema_version
				";
				$GLOBALS['_database']->Execute($update_schema_version);
				if ($GLOBALS['_database']->ErrorMsg()) {
					$this->error = "SQL Error in ProductInit::schema_manager: ".$GLOBALS['_database']->ErrorMsg();
					app_log($this->error,'error',__FILE__,__LINE__);
					$GLOBALS['_database']->RollbackTrans();
					return 0;
				}
				$GLOBALS['_database']->CommitTrans();
			}
		}
	}
?>