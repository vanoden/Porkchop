<?php
	namespace Form;

	/* Base Class for APIs */
	class API extends \API {

		public function __construct() {
			$this->_name = 'form';
			$this->_version = '0.1.1';
			$this->_release = '2023-03-14';
			$this->_schema = new \Form\Schema();
			parent::__construct();
		}

		###################################################
		### Query Form List								###
		###################################################
		public function findForms() {
			# Default StyleSheet
			if (! isset($_REQUEST["stylesheet"])) $_REQUEST["stylesheet"] = 'form.forms.xsl';
			$this->requirePrivilege("manage forms");

			$response = new \APIResponse();

			# Initiate Form List
			$formList = new \Form\FormList();

			# Find Matching Threads
			$forms = $formList->find();

			# Error Handling
			if ($formList->error()) error($formList->error());
			else{
				$response->addElement('form',$forms);
				$response->success(true);
			}

			api_log('content',$_REQUEST,$response);

			# Send Response
			print $this->formatOutput($response);
		}
		
		###################################################
		### Get Details regarding Specified Form		###
		###################################################
		public function getForm() {
			# Default StyleSheet
			if (! $_REQUEST["stylesheet"]) $_REQUEST["stylesheet"] = 'content.message.xsl';

			# Initiate Page Object
			$form = new \Form\Form();

			# Error Handling
			$response = new \APIResponse();
			if ($form->error()) error($form->error());
			elseif ($form->id) {
				$response->addElement('form',$form);
				$response->success(true);
			}
			else $this->error("Page not found");
	
			api_log('content',$_REQUEST,$response);
	
			# Send Response
			print $this->formatOutput($response);
		}

		###################################################
		### Create a new Form							###
		###################################################
		public function addForm() {
			if (!$this->validCSRFToken()) $this->error("Invalid Request");

			$this->requirePrivilege('manage forms');

			// Build Request with Validated Parameters
			$form = new \Form\Form();
			if (! $_REQUEST['code']) $_REQUEST['code'] = uniqid();
			if (! $form->validCode($_REQUEST['code'])) error("Invalid code");
			if ($form->get($_REQUEST['code'])) error("Page already exists");

			if (! $form->validName($_REQUEST['title'])) error("Invalid title");
			if (! $form->add(array('title' => $_REQUEST['title'],'code' => $_REQUEST['view']))) {
				if ($form->error()) error("Error adding form: ".$form->error());
			}
			if (isset($_REQUEST['instructions'])) $parameters['instructions'] = $_REQUEST['instructions'];
			if (!empty($_REQUEST['method'])) {
				if (! $form->validMethod($_REQUEST['method'])) $this->error("Invalid method");
				$parameters['method'] = $_REQUEST['method'];
			}
			if (!empty($_REQUEST['action'])) {
				if (! $form->validAction($_REQUEST['action'])) $this->error("Invalid action");
				$parameters['action'] = $_REQUEST['action'];
			}

			// Send Reponse
			$response = new \APIResponse();
			$response->success(true);
			$response->addElement('form',$form);
			print $this->formatOutput($response);
		}

		###################################################
		### Update an existing Form						###
		###################################################
		public function updateForm() {
			if (!$this->validCSRFToken()) $this->error("Invalid Request");

			$this->requirePrivilege('manage forms');

			// Find Form to Update
			if (!empty($_REQUEST['id'])) {
				$form = new \Form\Form($_REQUEST['id']);
			}
			elseif (!empty($_REQUEST['code'])) {
				$form = new \Form\Form();
				if (! $form->get($_REQUEST['code'])) $this->error($form->error());
			}

			// Build Request with Validated Parameters
			$parameters = array();
			if (!empty($_REQUEST['title'])) {
				if (! $form->validName($_REQUEST['title'])) error("Invalid title");
				$parameters['title'] = $_REQUEST['title'];
			}
			if (isset($_REQUEST['instructions'])) $parameters['instructions'] = $_REQUEST['instructions'];
			if (!empty($_REQUEST['method'])) {
				if (! $form->validMethod($_REQUEST['method'])) $this->error("Invalid method");
				$parameters['method'] = $_REQUEST['method'];
			}
			if (!empty($_REQUEST['action'])) {
				if (! $form->validAction($_REQUEST['action'])) $this->error("Invalid action");
				$parameters['action'] = $_REQUEST['action'];
			}

			// Update Form
			if (! $form->update($parameters)) $this->error($form->error());

			// Send Response
			$response = new \APIResponse();
			$response->success(true);
			$response->addElement('form',$form);
			print $this->formatOutput($response);
		}

		###################################################
		### Return an Array of Form::Question Objects	###
		###################################################
		public function findQuestions() {
			if ($_REQUEST['form_code']) {
				$form = new \Form\Form();
				if (!$form->get($_REQUEST['form_code'])) {
					if ($form->error()) $this->error($form->error());
					else $this->error("Form not found");
				}
			}
			elseif ($_REQUEST['form_id']) {
				$form = new \Form\Form($_REQUEST['form_id']);
				if (!$form->exists()) {
					if ($form->error()) $this->error($form->error());
					else $this->error("Form not found");
				}
			}
			else $this->error("form required");
		
			$response = new \APIResponse();
			$response->success = 1;
			print $this->formatOutput($response);
		}

		###################################################
		### Add Question to Form						###
		###################################################
		public function addQuestion() {
			if (!$this->validCSRFToken()) $this->error("Invalid Request");

			if (! $GLOBALS['_SESSION_']->customer->can('manage forms')) $this->deny();

			# Default StyleSheet
			if (! $_REQUEST["stylesheet"]) $_REQUEST["stylesheet"] = 'form.questions.xsl';

			$response = new \APIResponse();

			# Initiate Form Object
			if ($_REQUEST['form_code']) {
				$form = new \Form\Form();
				if (!$form->get($_REQUEST['form_code'])) {
					if ($form->error()) $this->error($form->error());
					else $this->error("Form not found");
				}
			}
			elseif ($_REQUEST['form_id']) {
				$form = new \Form\Form($_REQUEST['form_id']);
				if (!$form->exists()) {
					if ($form->error()) $this->error($form->error());
					else $this->error("Form not found");
				}
			}
			else $this->error("form required");

			// Load Object for Validation
			$object = new \Form\Question();
			$parameters = array(
				'form_id'				=> $form->id
			);
			if ($object->validName($_REQUEST['name'])) $parameters['name'] = $_REQUEST['name'];
			else $this->error("Invalid name");
			$parameters['prompt'] = trim($_REQUEST['prompt']);
			if ($object->validType($_REQUEST['type'])) $parameters['type'] = $_REQUEST['type'];
			else $this->error("Invalid type");
			$parameters['help'] = trim($_REQUEST['help']);
			$parameters['validation_pattern'] = trim($_REQUEST['validation_pattern']);
			$parameters['example'] = $_REQUEST['example'];
			if ($parameters['required'] == 1) $parameters['required'] = true;
			else $parameters['required'] = false;

			// Add Question to Form
			$question = $form->addQuestion($parameters);
			if ($form->error()) $this->error($form->error());

			// Prepare Response
			$response->success(true);
			$response->addElement('question',$question);
	
			api_log('form',$_REQUEST,$response);
	
			# Send Response
			print $this->formatOutput($response);
		}
		
		###################################################
		### Update Specified Question					###
		###################################################
		public function updateQuestion() {
			if (!$this->validCSRFToken()) $this->error("Invalid Request");

			if (! $GLOBALS['_SESSION_']->customer->can('manage forms')) error("Permission Denied");
	
			# Default StyleSheet
			if (! $_REQUEST["stylesheet"]) $_REQUEST["stylesheet"] = 'form.question.xsl';
			$response = new \APIResponse();

			# Initiate Form Object
			if ($_REQUEST['id']) {
				$question = new \Form\Question($_REQUEST['id']);
				if (! $question->exists()) $this->error("Question not found");
			}
			else $this->error('question id required');

			# Validate Input!
			if (!empty($_REQUEST['name'])) {
				if ($question->validName($_REQUEST['name'])) $parameters['name'] = $_REQUEST['name'];
				else $this->error("Invalid name");
			}
			if (isset($_REQUEST['prompt'])) $parameters['prompt'] = trim($_REQUEST['prompt']);
			if (!empty($_REQUEST['type'])) {
				if ($question->validType($_REQUEST['type'])) $parameters['type'] = $_REQUEST['type'];
				else $this->error("Invalid type");
			}
			if (isset($_REQUEST['help'])) $parameters['help'] = trim($_REQUEST['help']);
			if (isset($_REQUEST['validation_pattern'])) $parameters['validation_pattern'] = trim($_REQUEST['validation_pattern']);
			if (isset($_REQUEST['example'])) $parameters['example'] = $_REQUEST['example'];
			if (isset($_REQUEST['required'])) {
				if ($_REQUEST['required'] == 1) $parameters['required'] = true;
				else $parameters['required'] = false;
			}

			# Find Update Question
			$question->update($parameters);
	
			# Error Handling
			if ($question->error()) error($question->error());
			else{
				$response->addElement('question',$question);
				$response->success(true);
			}
	
			api_log('form',$_REQUEST,$response);
			# Send Response
			print $this->formatOutput($response);
		}
		
		###################################################
		### Delete Question								###
		###################################################
		public function dropQuestion() {
			if (!$this->validCSRFToken()) $this->error("Invalid Request");

			if (! $GLOBALS['_SESSION_']->customer->can('manage forms')) error("Permission Denied");
	
			# Default StyleSheet
			if (! $_REQUEST["stylesheet"]) $_REQUEST["stylesheet"] = 'form.question.xsl';
			$response = new \APIResponse();

			# Initiate Form Object
			if ($_REQUEST['id']) {
				$question = new \Form\Question($_REQUEST['id']);
				if (! $question->exists()) $this->error("Question not found");
			}
			else $this->error('question id required');

			# Validate Input!

			# Find Update Question
			if ($question->dropOptions()) $question->drop();
			else $this->error($question->error());
	
			# Error Handling
			if ($question->error()) error($question->error());
			else $response->success = 1;

			api_log('form',$_REQUEST,$response);
			# Send Response
			print $this->formatOutput($response);
		}

		###################################################
		### Get Options for Question					###
		###################################################
		public function findQuestionOptions() {
			if (! $GLOBALS['_SESSION_']->customer->can('manage forms')) $this->deny();

			# Default StyleSheet
			if (! $_REQUEST["stylesheet"]) $_REQUEST["stylesheet"] = 'form.questions.xsl';

			$response = new \APIResponse();

			# Initiate Form Object
			if ($_REQUEST['question_id']) {
				$question = new \Form\Question($_REQUEST['question_id']);
				if (!$question->exists()) {
					if ($question->error()) $this->error($question->error());
					else $this->error("Question not found");
				}
			}
			else $this->error("question_id required");

			$options = $question->options();
			if ($question->error()) $this->error($question->error());

			// Prepare Response
			$response = new \APIResponse();
			$response->success(true);
			$response->addElement('option',$options);
	
			# Send Response
			api_log('form',$_REQUEST,$response);
			print $this->formatOutput($response);

		}

		###################################################
		### Add Option to Question						###
		###################################################
		public function addQuestionOption() {
			if (!$this->validCSRFToken()) $this->error("Invalid Request");

			if (! $GLOBALS['_SESSION_']->customer->can('manage forms')) $this->deny();

			# Default StyleSheet
			if (! $_REQUEST["stylesheet"]) $_REQUEST["stylesheet"] = 'form.questions.xsl';

			$response = new \APIResponse();

			# Initiate Form Object
			if ($_REQUEST['question_id']) {
				$question = new \Form\Question($_REQUEST['question_id']);
				if (!$question->exists()) {
					if ($question->error()) $this->error($question->error());
					else $this->error("Question not found");
				}
			}
			else $this->error("question_id required");

			// Load Object for Validation
			$object = new \Form\Question();
			$parameters = array(
				'question_id'				=> $question->id
			);
			$parameters['text'] = trim($_REQUEST['text']);
			$parameters['value'] = trim($_REQUEST['value']);
			if (isset($_REQUEST['sort_order'])) $parameters['sort_order'] = trim($_REQUEST['sort_order']);

			// Add Option to Question
			$option = $question->addOption($parameters);
			if ($question->error()) $this->error($question->error());

			// Prepare Response
			$response->success(true);
			$response->addElement('option',$option);
	
			# Send Response
			api_log('form',$_REQUEST,$response);
			print $this->formatOutput($response);
		}
		
		###################################################
		### Update Specified Option						###
		###################################################
		public function updateQuestionOption() {
			if (!$this->validCSRFToken()) $this->error("Invalid Request");

			if (! $GLOBALS['_SESSION_']->customer->can('manage forms')) error("Permission Denied");
	
			# Default StyleSheet
			if (! $_REQUEST["stylesheet"]) $_REQUEST["stylesheet"] = 'form.question.xsl';
			$response = new \APIResponse();

			# Initiate Option Object
			if ($_REQUEST['id']) {
				$option = new \Form\Question\Option($_REQUEST['id']);
				if (! $option->exists()) $this->error("Option not found");
			}
			else $this->error('option id required');

			# Validate Input!
			if (isset($_REQUEST['text'])) $parameters['text'] = trim($_REQUEST['text']);
			if (isset($_REQUEST['value'])) $parameters['value'] = trim($_REQUEST['value']);
			if (isset($_REQUEST['sort_order'])) $parameters['sort_order'] = trim($_REQUEST['sort_order']);

			# Find Update Option
			$option->update($parameters);
	
			# Error Handling
			if ($option->error()) error($option->error());
			else{
				$response->addElement('option',$option);
				$response->success(true);
			}
	
			api_log('form',$_REQUEST,$response);
			# Send Response
			print $this->formatOutput($response);
		}
		
		###################################################
		### Delete Question Option						###
		###################################################
		public function dropQuestionOption() {
			if (!$this->validCSRFToken()) $this->error("Invalid Request");

			if (! $GLOBALS['_SESSION_']->customer->can('manage forms')) error("Permission Denied");
	
			# Default StyleSheet
			if (! $_REQUEST["stylesheet"]) $_REQUEST["stylesheet"] = 'form.question.xsl';
			$response = new \APIResponse();

			# Initiate Form Object
			if ($_REQUEST['id']) {
				$option = new \Form\Question\Option($_REQUEST['id']);
				if (! $option->exists()) $this->error("Option not found");
			}
			else $this->error('option id required');

			# Find Update Question
			$option->drop();
	
			# Error Handling
			if ($option->error()) error($option->error());
			else $response->success(true);

			api_log('form',$_REQUEST,$response);

			# Send Response
			print $this->formatOutput($response);
		}

		public function _methods() {
			return array(
				'ping'			=> array(),
				'findForms'	=> array(
				),
				'addForm'	=> array(
					"title"		=> array('required' => true),
					"code"		=> array(),
					'instructions'	=> array(),
					"action"	=> array('required' => true)
				),
				'getForm'	=> array(
					'code'		=> array(),
					'id'		=> array()
				),
				'updateForm'	=> array(
					"title"		=> array('required' => true),
					"code"		=> array(),
					'instructions'	=> array(),
					'method'	=> array(
						'required',
						'options' => array(
							'get','post'
						)
					),
					"action"	=> array('required' => true)
				),
				'findQuestions'	=> array(
					'form_code'		=> array(),
					'form_id'		=> array()
				),
				'addQuestion'	=> array(
					'form_code'		=> array(),
					'form_id'		=> array(),
					'name'			=> array(),
					'text'		=> array(),
					'validation_pattern'	=> array(),
					'sort_order'	=> array(),
					'example'		=> array(),
					'required'		=> array(
						'options'	=> array('yes','no'),
					),
					'help'			=> array(),
					'type'			=> array(
							'required' => true,
							'options'	=> array(
								'hidden',
								'text',
								'checkbox',
								'select',
								'textarea',
								'submit'
							)
					)
				),
				'updateQuestion'	=> array(
					'id'		=> array('required' => true),
					'name'			=> array(),
					'text'		=> array(),
					'validation_pattern'	=> array(),
					'sort_order'	=> array(),
					'example'		=> array(),
					'required'		=> array(
						'options'	=> array('yes','no'),
					),
					'help'			=> array(),
					'type'			=> array(
							'required' => true,
							'options'	=> array(
								'hidden',
								'text',
								'checkbox',
								'select',
								'textarea',
								'submit'
							)
					)
				),
				'dropQuestion'	=> array(
					'id'		=> array('required' => true)
				),
				'findQuestionOptions'	=> array(
					'question_id'		=> array('required' => true)
				),
				'addQuestionOption'	=> array(
					'question_id'	=> array('required'),
					'text'			=> array('required'),
					'value'			=> array('required'),
					'sort_order'	=> array()
				),
				'updateQuestionOption'	=> array(
					'id'			=> array('required'),
					'text'			=> array('required'),
					'value'			=> array('required'),
					'sort_order'	=> array()
				),
				'dropQuestionOption'	=> array(
					'id'			=> array('required')
				)
			);		
		}
	}
