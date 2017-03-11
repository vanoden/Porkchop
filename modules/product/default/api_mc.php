<?php
    ###############################################
    ### Handle API Request for product			###
    ### communications							###
    ### A. Caravello 8/12/2013               	###
    ###############################################

	app_log('Request: '.print_r($_REQUEST,true),'debug',__FILE__,__LINE__);

	###############################################
	### Load API Objects						###
    ###############################################
	# Call Requested Event
	if ($_REQUEST["method"]) {
		# Call the Specified Method
		$function_name = $_REQUEST["method"];
		$function_name();
		exit;
	}
	# Only Developers Can See The API
	elseif (! $GLOBALS['_SESSION_']->customer->has_role('product manager')) {
		header("location: /_product/home");
		exit;
	}

	###################################################
	### Just See if Server Is Communicating			###
	###################################################
	function ping() {
		$response = new \HTTP\Response();
		$response->header->session = $GLOBALS['_SESSION_']->code;
		$response->header->method = $_REQUEST["method"];
		$response->header->date = system_time();
		$response->message = "PING RESPONSE";
		$response->success = 1;
		header('Content-Type: application/xml');
		print XMLout($response);
	}
	###################################################
	### Add a Product								###
	###################################################
	function addProduct() {
		$product = new \Product\Item();

		$product->add(
			array(
				'code'			=> $_REQUEST['code'],
				'name'			=> $_REQUEST['name'],
				'description'	=> $_REQUEST['description'],
				'status'		=> $_REQUEST['status'],
				'type'			=> $_REQUEST['type']
			)
		);
		if ($product->error) error("Error adding product: ".$product->error);
		$response = new \HTTP\Response();
		$response->success = 1;
		$response->product = $product;

		header('Content-Type: application/xml');
		print XMLout($response);
	}

	###################################################
	### Update a Product							###
	###################################################
	function updateProduct() {
		$product = new \Product\Item();
		$product->get($_REQUEST['code']);
		if ($product->error) error("Error finding product: ".$product->error);
		if (! $product->id) error("Product not found");

		$product->update(
			array(
				'name'			=> $_REQUEST['name'],
				'type'			=> $_REQUEST['type'],
				'status'		=> $_REQUEST['status'],
				'description'	=> $_REQUEST['description'],
			)
		);
		if ($product->error) error("Error updating product: ".$product->error);
		$response = new \HTTP\Response();
		$response->success = 1;
		$response->product = $product;

		header('Content-Type: application/xml');
		print XMLout($response);
	}

	###################################################
	### Get Specified Product						###
	###################################################
	function getProduct() {
		$product = new \Product\Item();
		$product->get($_REQUEST['code']);

		if ($product->error) error("Error getting product: ".$product->error);
		$response = new \HTTP\Response();
		$response->success = 1;
		$response->product = $product;

		header('Content-Type: application/xml');
		print XMLout($response);
	}

	###################################################
	### Find matching Product						###
	###################################################
	function findProducts() {
		$productlist = new \Product\ItemList();
		$parameters = array();
		if (isset($_REQUEST['code'])) $parameters["code"] = $_REQUEST['code'];
		if (isset($_REQUEST['name'])) $parameters["name"] = $_REQUEST['name'];
		if (isset($_REQUEST['status'])) $parameters["status"] = $_REQUEST['status'];
		$products = $productlist->find($parameters);
		if ($productlist->error) error("Error finding products: ".$productlist->error);
		$response = new \HTTP\Response();
		$response->success = 1;
		$response->product = $products;

		header('Content-Type: application/xml');
		print XMLout($response);
	}

	###################################################
	### Add a Relationship							###
	###################################################
	function addRelationship() {
		$_product = new Product();
		if (defined($_REQUEST['parent_code']))
		{
			$parent = $_product->get($_REQUEST['parent_code']);
			if (! $parent->id) error("Parent product '".$_REQUEST['parent_code']."' not found");
			$_REQUEST['parent_id'] = $parent->id;
		}
		if ($_REQUEST['child_code'])
		{
			$child = $_product->get($_REQUEST['child_code']);
			if (! $child->id) error("Child product '".$_REQUEST['child_code']."' not found");
			$_REQUEST['child_id'] = $child->id;
		}
		if (! $_REQUEST['child_id'])
			error("child_id or valid child_code required");

		$_relationship = new ProductRelationship();
		$relationship = $_relationship->add(
			array(
				'parent_id'	=> $_REQUEST['parent_id'],
				'child_id'	=> $_REQUEST['child_id'],
			)
		);
		if ($_relationship->error) error("Error adding relationship: ".$_relationship->error);
		$response = new \HTTP\Response();
		$response->success = 1;
		$response->relationship = $relationship;

		header('Content-Type: application/xml');
		print XMLout($response);
	}

	###################################################
	### Get a Relationship							###
	###################################################
	function getRelationship() {
		$_product = new Product();
		if ($_REQUEST['parent_code']) {
			$parent = $_product->get($_REQUEST['parent_code']);
			$_REQUEST['parent_id'] = $parent->id;
		}
		if ($_REQUEST['child_code']) {
			$child = $_product->get($_REQUEST['child_code']);
			$_REQUEST['child_id'] = $child->id;
		}
		if (! $_REQUEST['child_id'])
			error("child_id or valid child_code required");

		$_relationship = new ProductRelationship();
		$relationship = $_relationship->get($_REQUEST['parent_id'],$_REQUEST['child_id']);

		if ($_relationship->error) error("Error getting relationship: ".$_relationship->error);
		$response = new \HTTP\Response();
		$response->success = 1;
		$response->relationship = $relationship;

		header('Content-Type: application/xml');
		print XMLout($response);
	}

	###################################################
	### Find Relationships							###
	###################################################
	function findRelationships() {
		$_product = new Product();
		if ($_REQUEST['parent_code'])
		{
			$parent = $_product->get($_REQUEST['parent_code']);
			$_REQUEST['parent_id'] = $parent->id;
		}
		if ($_REQUEST['child_code'])
		{
			$child = $_product->get($_REQUEST['child_code']);
			$_REQUEST['child_id'] = $child->id;
		}
		if (preg_match('/^\d+$/',$_REQUEST['parent_id'])) $parameters['parent_id'] = $_REQUEST['parent_id'];
		if ($_REQUEST['child_id']) $parameters['child_id'] = $_REQUEST['child_id'];
		
		$_relationship = new ProductRelationship();
		$relationships = $_relationship->find($parameters);

		if ($_relationship->error) error("Error finding relationships: ".$_relationship->error);
		$response = new \HTTP\Response();
		$response->success = 1;
		$response->relationship = $relationships;

		header('Content-Type: application/xml');
		print XMLout($response);
	}

	###################################################
	### Add a Group									###
	###################################################
	function addGroup() {
		$_group = new ProductGroup();
		$group = $_group->add(
			array(
				'code'		=> $_REQUEST['code'],
				'name'		=> $_REQUEST['name'],
			)
		);
		if ($_group->error) error("Error adding group: ".$_group->error);
		$response = new \HTTP\Response();
		$response->success = 1;
		$response->group = $group;

		header('Content-Type: application/xml');
		print XMLout($response);
	}

	###################################################
	### Update a Group								###
	###################################################
	function updateGroup() {
		$_group = new ProductGroup();
		list($group) = $_group->find(
			array(
				'code' 		=> $_REQUEST['code'],
			)
		);
		if ($_group->error) error("Error finding group: ".$_group->error);
		if (! $group->id) error("Group '".$_REQUEST['code']."' not found");
		$group = $_group->update(
			$collection->id,
			array(
				'name'			=> $_REQUEST['name'],
				'description'	=> $_REQUEST['description']
			)
		);
		if ($_group->error) error("Error adding group: ".$_group->error);
		$response = new \HTTP\Response();
		$response->success = 1;
		$response->group = $group;

		header('Content-Type: application/xml');
		print XMLout($response);
	}

	###################################################
	### Find matching Group							###
	###################################################
	function findGroups() {
		$_group = new ProductGroup();
		$groups = $_group->find(
			array(
				'code' 			=> $_REQUEST['code'],
				'description'	=> $_REQUEST['description'],
			)
		);
		if ($_group->error) error("Error finding groups: ".$_group->error);
		$response = new \HTTP\Response();
		$response->success = 1;
		$response->group = $groups;

		header('Content-Type: application/xml');
		print XMLout($response,array("stylesheet" => "monitor.collections.xsl"));
	}

	###################################################
	### Add Product to Group						###
	###################################################
	function addGroupProduct() {
		if (! preg_match('/^[\w\-\.\_\s]+$/',$_REQUEST['group_code'])) error("group_code required for addGroupProduct method");
		if (! preg_match('/^[\w\-\.\_\s]+$/',$_REQUEST['product_code'])) error("group_code required for addGroupProduct method");

		$_group = new ProductGroup();
		list($group) = $_group->find(
			array(
				'code' 				=> $_REQUEST['collection_code'],
			)
		);
		if ($_group->error) error("Error finding group: ".$_group->error);
		if (! $group->id) error("Group not found");

		$_product = new Product();
		list($product) = $_product->find(
			array(
				'code' 				=> $_REQUEST['product_code'],
			)
		);
		if ($_product->error) error("Error finding product: ".$_product->error);
		if (! $product->id) error("Product not found");

		$_group->addProduct(
			$group->id,
			$product->id,
			array(
			)
		);
		if ($_group->error) error("Error adding product to group: ".$_group->error);
		$response = new \HTTP\Response();
		$response->success = 1;
		$response->product = $product;

		header('Content-Type: application/xml');
		print XMLout($response);
	}
	###################################################
	### Find Products in Group						###
	###################################################
	function findGroupProducts() {
		$_group = new ProductGroup();
		list($group) = $_group->find(
			array(
				'code' 				=> $_REQUEST['group_code']
			)
		);
		if ($_group->error) error("Error finding group: ".$_group->error);
		if (! $group->id) error("Group not found");

		$products = $_group->products($group->id);
		if ($_group->error) error("Error finding products: ".$_group->error);

		$response = new \HTTP\Response();
		$response->success = 1;
		$response->product = $products;

		header('Content-Type: application/xml');
		print XMLout($response);
	}

	###################################################
	### Add Image to Product						###
	###################################################
	function addProductImage() {
		# Load Media Module
		require_once(MODULES."/media/_classes/default.php");

		$_product = new Product();
		$product = $_product->get($_REQUEST['product_code']);
		if ($_product->error) app_error("Error finding product: ".$_product->error,__FILE__,__LINE__);
		if (! $product->id) error("Product not found");

		$_image = new MediaItem();
		$image = $_image->get($_REQUEST['image_code']);
		if ($_image->error) app_error("Error finding image: ".$_image->error,__FILE__,__LINE__);
		if (! $image->id) error("Image not found");


		$_product->addImage($product->id,$image->id,$_REQUEST['label']);
		if ($_product->error) app_error("Error adding image: ".$_product->error,__FILE__,__LINE__);
		$response = new \HTTP\Response();
		$response->success = 1;

		header('Content-Type: application/xml');
		print XMLout($response);
	}
	###################################################
	### Add Metadata to Product						###
	###################################################
	function addProductMeta() {
		$_product = new Product();
		$product = $_product->get($_REQUEST['code']);
		if ($_product->error) app_error("Error finding product: ".$_product->error,__FILE__,__LINE__);
		if (! $product->id) error("Product not found");

		$_product->addMeta($product->id,$_REQUEST['key'],$_REQUEST['value']);
		if ($_product->error) app_error("Error adding metadata: ".$_product->error,__FILE__,__LINE__);
		$response = new \HTTP\Response();
		$response->success = 1;

		header('Content-Type: application/xml');
		print XMLout($response);
	}
    function schemaVersion() {
        $schema = new \Product\Schema();
        if ($schema->error) {
            app_error("Error getting version: ".$schema->error,__FILE__,__LINE__);
        }
        $version = $schema->version();
        $response = new \HTTP\Response();
        $response->success = 1;
        $response->version = $version;
        header('Content-Type: application/xml');
        print XMLout($response);
    }
    function schemaUpgrade() {
        $schema = new \Product\Schema();
        if ($schema->error) {
            app_error("Error getting version: ".$schema->error,__FILE__,__LINE__);
        }
        $version = $schema->upgrade();
        $response = new \HTTP\Response();
        $response->success = 1;
        $response->version = $version;
        header('Content-Type: application/xml');
        print XMLout($response);
	}
	###################################################
	### System Time									###
	###################################################
	function system_time() {
		return date("Y-m-d H:i:s");
	}
	###################################################
	### Application Error							###
	###################################################
	function app_error($message,$file = __FILE__,$line = __LINE__) {
		app_log($message,'error',$file,$line);
		error('Application Error');
	}
	###################################################
	### Return Properly Formatted Error Message		###
	###################################################
	function error($message) {
		$_REQUEST["stylesheet"] = '';
		error_log($message);
		$response->message = $message;
		$response->success = 0;
		header('Content-Type: application/xml');
		print XMLout($response,array("stylesheet" => $_REQUEST["stylesheet"]));
		exit;
	}
	###################################################
	### Convert Object to XML						###
	###################################################
	function XMLout($object,$user_options='') {
		if (0) {
			$fp = fopen('/var/log/api/monitor.log', 'a');
			fwrite($fp,"#### RESPONSE ####\n");
			fwrite($fp, print_r($object,true));
			fclose($fp);
		}

		require 'XML/Unserializer.php';
    	require 'XML/Serializer.php';
    	$options = array(
    	    XML_SERIALIZER_OPTION_INDENT        => '    ',
    	    XML_SERIALIZER_OPTION_RETURN_RESULT => true,
			XML_SERIALIZER_OPTION_MODE			=> 'simplexml',
			'rootName'							=> 'opt'
    	);
    	$xml = &new XML_Serializer($options);
	   	if ($xml->serialize($object)) {
			//error_log("Returning ".$xml->getSerializedData());
			$output = $xml->getSerializedData();
			if ($user_options["stylesheet"]) {
				$output = "<?xml-stylesheet type=\"text/xsl\" href=\"/".$user_options["stylesheet"]."\"?>".$output;
			}
			return $output;
		}
	}
?>
