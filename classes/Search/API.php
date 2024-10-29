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

			// Validate Input Parameters
			$validationClass = new \Search\Tag();

			if (!empty($_REQUEST['class']))
				if ( $validationClass->validClass($_REQUEST['class'])) $this->error("Invalid Class");
				else $parameters['class'] = $_REQUEST['class'];

			if (!empty($_REQUEST['category']))
				if (! $validationClass->validCategory($_REQUEST['category'])) $this->error("Invalid Category");
				else $parameters['category'] = $_REQUEST['category'];

			if (!empty($_REQUEST['value'])) 
				if (! $validationClass->validValue($_REQUEST['value'])) $this->error("Invalid Value");
				else $parameters['value'] = $_REQUEST['value'];

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
		 * Add a tag to an object
		 * @return void
		 */
		public function addTagObject() {
			$this->requirePrivilege('manage tags');

			$parameters = [];

			// Validate Input Parameters
			$validationClass = new \Search\Tag();
			if (! $validationClass->validClass($_REQUEST['class']))	$this->error("Invalid Class");
			else $parameters['class'] = $_REQUEST['class'];

			if (! $validationClass->validCategory($_REQUEST['category'])) $this->error("Invalid Category");
			else $parameters['category'] = $_REQUEST['category'];

			if (! $validationClass->validValue($_REQUEST['value'])) $this->error("Invalid Value");
			else $parameters['value'] = $_REQUEST['value'];

			if (empty($_REQUEST['id'])) $this->error("Object ID Required");
			elseif (is_numeric($_REQUEST['id'])) $parameters['id'] = $_REQUEST['id'];
			else $this->error("Invalid Object ID");

			$classString = $validationClass->_class($parameters['class']);
			$object = new $classString($_REQUEST['id']);

			if (! $object->id) $this->error("Invalid Object ID");

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
		 * Find objects matching specific criteria
		 * @return void
		 */
		public function findTagObjects() {
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

			$tagList = new \Search\TagList();
			$tags = $tagList->find($parameters);
			if ($tagList->error()) {
				$this->error($tagList->error());
			}

			$objects = [];
			foreach ($tags as $tag) {
				$classString = $validationClass->_class($tag->class);
				$object = new $classString($tag->id);
				$object->class = $tag->class;
				if ($object->id) array_push($objects,$object);
			}

			$response = new \APIResponse();
			if (count($tags) > 0) $response->AddElement('object',$objects);
			$response->print();
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