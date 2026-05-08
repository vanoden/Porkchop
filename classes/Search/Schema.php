<?php
	namespace Search;

	class Schema Extends \Database\BaseSchema {
		public $module = 'search';

		public function upgrade ($max_version = 999) {
			$this->clearError();

			if ($this->version() < 1) {
				app_log("Upgrading ".$this->module." schema to version 1",'notice',__FILE__,__LINE__);
				$create_table_query = "
					CREATE TABLE IF NOT EXISTS `search_tags` (
					`id` int(11) NOT NULL AUTO_INCREMENT,
					`class` varchar(255) NOT NULL DEFAULT '',
					`category` varchar(255) NOT NULL DEFAULT '',
					`value` varchar(255) NOT NULL DEFAULT '',
					PRIMARY KEY (`id`),
					UNIQUE KEY `unique_tag` (`class`, `category`, `value`)
					) ENGINE=InnoDB;
				";
				if (! $this->executeSQL($create_table_query)) {
					$this->SQLError("create site_audit_events table: ".$this->error());
					return false;
				}

				$create_table_query = "
					CREATE TABLE IF NOT EXISTS `search_tags_xref` (
						`id` int(11) NOT NULL AUTO_INCREMENT,
						`tag_id` int(11) NOT NULL,
						`object_id` int(11) NOT NULL,
						PRIMARY KEY (`id`),
						KEY `tag_id` (`tag_id`),
						KEY `object_id` (`object_id`),
						CONSTRAINT `search_tags_xref_ibfk_1` FOREIGN KEY (`tag_id`) REFERENCES `search_tags` (`id`) ON DELETE CASCADE
					) ENGINE=InnoDB;
				";
				if (! $this->executeSQL($create_table_query)) {
					$this->SQLError("create site_audit_events table: ".$this->error());
					return false;
				}

				$this->setVersion(1);
				$GLOBALS['_database']->CommitTrans();
			}

			// Add performance indexes in version 2
			if ($this->version() < 2) {
				app_log("Upgrading ".$this->module." schema to version 2",'notice',__FILE__,__LINE__);
				
				// Add composite index for faster object tag lookups
				$add_index_query = "
					ALTER TABLE `search_tags_xref`
					ADD INDEX `idx_object_tag` (`object_id`, `tag_id`)
				";
				if (! $this->executeSQL($add_index_query)) {
					app_log("Warning: Could not add index idx_object_tag: ".$this->error(), 'warning', __FILE__, __LINE__);
				}

				// Add index for tag searches by class and value
				$add_index_query = "
					ALTER TABLE `search_tags`
					ADD INDEX `idx_class_value` (`class`, `value`)
				";
				if (! $this->executeSQL($add_index_query)) {
					app_log("Warning: Could not add index idx_class_value: ".$this->error(), 'warning', __FILE__, __LINE__);
				}

			$this->setVersion(2);
			$GLOBALS['_database']->CommitTrans();
		}

		// Migrate tags from separate tables to unified system in version 3
		if ($this->version() < 3) {
			app_log("Upgrading ".$this->module." schema to version 3 - Migrating tags to unified system",'notice',__FILE__,__LINE__);
			
			$migrated_count = 0;
			
			// Migrate product_tags
			$check_table = $GLOBALS['_database']->Execute("SHOW TABLES LIKE 'product_tags'");
			if ($check_table && $check_table->RecordCount() > 0) {
				$get_tags_query = "SELECT id, product_id, name FROM product_tags";
				$rs = $GLOBALS['_database']->Execute($get_tags_query);
				if ($rs) {
					$count = 0;
					// Try Spectros\Product\Item first, fall back to Product\Item
					$productClass = class_exists('\Spectros\Product\Item') ? '\Spectros\Product\Item' : '\Product\Item';
					
					while ($row = $rs->FetchRow()) {
						list($tag_id, $product_id, $tag_name) = $row;
						if (empty($tag_name) || empty($product_id)) continue;
						
						// Create temporary product instance and use BaseModel::addTag()
						$product = new $productClass($product_id);
						if ($product->id && $product->addTag($tag_name, '')) {
							$count++;
						} else {
							app_log("Failed to migrate tag '$tag_name' for product_id=$product_id: ".($product->error() ?: 'Unknown error'), 'warning', __FILE__, __LINE__);
						}
					}
					app_log("Migrated $count product tags",'info',__FILE__,__LINE__);
					$migrated_count += $count;
				}
			}
			
			// Migrate register_tags
			$check_table = $GLOBALS['_database']->Execute("SHOW TABLES LIKE 'register_tags'");
			if ($check_table && $check_table->RecordCount() > 0) {
				$get_tags_query = "SELECT id, type, register_id, name FROM register_tags";
				$rs = $GLOBALS['_database']->Execute($get_tags_query);
				if ($rs) {
					$type_to_class = array(
						'ORGANIZATION' => '\Register\Organization',
						'USER' => '\Register\Person',
						'CONTACT' => '\Register\Person',
						'LOCATION' => '\Register\Location'
					);
					$count = 0;
					while ($row = $rs->FetchRow()) {
						list($tag_id, $type, $register_id, $tag_name) = $row;
						if (empty($tag_name) || empty($register_id)) continue;
						
						$class = isset($type_to_class[$type]) ? $type_to_class[$type] : '\Register\Person';
						$category = $type;
						
						// Create temporary object instance and use BaseModel::addTag()
						if (class_exists($class)) {
							$object = new $class($register_id);
							if ($object->id && $object->addTag($tag_name, $category)) {
								$count++;
							} else {
								app_log("Failed to migrate tag '$tag_name' for register_id=$register_id (type=$type): ".($object->error() ?: 'Unknown error'), 'warning', __FILE__, __LINE__);
							}
						}
					}
					app_log("Migrated $count register tags",'info',__FILE__,__LINE__);
					$migrated_count += $count;
				}
			}
			
			// Migrate monitor_tags
			$check_table = $GLOBALS['_database']->Execute("SHOW TABLES LIKE 'monitor_tags'");
			if ($check_table && $check_table->RecordCount() > 0) {
				$describe_query = "DESCRIBE monitor_tags";
				$rs = $GLOBALS['_database']->Execute($describe_query);
				if ($rs) {
					$columns = array();
					while ($row = $rs->FetchRow()) {
						$columns[] = $row[0];
					}
					
					$object_id_field = null;
					if (in_array('monitor_id', $columns)) {
						$object_id_field = 'monitor_id';
					} elseif (in_array('asset_id', $columns)) {
						$object_id_field = 'asset_id';
					}
					
					if ($object_id_field && in_array('name', $columns)) {
						$get_tags_query = "SELECT id, $object_id_field, name FROM monitor_tags";
						$rs = $GLOBALS['_database']->Execute($get_tags_query);
						if ($rs) {
							// Try Product\Instance class
							$instanceClass = '\Product\Instance';
							$count = 0;
							while ($row = $rs->FetchRow()) {
								list($tag_id, $monitor_id, $tag_name) = $row;
								if (empty($tag_name) || empty($monitor_id)) continue;
								
								// Create temporary instance and use BaseModel::addTag()
								if (class_exists($instanceClass)) {
									$instance = new $instanceClass($monitor_id);
									if ($instance->id && $instance->addTag($tag_name, '')) {
										$count++;
									} else {
										app_log("Failed to migrate tag '$tag_name' for monitor_id=$monitor_id: ".($instance->error() ?: 'Unknown error'), 'warning', __FILE__, __LINE__);
									}
								}
							}
							app_log("Migrated $count monitor tags",'info',__FILE__,__LINE__);
							$migrated_count += $count;
						}
					}
				}
			}
			
			app_log("Total tags migrated: $migrated_count",'notice',__FILE__,__LINE__);
			
			$this->setVersion(3);
			$GLOBALS['_database']->CommitTrans();
		}
		
		return true;
	}

}
