<?php
	namespace Content;

	/* Base Class for APIs */
	class API extends \API {

		public function __construct() {
			$this->_admin_role = 'content operator';
			$this->_name = 'content';
			$this->_version = '0.1.1';
			$this->_release = '2021-07-20';
			$this->_schema = new \Content\Schema();
			parent::__construct();
		}

		###################################################
		### Get A Filtered List of Blocks				###
		###################################################
		public function findBlocks() {
			# Initiate Product Object
			$block_list = new \Content\BlockList();

			# Find Matching Threads
			$parameters = array();
			if (isset($_REQUEST['name'])) $parameters['name'] = $_REQUEST['name'];
			if (isset($_REQUEST['options'])) $parameters['options'] = $_REQUEST['options'];
			$blocks = $block_list->find($parameters);

			# Error Handling
			if ($block_list->error()) $this->error($block_list->error());
			else{
				$response = new \APIResponse();
				$response->AddElement('block',$blocks);
				$response->print();
			}
		}
		
		###################################################
		### Search for Blocks         				###
		###################################################
		public function searchBlocks() {
			# Initiate Product Object
			$block_list = new \Content\BlockList();

			# Find Matching Threads
			$parameters = array();
			
			if (isset($_REQUEST['string'])) $parameters['string'] = $_REQUEST['string'];
			$blocks = $block_list->search($parameters);

			# Error Handling
			if ($block_list->error()) $this->error($block_list->error());
			else {
				$response = new \APIResponse();
				$response->AddElement('block',$blocks);
				$response->print();
			}
		}
		
		###################################################
		### Get Details regarding Specified Block		###
		###################################################
		public function getBlock() {
			# Initiate Product Object
			$block = new \Content\Block($_REQUEST['id']);
			if (! isset($_REQUEST['id'])) {
				if (isset($_REQUEST['code']) && $_REQUEST['code']) $_REQUEST['target'] = $_REQUEST['code'];
				if (empty($_REQUEST['target'])) $_REQUEST['target'] = '';

				# Find Matching Threads
				$block->get($_REQUEST['target']);
			}

			# Error Handling
			if ($block->error()) $this->error($block->error());
			else{
				$response = new \APIResponse();
				$response->AddElement('block',$block);
				$response->print();
			}
		}

		public function getMessage() {
			$this->getBlock();
		}
		###################################################
		### Get Details regarding Specified Product		###
		###################################################
		public function addBlock() {
			# Initiate Product Object
			$content = new \Content\Block();

			# Find Matching Threads
			$block = $content->add(
				array (
					'name'			=> $_REQUEST['name'],
					'target'		=> $_REQUEST['target'],
					'title'			=> $_REQUEST['title'],
					'content'		=> $_REQUEST['content']
				)
			);

			# Error Handling
			if ($content->error()) $this->error($content->error());
			else{
				$response = new \APIResponse();
				$response->AddElement('block',$block);
				$response->print();
			}
		}

		###################################################
		### Update Specified Block					###
		###################################################
		public function updateBlock() {
			# Initiate Product Object
			$block = new \Content\Block();
			if (isset($_REQUEST['id']) && $_REQUEST['id']) {
				$block->id = $_REQUEST['id'];
				if (! $block->details()) $this->error("Block id ".$_REQUEST['id']." not found");
			}
			elseif (isset($_REQUEST['target']) && $_REQUEST['target']) {
				if (! $block->get($_REQUEST['target'])) $this->error("Block '".$_REQUEST['target']."' not found");
			}
			else $this->error("Must provide block id or target");
			if (! $block->id) $this->error("Block '".$_REQUEST['id']."' not found");

			$parameters = array();
			if (isset($_REQUEST['name'])) $parameters['name'] = $_REQUEST['name'];
			if (isset($_REQUEST['title'])) $parameters['title'] = $_REQUEST['title'];
			if (isset($_REQUEST['content'])) $parameters['content'] = $_REQUEST['content'];

			# Find Matching Threads
			$block->update($parameters);

			# Error Handling
			if ($block->error()) $this->error($block->error());
			else{
				$response = new \APIResponse();
				$response->AddElement('block',$block);
				$response->print();
			}
		}

		###################################################
		### Purge Cache of Specified Block			###
		###################################################
		public function purgeBlock() {
			# Initiate Product Object
			$block = new Block();

			# Get Block
			if (! $block->get($_REQUEST['target'])) $this->error($block->error());

			if (! $block->exists())
			$this->error("Unable to find matching block");

			# Purge Cache for block
			$block->purge_cache($block->id);

			# Error Handling
			if ($block->error()) $this->error($block->error());
			else{
				$response = new \APIResponse();
				$response->print();
			}
		}

		public function _methods() {
			return array(
				'ping'			=> array(),
				'parse'			=> array(
					'description'	=> 'Parse a content block',
					'parameters'	=> array(
						'string'	=> array(
							'required' => true,
							'validation_method' => 'Content::Block::safeString()'
						)
					)
				),
				'findBlocks'	=> array(
					'description'	=> 'Find matching content blocks',
					'parameters'	=> array(
						'name'		=> array(),
						'options'	=> array(),
					),
				),
				'searchBlocks'	=> array(
					'description'	=> 'Search for content blocks',
					'parameters'	=> array(
						'string'		=> array(
							'required' => true,
							'validation_method' => 'Content::Block::safeString()'
						),
					)
				),
				'getBlock'	=> array(
					'description'	=> 'Get details regarding specified content block',
					'parameters'	=> array(
						'target'	=> array(
							'required' => true,
							'validation_method' => 'Content::Messsage::validCode()'
						),
						'code'	=> array(
							'required' => true,
							'validation_method' => 'Content::Block::validCode()'
						),
					),
				),
				'getMessage'	=> array(
					'decription'	=> 'Get details regarding specified content block',
					'deprecated'	=> true,
					'hidden'	=> true,
					'parameters'	=> array(
						'target'	=> array(
							'required' => true,
							'validation_method' => 'Content::Block::validCode()'
						),
						'code'	=> array(
							'required' => true,
							'validation_method' => 'Content::Block::validCode()'
						),
					)
					),
				'addBlock'	=> array(
					'description'	=> 'Add a new block',
					'token_required'	=> true,
					'privilege_required'	=> 'edit content blocks',
					'parameters'	=> array(
						'target'	=> array(
							'required' => true,
							'validation_method' => 'Content::Block::validTarget()',
						),
						'name'		=> array(
							'required' => true,
							'validation_method' => 'Content::Block::validName()',
						),
						'title'		=> array(
							'required' => true,
							'validation_method' => 'Content::Block::validName()',
						),
						'content'	=> array(
							'required' => true,
							'type' => 'textarea',
							'validation_method' => 'Content::Block::validContent()',
						),
						'custom_1'	=> array(
							'validation_method' => 'Content::Block::validName()',
						),
						'custom_2'	=> array(
							'validation_method' => 'Content::Block::validName()',
						),
						'custom_3'	=> array(
							'validation_method' => 'Content::Block::validName()',
						),
					),
				),
				'updateBlock'	=> array(
					'description'	=> 'Update a block',
					'token_required'	=> true,
					'privilege_required'	=> 'edit content blocks',
					'parameters'	=> array(
						'id'		=> array(
							'requirement_group' => 0,
							'content-type'	=> 'int',
							'hidden'	=> true,
						),
						'target'	=> array(
							'requirement_group' => 1,
							'validation_method' => 'Content::Block::validTarget()',
						),
						'name'		=> array(
							'validation_method' => 'Content::Block::validName()',
						),
						'title'		=> array(
							'validation_method' => 'Content::Block::validName()',
						),
						'content'	=> array(
							'type' => 'textarea',
							'validation_method' => 'Content::Block::validContent()',
						),
						'custom_1'	=> array(
							'validation_method' => 'Content::Block::validName()',
						),
						'custom_2'	=> array(
							'validation_method' => 'Content::Block::validName()',
						),
						'custom_3'	=> array(
							'validation_method' => 'Content::Block::validName()',
						),
					),
				),
				'purgeBlock'	=> array(
					'description'	=> 'Purge the cache for a block',
					'token_required'	=> true,
					'privilege_required'	=> 'edit content blocks',
					'parameters'	=> array(
						'id'		=> array(
							'required' => true,
							'content-type'	=> 'int',
						),
						'target'	=> array(
							'required' => true,
							'validation_method' => 'Content::Block::validTarget()',
						),
					),
				),
			);
		}
	}
