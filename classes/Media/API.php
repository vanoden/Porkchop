<?php
	namespace Media;

	/* Base Class for APIs */
	class API extends \API {

		public function __construct() {
			$this->_name = 'media';
			$this->_version = '0.3.0';
			$this->_release = '2022-03-21';
			$this->_schema = new \Media\Schema();
			$this->_admin_role = 'media manager';
			parent::__construct();
		}

		###################################################
		### Add Media Item								###
		###################################################
		public function addMediaItem() {
			if (!$this->validCSRFToken()) $this->error("Invalid Request");

			# Make Sure Upload was Successful
			$this->check_upload($_FILES['file']);

			# Default StyleSheet
			if (! $_REQUEST["stylesheet"]) $_REQUEST["stylesheet"] = 'media.item.xsl';
			
			# Initiate Media Item Object
			$item = new \Media\Item();
			if ($item->error()) $this->error($item->error());

			// Parameters for new File
			$params = array(
				'code' => $_REQUEST['code'],
				'name' => $_REQUEST['name'],
				'type' => $_REQUEST['type'],
				'index' => $_REQUEST['index'],
				'mime_type' => $_FILES['file']['type'],
				'size' => $_FILES['file']['size'],
				'original_file' => $_FILES['file']['name']
			);
			# Add Document to Database
			$item->add($params);
			if ($item->error()) $this->error($item->error());

			$response = new \APIResponse();
			$response->addElement('item',$item);
			$response->print();
		}

		###################################################
		### Find Media Items							###
		###################################################
		public function findMediaItems() {
			# Default StyleSheet
			if (! $_REQUEST["stylesheet"]) $_REQUEST["stylesheet"] = 'media.items.xsl';

			# Initiate Media Item Object
			$itemlist = new \Media\ItemList();
			if ($itemlist->error()) $this->error($itemlist->error());

			# Get Items from Database
			$parameters = array();
			if ($_REQUEST['type']) $parameters['type'] = $_REQUEST['type'];
			foreach ($_REQUEST['key'] as $key) {
				$parameters[$key] = $_REQUEST['value'][$key];
			}
			$items = $itemlist->find($parameters);

			# Error Handling
			if ($itemlist->error()) $this->error($itemlist->error());
			else{
				$response = new \APIResponse();
				$response->AddElement('items',$items);
				$response->print();
			}
		}

		###################################################
		### Get Media Item								###
		###################################################
		public function getMediaItem() {
			# Default StyleSheet
			if (! $_REQUEST["stylesheet"]) $_REQUEST["stylesheet"] = 'media.item.xsl';

			# Initiate Media Item Object
			$item = new \Media\Item();
			if ($item->error()) $this->error($item->error());

			# Get Item from Database
			if (! $item->validCode($_REQUEST['code'])) {
				$this->error("Invalid code");
			}
			$item->get($_REQUEST['code']);

			# Error Handling
			if ($item->error()) $this->error($item->error());
			else{
				$response = new \APIResponse();
				$response->AddElement('item',$item);
				$response->print();
			}
		}
		
		###################################################
		### Download File								###
		###################################################
		public function downloadMediaFile() {
			# Initiate Media File Object
			$file = new \Media\File();
			if ($file->error()) $this->error($file->error());

			# Get File from Repository
			$file->load($_REQUEST['code']);

			# Error Handling
			if ($file->error()) $this->error($file->error());
			else{
				app_log("Downloading ".$file->code.", type ".$file->mime_type.", ".$file->size." bytes.",'debug',__FILE__,__LINE__);
				if ($file->size != strlen($file->content())) app_log("Size doesn't match: ".$file->size." != ".strlen($file->content()),'notice',__FILE__,__LINE__);
				header('Content-Type: '.$file->mime_type);
				header('Content-Disposition: attachment; filename='.$file->display_name());
				print ($file->content());
				exit;
			}
		}
		
		###################################################
		### downloadMediaImage							###
		###################################################
		public function downloadMediaImage() {
			# Initiate Media Image Object
			$image = new \Media\Image();
			$image->get($_REQUEST['code']);
			if ($image->error()) $this->app_error("Error getting MediaImage: ".$image->error(),'error',__FILE__,__LINE__);
			if (! $image->id) $this->error("Image not found");

			if (! $image->readable()) {
				$this->error("Image not readable");
			}

			if ($_REQUEST['width'] && $_REQUEST['height']) {
				$file_content = $image->resized($_REQUEST['height'], $_REQUEST['width']);
				if ($image->error()) $this->error("Error resizing image: ".$image->error(),'error',__FILE__,__LINE__);
			}
			else {
				# Downloading
				$file_content = $image->download();
			}
			header('Content-Length: '.strlen($file_content));
			header('Content-Type: '.$image->mime_type);
			header('Content-Disposition: attachment; filename='.$image->display_name);
			print ($file_content);
			flush();
			ob_flush();

			exit;
		}
		
		###################################################
		### Add Media Metadata							###
		###################################################
		public function setMediaMetadata() {
			# Make Sure Upload was Successful
			$this->check_upload($_FILES['file']);

			# Default StyleSheet
			if (! $_REQUEST["stylesheet"]) $_REQUEST["stylesheet"] = 'media.item.xsl';
			
			# Initiate Media Item Object
			$item = new \Media\Item();
			if ($item->error()) $this->error($item->error());

			# Find Item
			$item->get($_REQUEST['code']);
			if ($item->error()) $this->error("Error finding item: ".$item->error());
			if (! $item->id) $this->error("Item not found");

			# Add Meta Tag
			$item->setMetadata($_REQUEST['label'],$_REQUEST['value']);
			if ($item->error()) $this->error($item->error());

			$response = new \APIResponse();
			$response->addElement('item',$item);
			$response->print();
		}

		/****************************************/
		/* File Upload Check					*/
		/****************************************/
		public function check_upload($request) {
			try {
				// Undefined | Multiple Files | $_FILES Corruption Attack
				// If this request falls under any of them, treat it invalid.
				if (
					!isset($request['error']) ||
					is_array($request['error'])
				) {
					throw new \RuntimeException('Invalid parameters.');
				}
			
				// Check $_FILES['upfile']['error'] value.
				switch ($request['error']) {
					case UPLOAD_ERR_OK:
						break;
					case UPLOAD_ERR_NO_FILE:
						throw new \RuntimeException('No file sent.');
					case UPLOAD_ERR_INI_SIZE:
					case UPLOAD_ERR_FORM_SIZE:
						throw new \RuntimeException('Exceeded filesize limit.');
					default:
						throw new \RuntimeException('Unknown errors.');
				}
			
				// You should also check filesize here. 
				if ($request['size'] > 32000000) {
					throw new \RuntimeException('Exceeded filesize limit.');
				}
			} catch (\RuntimeException $e) {
				error("Problem with file upload: ".$e->getMessage());
				return 0;
			}
			return 1;
		}

		public function _methods() {
			$validationClass = new \Product\Item();
			return array(
				'ping'			=> array(),
				'addMediaItem'	=> array(
					'description'		=> 'Add a new media item',
					'authentication_required' => true,
					'return_type'		=> 'Media::Item',
					'return_element'	=> 'item',
					'token_required'	=> true,
					'parameters'		=> array(
						'code' => array(
							'type' => 'string',
							'description' => 'Unique code for the media item',
							'required' => true,
							'validation_method' => 'Media::Item::validCode()',
						),
						'name' => array(
							'type' => 'string',
							'description' => 'Name of the media item',
							'required' => true,
							'validation_method' => 'Media::Item::validName()',
						),
						'type' => array(
							'type' => 'string',
							'description' => 'Type of the media item',
							'required' => true,
							'validation_method' => 'Media::Item::validType()',
						),
					)
				),
				'findMediaItems' => array(
					'description'		=> 'Find media items based on parameters',
					'return_type'		=> 'Media::ItemList',
					'return_element'	=> 'items',
					'parameters'		=> array(
						'type' => array(
							'type' => 'string',
							'description' => 'Type of media items to find',
							'required' => false,
						),
						'key[]' => array(
							'type' => 'string',
							'description' => 'Keys to filter by',
							'required' => false,
						),
						'value[]' => array(
							'type' => 'string',
							'description' => 'Values corresponding to keys',
							'required' => false,
						),
					)
				),
				'getMediaItem' => array(
					'description'		=> 'Get a specific media item by code',
					'return_type'		=> 'Media::Item',
					'return_element'	=> 'item',
					'parameters'		=> array(
						'code' => array(
							'type' => 'string',
							'description' => 'Code of the media item to retrieve',
							'required' => true,
							'validation_method' => 'Media::Item::validCode()',
						),
					)
				),
				'downloadMediaFile' => array(
					'description'		=> 'Download a raw media file by code',
					'parameters'		=> array(
						'code' => array(
							'type' => 'string',
							'description' => 'Code of the media file to download',
							'required' => true,
							'validation_method' => 'Media::File::validCode()',
						),
					)
				),
				'downloadMediaImage' => array(
					'description'		=> 'Download a sized media image by code',
					'parameters'		=> array(
						'code' => array(
							'type' => 'string',
							'description' => 'Code of the media image to download',
							'required' => true,
							'validation_method' => 'Media::Image::validCode()',
						),
						'width' => array(
							'type' => 'integer',
							'description' => 'Width of the image to download',
							'required' => false,
							'default' => 100,
						),
						'height' => array(
							'type' => 'integer',
							'description' => 'Height of the image to download',
							'required' => false,
							'default' => 100,
						),
					)
				),
				'setMediaMetadata' => array(
					'description'		=> 'Set metadata for a media item',
					'authentication_required' => true,
					'token_required'	=> true,
					'return_type'		=> 'Media::Item',
					'return_element'	=> 'item',
					'parameters'		=> array(
						'code' => array(
							'type' => 'string',
							'description' => 'Code of the media item to update',
							'required' => true,
							'validation_method' => 'Media::Item::validCode()',
						),
						'label' => array(
							'type' => 'string',
							'description' => 'Metadata label to set',
							'required' => true,
						),
						'value' => array(
							'type' => 'string',
							'description' => 'Value for the metadata label',
							'required' => true,
						),
					)
					),
					'check_upload' => array(
						'description' => 'Check file upload for errors',
						'parameters' => array(
							'file' => array(
								'type' => 'file',
								'description' => 'File to check for upload errors',
								'required' => true,
							),
						),
					),
			);
		}
	}
