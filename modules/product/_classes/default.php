<?php
	###################################################
	### product.php									###
	### Class representing a product or service for	###
	### sale or use in the Root Seven System.		###
	### A. Caravello 6/7/2009						###
	###################################################

	class ProductInit
	{
		public $error;
		public $errno;

		public function __construct()
		{
			# See if Schema is Available
			$schema_list = $GLOBALS['_database']->MetaTables();

			if (! in_array("product__info",$schema_list))
			{
				# Create __info table
				$create_table_query = "
					CREATE TABLE product__info (
						label	varchar(100) not null primary key,
						value	varchar(255)
					)
				";
				$GLOBALS['_database']->Execute($create_table_query);
				if ($GLOBALS['_database']->ErrorMsg())
				{
					$this->error = "SQL Error creating info table in ProductInit::__construct: ".$GLOBALS['_database']->ErrorMsg();
					return 0;
				}
			}

			# Check Current Schema Version
			$get_version_query = "
				SELECT	value
				FROM	product__info
				WHERE	label = 'schema_version'
			";

			$rs = $GLOBALS['_database']->Execute($get_version_query);
			if (! $rs)
			{
				$this->error = "SQL Error in ProductInit::__construct: ".$GLOBALS['_database']->ErrorMsg();
				return 0;
			}

			list($current_schema_version) = $rs->FetchRow();

			if ($current_schema_version < 1)
			{
				app_log("Upgrading schema to version 1",'notice',__FILE__,__LINE__);

				# Start Transaction
				if (! $GLOBALS['_database']->BeginTrans())
					app_log("Transactions not supported",'warning',__FILE__,__LINE__);

				$create_table_query = "
					CREATE TABLE IF NOT EXISTS `product_products` (
						`id` 			int(11) NOT NULL AUTO_INCREMENT,
						`code` 			varchar(45) NOT NULL,
						`type`			enum('group','kit','inventory','unique') DEFAULT 'inventory',
						`status`		enum('ACTIVE','HIDDEN','DELETED') NOT NULL DEFAULT 'ACTIVE',
						PRIMARY KEY (`id`),
						UNIQUE KEY `uk_product_code` (`code`)
					)
				";
				$GLOBALS['_database']->Execute($create_table_query);
				if ($GLOBALS['_database']->ErrorMsg())
				{
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
				if ($GLOBALS['_database']->ErrorMsg())
				{
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
				if ($GLOBALS['_database']->ErrorMsg())
				{
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
				if ($GLOBALS['_database']->ErrorMsg())
				{
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
				if ($GLOBALS['_database']->ErrorMsg())
				{
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
				if ($GLOBALS['_database']->ErrorMsg())
				{
					$this->error = "SQL Error in ProductInit::schema_manager: ".$GLOBALS['_database']->ErrorMsg();
					app_log($this->error,'error',__FILE__,__LINE__);
					$GLOBALS['_database']->RollbackTrans();
					return 0;
				}
				$GLOBALS['_database']->CommitTrans();
			}
		}
	}
	class Product
	{
		public $id;
		public $code;
		public $name;
		public $description;

		public function __construct($code = '')
		{
			# Clear Error Info
			$this->error = '';

			# Database Initialization
			$init = new ProductInit();
			if ($init->error)
			{
				$this->error = $init->error;
				return null;
			}

			if ($code)
			{
				$this->get($code);
			}
		}

		public function defaultCategory()
		{
			$get_category_query = "
				SELECT	id
				FROM	product_products
				WHERE	code = '_root'
			";
			$rs = $GLOBALS['_database']->Execute($get_category_query);
			if (! $rs)
			{
				$this->error = $GLOBALS['_database']->ErrorMsg();
				return 0;
			}
			list($this->id) = $rs->FetchRow();
			return $this->details($this->id);
		}

		public function get($code = '')
		{
			if (! $code) $code = $this->code;

			# Prepare Query to Get Product
			$get_object_query = "
				SELECT	id
				FROM	product_products
				WHERE	code = ?
			";

			# Return new product
            $rs = $GLOBALS['_database']->Execute(
				$get_object_query,
				array($code)
			);
            if (! $rs)
            {
                $this->error = $GLOBALS['_database']->ErrorMsg();
                return 0;
            }
			else
			{
				$this->id = $rs->fields[0];
				return $this->details($this->id);
			}
		}

		public function update($id,$parameters)
		{
			if (! $GLOBALS['_SESSION_']->customer->has_role('product manager'))
			{
				$this->error = "You do not have permissions for this task.";
				app_log($GLOBALS['_SESSION_']->customer->login." failed to update products because not product manager role",'notice',__FILE__,__LINE__);
				app_log(print_r($GLOBALS['_SESSION_']->customer,true),'debug',__FILE__,__LINE__);
				return 0;
			}

			$ok_params = array(
				"status"		=> "/.+/",
				"type"			=> "/.+/",
			);

			# Prepare Query to Update Product
			$update_product_query = "
				UPDATE	product_products
				SET		id = id
			";

			# Loop Through Parameters
			foreach (array_keys($parameters) as $parameter)
			{
				if ($ok_params[$parameter])
				{
					$value = $GLOBALS['_database']->qstr($parameters[$parameter],get_magic_quotes_gpc());
					$update_product_query .= ",
					$parameter	= $value";
				}
			}

			$update_product_query .= "
				WHERE	id = ?
			";

			$rs = $GLOBALS['_database']->Execute(
				$update_product_query,
				array($id)
			);
            if (! $rs)
            {
				$this->error = $GLOBALS['_database']->ErrorMsg();
				app_log($update_product_query,'debug',__FILE__,__LINE__);
				return null;
            }
			return $this->details($id);
		}

		public function add($parameters)
		{
			if (! $GLOBALS['_SESSION_']->customer->has_role('product manager'))
			{
				$this->error = "You do not have permissions for this task.";
				app_log($GLOBALS['_SESSION_']->customer->login." failed to update products because not product manager role",'notice',__FILE__,__LINE__);
				app_log(print_r($GLOBALS['_SESSION_'],true),'debug',__FILE__,__LINE__);
				return 0;
			}
			$this->error = '';

			# Make Sure Minimum Parameters Sent
			if (! $parameters['code'])
			{
				$this->error = "Code required to add product";
				return 0;
			}
			if ($this->get($parameters['code']))
			{
				$this->error = "Code '".$parameters['code']."' is not unique";
				return 0;
			}

			if (! $parameters['type'])
			{
				$this->error = "Valid product type required";
				return 0;
			}

			# Prepare Query to Create Product
			$add_product_query = "
				INSERT
				INTO	product_products
				(		code,
						type,
						status
				)
				VALUES
				(		?,?,?)
			";

			# Return new product
            $rs = $GLOBALS['_database']->Execute(
				$add_product_query,
				array(
					$parameters["code"],
					$parameters["type"],
					$parameters['status']
				)
			);
            if (! $rs)
            {
                $this->error = "SQL Error: ".$GLOBALS['_database']->ErrorMsg();
                return 0;
            }
			$this->id = $GLOBALS['_database']->Insert_ID();

			app_log("Created Product ".$this->id,'notice',__FILE__,__LINE__);
			return $this->details($this->id);
		}

		public function find($parameters)
		{
			$this->error = '';

			$find_product_query = "
				SELECT	DISTINCT(p.id)
				FROM	product_products p
				LEFT OUTER JOIN
						product_relations r
				ON		r.product_id = p.id
				WHERE	p.status != 'DELETED'";

			# Filter on Given Parameters
			if (array_key_exists('type',$parameters))
				$find_product_query .= "
				AND		p.type = ".$GLOBALS['_database']->qstr($parameters["type"],get_magic_quotes_gpc());
			if (array_key_exists('status',$parameters))
				$find_product_query .= "
				AND		p.status = ".$GLOBALS['_database']->qstr($parameters["type"],get_magic_quotes_gpc());
			else
				$find_product_query .= "
				AND		p.status = 'ACTIVE'";

			if (array_key_exists('category_code',$parameters))
			{
				if (is_array($parameters['category_code']))
				{
					$category_ids = array();
					foreach ($parameters['category_code'] as $category_code)
					{
						list($category) = $this->find(
							array(
								code	=> $category_code
							)
						);
						array_push($category_ids,$category->id);
					}
					$find_product_query .= "
					AND	r.parent_id in (".join(',',$category_ids).")";
				}
				elseif(preg_match('/^[\w\-\_\.\s]+$/',$parameters['category_code']))
				{
					list($category) = $this->find(
						array(
							code	=> $parameters['category_code']
						)
					);
					$parameters['category_id'] = $category->id;
				}
			}

			if (array_key_exists("category",$parameters))
			{
				# Get Parent ID
				$_parent = new Product($parameters["category"]);
				$category_id = $_parent->id;

				if (! $category_id)
				{
					$this->error = "Invalid Category";
					return null;
				}
				$find_product_query .= "
				AND		r.parent_id = $category_id";
			}
			elseif (array_key_exists('category_id',$parameters)) $find_product_query .= "
				AND		r.parent_id = $category_id";

			if (array_key_exists('status',$parameters) and preg_match('/^(active|hidden|deleted)$/i',$parameters["status"])) $find_product_query .= "
				AND		p.status = ".$parameters["status"];

			if (array_key_exists('id',$parameters) and preg_match('/^\d+$/',$parameters['id'])) $find_product_query .= "
				AND		p.id = ".$parameters['id'];

			if (isset($parameters['_sort']) && preg_match('/^[\w\_]+$/',$parameters['_sort']))
				$find_product_query .= "
				ORDER BY p.".$parameters['_sort'];
			else	
				$find_product_query .= "
				ORDER BY r.view_order,p.name";

			#app_log("Find Products Query: ".preg_replace("/(\n|\r)/","",$find_product_query),'debug',__FILE__,__LINE__);
			$rs = $GLOBALS['_database']->Execute($find_product_query);
			if ($GLOBALS['_database']->ErrorMsg())
			{
				$this->error = $GLOBALS['_database']->ErrorMsg();
				return null;
			}

			$objects = array();
			while (list($id) = $rs->FetchRow())
			{
				$object = $this->details($id);
				array_push($objects,$object);
			}
			return $objects;
		}

		public function details($id = 0)
		{
			if (! $id) $id = $this->id;
			$id = preg_replace("/\D/",'',$id);

			if (! $id)
			{
				#$this->error = "No Product found for details";
				return null;
			}

			# Prepare Query to Get Product Details
			$get_details_query = "
				SELECT	id,
						code,
						status,
						type
				FROM	product_products
				WHERE	id = ?";

			$rs = $GLOBALS['_database']->Execute(
				$get_details_query,
				array($id)
			);
			if (! $rs)
			{
				$this->error = "SQL Error in Product::details: ".$GLOBALS['_database']->ErrorMsg();
				return 0;
			}

			$object = $rs->FetchNextObject(false);

			# Add Images
			$images = $this->getImages($id);
			$object->image = $images;

			# Add Metadata
			$metadata = $this->getMeta($id);
			if ($this->error) return null;
			foreach ($metadata as $label => $value)
			{
				if (! isset($object->$label))
					$object->$label = $value;
			}

			$this->id = $object->id;
			$this->code = $object->code;
			$this->name = $object->name;
			$this->description = $object->description;

			return $object;
		}

		public function inCategory($category,$product = '')
		{
			if ($product)
			{
				$_product = new Product($product);
				$product_id = $_product->{id};
			}
			else
				$product_id = $this->id;
			if (! $product_id)
			{
				$this->error = "Could not find product";
				return 0;
			}

			# Get Parent ID
			$_parent = new Product($category);
			$parent_id = $_parent->id;
			if (! $parent_id)
			{
				$this->error = "Could not find category $category";
				return 0;
			}

			# Prepare Query to Tie Product to Category
			$in_category_query = "
				SELECT	1
				FROM	product_relations
				WHERE	product_id = '".$product_id."'
				AND		parent_id = '".$parent_id."'
			";
			#error_log(preg_replace("/(\n|\r)/","",preg_replace("/\t/"," ",$in_category_query)));
			$rs = $GLOBALS['_database']->Execute($in_category_query);
			if (! $rs)
			{
				$this->error = $GLOBALS['_database']->ErrorMsg();
				return 0;
			}
			list($found) = $rs->FetchRow();
			if (! $found) $found = 0;
			return $found;
		}

		public function addToCategory($category,$product = '')
		{
			if ($product)
			{
				$_product = new Product($product);
				$product_id = $_product->{id};
			}
			else
				$product_id = $this->id;
			if (! $product_id)
			{
				$this->error = "Could not find product";
				return 0;
			}

			# Get Parent ID
			$_parent = new Product($category);
			$parent_id = $_parent->id;
			if (! $parent_id)
			{
				$this->error = "Could not find category $category";
				return 0;
			}

			# Prepare Query to Tie Product to Category
			$to_category_query = "
				INSERT
				INTO	product_relations
				(		product_id,
						parent_id
				)
				VALUES
				(		'$product_id',
						'$parent_id'
				)
			";
			#error_log(preg_replace("/(\n|\r)/","",preg_replace("/\t/"," ",$to_category_query)));
			$rs = $GLOBALS['_database']->Execute($to_category_query);
			if ($GLOBALS['_database']->ErrorMsg())
			{
				$this->error = $GLOBALS['_database']->ErrorMsg();
				return 0;
			}
			return 1;
		}

		public function getImages($id=0)
		{
			if (! $id) $id = $this->id;

			# Get Images From Database
			$get_image_query = "
				SELECT	image_id
				FROM	product_images
				WHERE	product_id = ?
			";

			$rs = $GLOBALS['_database']->Execute($get_image_query,array($id));
			if (! $rs)
			{
				$this->error = "SQL Error in Product::getImages: ".$GLOBALS['_database']->ErrorMsg();
				return 0;
			}

			# Load MediaItem Class
			include_once( MODULES."/media/_classes/default.php");
			$images = array();
			while (list($image_id) = $rs->FetchRow())
			{
				$_image = new MediaItem();
				if ($_image->error)
				{
					$this->error = "Could not load MediaItem class: ".$_image->error;
					return null;
				}
				$image = $_image->details($image_id);
				if ($_image->error)
				{
					$this->error = "Error loading image: ".$_image->error;
					return null;
				}
				array_push($images,$image);
			}
			return $images;
		}
		public function addImage($product_id,$image_id)
		{
			# Prepare Query to Tie Product to Category
			$add_image_query = "
				INSERT
				INTO	product_images
				(		product_id,
						image_id
				)
				VALUES
				(		?,?
				)
			";
			$GLOBALS['_database']->Execute($add_image_query,array($product_id,$image_id));
			if ($GLOBALS['_database']->ErrorMsg())
			{
				$this->error = "SQL Error in Product::addImage: ".$GLOBALS['_database']->ErrorMsg();
				return 0;
			}
			return 1;
		}
		public function dropImage($product_id,$image_id)
		{
			# Prepare Query to Drop Image from Product
			$drop_image_query = "
				DELETE
				FROM	product_images
				WHERE	product_id = ?
				AND		image_id = ?
			";
			$GLOBALS['_database']->Execute($drop_image_query,array($product_id,$image_id));
			if ($GLOBALS['_database']->ErrorMsg())
			{
				$this->error = "SQL Error in Product::dropImage: ".$GLOBALS['_database']->ErrorMsg();
				return 0;
			}
			return 1;
		}
		public function hasImage($product_id,$image_id)
		{
			# Prepare Query to Get Image
			$get_image_query = "
				SELECT	1
				FROM	product_images
				WHERE	product_id = ?
				AND		image_id = ?
			";
			$rs = $GLOBALS['_database']->Execute($get_image_query,$product_id,$image_id);
			if (! $rs)
			{
				$this->error = "SQL Error in Product::hasImage: ".$GLOBALS['_database']->ErrorMsg();
				return null;
			}
			list($found) = $rs->FetchRow();
			if (! $found) $found = 0;
			return $found;
		}
		public function getMeta($id)
		{
			$get_meta_query = "
				SELECT	`key`,value
				FROM	product_metadata
				WHERE	product_id = ?
			";
			$rs = $GLOBALS['_database']->Execute(
				$get_meta_query,
				array($id)
			);
			if (! $rs)
			{
				$this->error = "SQL Error in Product::getMeta: ".$GLOBALS['_database']->ErrorMsg();
				return null;
			}
			$metadata = array();
			while (list($label,$value) = $rs->FetchRow())
			{
				$metadata[$label] = $value;
			}
			return $metadata;
		}
		public function addMeta($id,$key,$value)
		{
			$add_meta_query = "
				REPLACE
				INTO	product_metadata
				(		product_id,`key`,value)
				VALUES
				(		?,?,?)
			";
			$GLOBALS['_database']->Execute(
				$add_meta_query,
				array(
					$id,$key,$value
				)
			);
			if ($GLOBALS['_database']->ErrorMsg())
			{
				$this->error = "SQL Error in Product::addMeta: ".$GLOBALS['_database']->ErrorMsg();
				return null;
			}
			return 1;
		}
	}

	class ProductRelationship
	{
		public $error;
		public function __construct()
		{
			# Database Initialization
			$init = new ProductInit();
			if ($init->error)
			{
				$this->error = $init->error;
				return 0;
			}
		}
		public function add($parameters = array())
		{
			$add_object_query = "
				INSERT
				INTO	product_relations
				(		parent_id,product_id)
				VALUES
				(		?,?)
			";
			$GLOBALS['_database']->Execute(
				$add_object_query,
				array(
					$parameters['parent_id'],
					$parameters['child_id']
				)
			);
			if ($GLOBALS['_database']->ErrorMsg())
			{
				$this->error = "SQL Error in ProductRelationship::add: ".$GLOBALS['_database']->ErrorMsg();
				return null;
			}
			return $this->get($parameters['parent_id'],$parameters['child_id']);
		}
		public function get($parent_id,$child_id)
		{
			$get_object_query = "
				SELECT	parent_id,
						product_id child_id
				FROM	product_relations
				WHERE	parent_id = ?
				AND		product_id = ?
			";
			$rs = $GLOBALS['_database']->Execute(
				$get_object_query,
				array(
					$parent_id,
					$child_id
				)
			);
			if ($GLOBALS['_database']->ErrorMsg())
			{
				$this->error = "SQL Error in ProductRelationship::get: ".$GLOBALS['_database']->ErrorMsg();
				return null;
			}
			$array = $rs->FetchRow();
			return (object) $array;
		}
		public function find($parameters)
		{
			$find_objects_query = "
				SELECT	parent_id,
						product_id child_id
				FROM	product_relations
				WHERE	product_id = product_id
			";
			if (preg_match('/^\d+$/',$parameters['parent_id']))
				$find_objects_query .= "
				AND		parent_id = ".$GLOBALS['_database']->qstr($parameters['parent_id'],get_magic_quotes_gpc());
			if ($parameters['child_id'])
				$find_objects_query .= "
				AND		child_id = ".$GLOBALS['_database']->qstr($parameters['child_id'],get_magic_quotes_gpc());

			$find_objects_query .= "
				ORDER BY view_order
			";

			$rs = $GLOBALS['_database']->Execute($find_objects_query);
			if ($GLOBALS['_database']->ErrorMsg())
			{
				$this->error = "SQL Error in ProductRelationship::find: ".$GLOBALS['_database']->ErrorMsg();
				return null;
			}
			$objects = array();
			while(list($parent_id,$child_id) = $rs->FetchRow())
			{
				$object = $this->get($parent_id,$child_id);
				if ($this->error) return null;
				array_push($objects,$object);
			}
			return $objects;
		}
	}
	class ProductCategory
	{
		public $error;
		public function __construct()
		{
			# Database Initialization
			$init = new ProductInit();
			if ($init->error)
			{
				$this->error = $init->error;
				return 0;
			}
		}
	}

	class Unique
	{
		public $product_id;
		public $serial_number;
		public $error;

		public function __construct()
		{
			# Database Initialization
			$init = new ProductInit();
			if ($init->error)
			{
				$this->error = $init->error;
				return 0;
			}
		}
	}
?>
