<?php
	namespace Search;

	class API Extends \API {
		public function __construct() {
			$this->_admin_role = 'search manager';
			$this->_name = 'search';
			$this->_version = '0.1.1';
			$this->_release = '2024-10-29';
			$this->_schema = new \Search\Schema();
			parent::__construct();
		}

		/**
		 * Find all search tags matching specific criteria
		 * @return void
		 */
		public function findTags() {
			$parameters = [];

			if ($_REQUEST['class']) $parameters['class'] = $_REQUEST['class'];
			if ($_REQUEST['category']) $parameters['category'] = $_REQUEST['category'];
			if ($_REQUEST['value']) $parameters['value'] = $_REQUEST['value'];

			// Validate Input Parameters
			$validationClass = new \Search\Tag();
			if (! $validationClass->validClass($parameters['class'])) {
				$this->error("Invalid Class");
			}
			if (! $validationClass->validCategory($parameters['category'])) {
				$this->error("Invalid Category");
			}
			if (! $validationClass->validValue($parameters['value'])) {
				$this->error("Invalid Value");
			}

			$tagList = new \Search\TagList();
			$tags = $tagList->find($parameters);
			if ($tagList->error()) {
				$this->error($tagList->error());
			}

			$response = new \APIResponse();
			if (count($tags) > 0) $response->AddElement('tag',$tags);
			$response->print();
		}

		/**
		 * Add a tag for searching
		 * @return void
		 */
		public function addTag() {
			$this->requirePrivilege('manage tags');

			$parameters = [];

			// Validate Input Parameters
			$validationClass = new \Search\Tag();
			if (!empty($_REQUEST['class']) && ! $validationClass->validClass($_REQUEST['class'])) {
				$this->error("Invalid Class");
			}
			elseif (!empty($_REQUEST['class'])) $parameters['class'] = $_REQUEST['class'];
			if (!empty($_REQUEST['category']) && ! $validationClass->validCategory($_REQUEST['category'])) {
				$this->error("Invalid Category");
			}
			elseif (!empty($_REQUEST['category'])) $parameters['category'] = $_REQUEST['category'];
			if (!empty($_REQUEST['value']) && ! $validationClass->validValue($_REQUEST['value'])) {
				$this->error("Invalid Value");
			}
			elseif (!empty($_REQUEST['value'])) $parameters['value'] = $_REQUEST['value'];

			$tag = new \Search\Tag();
			if ($tag->add($parameters)) {
				$response = new \APIResponse();
				$response->AddElement('tag',$tag);
				$response->print();
			}
			else {
				$this->error($tag->error());
			}
		}

		/**
		 * Metadata for API Methods
		 */
		public function _methods() {
			$queue = new \Register\Queue();
			return array(
				'ping'	=> array(
					'description' => 'Check API Availability',
					'authentication_required' => false,
					'parameters' => array(),
					'return_element' => 'message',
					'return_type' => 'string'
				),
				'addTag'	=> array(
					'description'	=> 'Add a tag for searching',
					'authentication_required'	=> true,
					'token_required' 			=> true,
					'privilege_required' 		=> 'manage tags',
					'parameters'	=> array(
						'class'		=> array('required' => true, 'prompt' => 'Tag Class'),
						'category'	=> array('required' => true, 'prompt' => 'Tag Category'),
						'value'		=> array('required' => true, 'prompt' => 'Tag Value')
					)
				),
				'findTags'	=> array(
					'description'	=> 'Find tags matching specific criteria',
					'authentication_required'	=> false,
					'token_required' => false,
					'return_element' => 'tag',
					'return_type' => 'Search::Tag',
					'return_mime_type' => 'application/xml',
					'parameters' 	=> array(
						'class'			=> array('required' => false, 'prompt' => 'Tag Class'),
						'category'		=> array('required' => false, 'prompt' => 'Tag Category'),
						'value'			=> array('required' => false, 'prompt' => 'Tag Value')
					)
				),
				'addTagObject'	=> array(
					'description'	=> 'Add a tag to an object',
					'authentication_required'	=> true,
					'token_required' => true,
					'privilege_required' => 'manage tags',
					'parameters'	=> array(
						'class'		=> array('required' => true, 'prompt' => 'Object Class'),
						'category'	=> array('required' => true, 'prompt' => 'Tag Category'),
						'value'		=> array('required' => true, 'prompt' => 'Tag Value'),
						'id'		=> array('required' => true, 'prompt' => 'Object ID')
					)
					),
				'findTagObjects' => array(
					'description'	=> 'Find objects matching specific criteria',
					'authentication_required'	=> false,
					'token_required' => false,
					'return_element' => 'object',
					'return_mime_type' => 'application/xml',
					'parameters'	=> array(
						'class'			=> array('required' => false, 'prompt' => 'Object Class'),
						'category'		=> array('required' => false, 'prompt' => 'Object Category'),
						'value'			=> array('required' => false, 'prompt' => 'Object Value')
					)
				)
			);
		}
	}