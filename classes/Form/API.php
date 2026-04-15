<?php
	namespace Form;

	/* Base Class for APIs */
	class API extends \API {

		public function __construct() {
			$this->_name = 'form';
			$this->_version = '0.3.0';
			$this->_release = '2026-04-10';
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
			$response->print();
		}
		
		###################################################
		### Get Details regarding Specified Form		###
		###################################################
		public function getForm() {
			if (! isset($_REQUEST["stylesheet"])) $_REQUEST["stylesheet"] = 'content.message.xsl';
			$this->requirePrivilege("manage forms");

			$form = new \Form\Form();
			if (! empty($_REQUEST['id'])) {
				$form = new \Form\Form((int)$_REQUEST['id']);
			}
			elseif (! empty($_REQUEST['code'])) {
				if (! $form->get($_REQUEST['code'])) {
					$this->error($form->error() ?: "Form not found");
				}
			}
			else {
				$this->error("code or id required");
			}

			$response = new \APIResponse();
			if (! $form->exists()) {
				$this->error("Form not found");
			}
			$response->addElement('form', $form);
			$response->success(true);
			$response->print();
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
			$code = $_REQUEST['code'] ?? $_REQUEST['view'] ?? '';
			if (! $form->add(array('title' => $_REQUEST['title'], 'code' => $code))) {
				if ($form->error()) error("Error adding form: ".$form->error());
			}
			$parameters = array();
			if (isset($_REQUEST['instructions'])) $parameters['instructions'] = $_REQUEST['instructions'];
			$form_method = null;
			if (! empty($_REQUEST['submission_method'])) {
				$form_method = trim((string) $_REQUEST['submission_method']);
			} elseif (! empty($_REQUEST['method'])) {
				$method_candidate = trim((string) $_REQUEST['method']);
				// Ignore API dispatcher method names (e.g. addForm/updateForm) and only accept form submit methods.
				if (in_array(strtolower($method_candidate), array('get', 'post'))) $form_method = $method_candidate;
			}
			if ($form_method !== null && $form_method !== '') {
				if (! $form->validMethod($form_method)) $this->error("Invalid method");
				$parameters['method'] = $form_method;
			}
			if (!empty($_REQUEST['action'])) {
				if (! $form->validAction($_REQUEST['action'])) $this->error("Invalid action");
				$parameters['action'] = $_REQUEST['action'];
			}
			if (count($parameters) && ! $form->update($parameters)) {
				$this->error($form->error());
			}

			$response = new \APIResponse();
			$response->success(true);
			$response->addElement('form',$form);
			$response->print();
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
			$form_method = null;
			if (! empty($_REQUEST['submission_method'])) {
				$form_method = trim((string) $_REQUEST['submission_method']);
			} elseif (! empty($_REQUEST['method'])) {
				$method_candidate = trim((string) $_REQUEST['method']);
				// Ignore API dispatcher method names (e.g. addForm/updateForm) and only accept form submit methods.
				if (in_array(strtolower($method_candidate), array('get', 'post'))) $form_method = $method_candidate;
			}
			if ($form_method !== null && $form_method !== '') {
				if (! $form->validMethod($form_method)) $this->error("Invalid method");
				$parameters['method'] = $form_method;
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
			$response->print();
		}

		###################################################
		### Return an Array of Form::Question Objects	###
		###################################################
		public function findQuestions() {
			$this->requirePrivilege("manage forms");

			$questions = array();
			if (! empty($_REQUEST['version_id'])) {
				$qlist = new \Form\QuestionList();
				$questions = $qlist->find(array('version_id' => (int)$_REQUEST['version_id']));
				if ($qlist->error()) $this->error($qlist->error());
			}
			elseif (! empty($_REQUEST['form_code'])) {
				$form = new \Form\Form();
				if (! $form->get($_REQUEST['form_code'])) {
					$this->error($form->error() ?: "Form not found");
				}
				$v = $form->activeVersion();
				if ($v) {
					$questions = $v->questions();
				}
			}
			elseif (! empty($_REQUEST['form_id'])) {
				$form = new \Form\Form((int)$_REQUEST['form_id']);
				if (! $form->exists()) {
					$this->error($form->error() ?: "Form not found");
				}
				$v = $form->activeVersion();
				if ($v) {
					$questions = $v->questions();
				}
			}
			else {
				$this->error("version_id or form_code or form_id required");
			}

			$response = new \APIResponse();
			$response->success(true);
			$response->addElement('question', $questions);
			$response->print();
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

			if (empty($_REQUEST['version_id'])) {
				$this->error("version_id required");
			}

			$object = new \Form\Question();
			$parameters = array(
				'version_id' => (int)$_REQUEST['version_id'],
			);
			$label = $_REQUEST['text'] ?? $_REQUEST['name'] ?? '';
			if ($object->validName($label)) {
				$parameters['text'] = trim($label);
			}
			else {
				$this->error("Invalid question text");
			}
			$parameters['prompt'] = isset($_REQUEST['prompt']) ? trim($_REQUEST['prompt']) : $parameters['text'];
			if ($object->validType($_REQUEST['type'])) {
				$parameters['type'] = $_REQUEST['type'];
			}
			else {
				$this->error("Invalid type");
			}
			$parameters['help'] = isset($_REQUEST['help']) ? trim($_REQUEST['help']) : '';
			$parameters['validation_pattern'] = isset($_REQUEST['validation_pattern']) ? trim($_REQUEST['validation_pattern']) : '';
			$parameters['example'] = $_REQUEST['example'] ?? '';
			if (isset($_REQUEST['required'])) {
				$parameters['required'] = ($_REQUEST['required'] === 1 || $_REQUEST['required'] === '1' || $_REQUEST['required'] === true);
			}
			else {
				$parameters['required'] = false;
			}
			if (isset($_REQUEST['sort_order'])) {
				$parameters['sort_order'] = (int)$_REQUEST['sort_order'];
			}
			if (isset($_REQUEST['group_id'])) {
				$parameters['group_id'] = $_REQUEST['group_id'];
			}
			if (isset($_REQUEST['aggregate_key'])) {
				$parameters['aggregate_key'] = trim($_REQUEST['aggregate_key']);
			}

			$question = $form->addQuestion($parameters);
			if ($form->error()) $this->error($form->error());

			// Prepare Response
			$response->success(true);
			$response->addElement('question',$question);
	
			api_log('form',$_REQUEST,$response);
	
			# Send Response
			$response->print();
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

			$parameters = array();
			if (!empty($_REQUEST['text'])) {
				if ($question->validName($_REQUEST['text'])) {
					$parameters['text'] = $_REQUEST['text'];
				}
				else {
					$this->error("Invalid text");
				}
			}
			elseif (!empty($_REQUEST['name'])) {
				if ($question->validName($_REQUEST['name'])) {
					$parameters['text'] = $_REQUEST['name'];
				}
				else {
					$this->error("Invalid name");
				}
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

			if (count($parameters)) {
				if (! $question->update($parameters)) {
					error($question->error());
				}
			}

			if ($question->error()) {
				error($question->error());
			}
			$response->addElement('question',$question);
			$response->success(true);
	
			api_log('form',$_REQUEST,$response);
			# Send Response
			$response->print();
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
			$response->print();
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
			$response->print();

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
			$response->print();
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

			$parameters = array();
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
	
			# Send Response
			$response->print();
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
			$response->print();
		}

		public function findVersions() {
			$this->requirePrivilege("manage forms");

			$formId = null;
			if (! empty($_REQUEST['form_id'])) {
				$formId = (int)$_REQUEST['form_id'];
			}
			elseif (! empty($_REQUEST['form_code'])) {
				$form = new \Form\Form();
				if (! $form->get($_REQUEST['form_code'])) {
					$this->error($form->error() ?: "Form not found");
				}
				$formId = (int)$form->id;
			}
			else {
				$this->error("form_id or form_code required");
			}

			$list = new \Form\VersionList();
			$versions = $list->find(array(
				'form_id' => $formId,
				'_sort' => 'id',
				'_order' => 'ASC',
			));
			if ($list->error()) {
				$this->error($list->error());
			}

			$response = new \APIResponse();
			$response->success(true);
			$response->addElement('version', $versions);
			$response->print();
		}

		public function addVersion() {
			if (! $this->validCSRFToken()) $this->error("Invalid Request");
			$this->requirePrivilege('manage forms');

			$form = new \Form\Form();
			if (! empty($_REQUEST['form_id'])) {
				$form = new \Form\Form((int)$_REQUEST['form_id']);
				if (! $form->exists()) $this->error("Form not found");
			}
			elseif (! empty($_REQUEST['form_code'])) {
				if (! $form->get($_REQUEST['form_code'])) $this->error($form->error() ?: "Form not found");
			}
			else {
				$this->error("form_id or form_code required");
			}

			$version = new \Form\Version();
			$code = trim((string)($_REQUEST['code'] ?? ''));
			$name = trim((string)($_REQUEST['name'] ?? ''));
			if ($code === '' || ! $version->validCode($code)) $this->error("Invalid code");
			if ($name === '' || ! $version->validName($name)) $this->error("Invalid name");

			$check = new \Form\Version();
			if ($check->get($code)) {
				$this->error("Version already exists");
			}

			$params = array(
				'form_id' => (int)$form->id,
				'code' => $code,
				'name' => $name,
				'description' => isset($_REQUEST['description']) ? (string)$_REQUEST['description'] : '',
				'instructions' => isset($_REQUEST['instructions']) ? (string)$_REQUEST['instructions'] : '',
			);

			if (! $version->add($params)) {
				$this->error($version->error() ?: "Could not add version");
			}

			$response = new \APIResponse();
			$response->success(true);
			$response->addElement('version', $version);
			$response->print();
		}

		public function publishVersion() {
			if (! $this->validCSRFToken()) {
				$this->error("Invalid Request");
			}
			$this->requirePrivilege('manage forms');
			if (empty($_REQUEST['version_id'])) {
				$this->error("version_id required");
			}
			$version = new \Form\Version((int)$_REQUEST['version_id']);
			if (! $version->exists()) {
				$this->error("Version not found");
			}
			$uid = isset($GLOBALS['_SESSION_']->customer->id) ? (int)$GLOBALS['_SESSION_']->customer->id : null;
			if (! $version->publish($uid)) {
				$this->error($version->error() ?: "Publish failed");
			}
			$response = new \APIResponse();
			$response->success(true);
			$response->addElement('version', $version);
			$response->print();
		}

		public function unpublishForm() {
			if (! $this->validCSRFToken()) {
				$this->error("Invalid Request");
			}
			$this->requirePrivilege('manage forms');
			$form = new \Form\Form();
			if (! empty($_REQUEST['form_code'])) {
				if (! $form->get($_REQUEST['form_code'])) {
					$this->error($form->error() ?: "Form not found");
				}
			}
			elseif (! empty($_REQUEST['form_id'])) {
				$form = new \Form\Form((int)$_REQUEST['form_id']);
				if (! $form->exists()) {
					$this->error("Form not found");
				}
			}
			else {
				$this->error("form_code or form_id required");
			}
			if (! $form->clearActiveVersion()) {
				$this->error($form->error() ?: "Unpublish failed");
			}
			$response = new \APIResponse();
			$response->success(true);
			$response->addElement('form', $form);
			$response->print();
		}

		public function aggregateFormAnswers() {
			$this->requirePrivilege("manage forms");
			$form = new \Form\Form();
			if (! empty($_REQUEST['form_code'])) {
				if (! $form->get($_REQUEST['form_code'])) {
					$this->error($form->error() ?: "Form not found");
				}
			}
			elseif (! empty($_REQUEST['form_id'])) {
				$form = new \Form\Form((int)$_REQUEST['form_id']);
				if (! $form->exists()) {
					$this->error("Form not found");
				}
			}
			else {
				$this->error("form_code or form_id required");
			}
			$answer = new \Form\Submission\Answer();
			$rows = $answer->aggregateByFormId((int)$form->id);
			if ($answer->error()) {
				$this->error($answer->error());
			}
			$versionList = new \Form\VersionList();
			$vc = count($versionList->find(array('form_id' => (int)$form->id)));
			if ($versionList->error()) {
				$this->error($versionList->error());
			}

			$response = new \APIResponse();
			$response->success(true);
			$response->addElement('aggregate', $rows);
			$vcObj = new \stdClass();
			$vcObj->count = $vc;
			$response->addElement('form_version_stats', array($vcObj));
			$response->print();
		}

		public function findSubmissions() {
			$this->requirePrivilege("manage forms");
			$form = new \Form\Form();
			if (! empty($_REQUEST['form_code'])) {
				if (! $form->get($_REQUEST['form_code'])) {
					$this->error($form->error() ?: "Form not found");
				}
			}
			elseif (! empty($_REQUEST['form_id'])) {
				$form = new \Form\Form((int)$_REQUEST['form_id']);
				if (! $form->exists()) {
					$this->error("Form not found");
				}
			}
			else {
				$this->error("form_code or form_id required");
			}
			$match = array('form_id' => $form->id);
			if (! empty($_REQUEST['object_type'])) {
				$match['object_type'] = $_REQUEST['object_type'];
			}
			if (isset($_REQUEST['object_id'])) {
				$match['object_id'] = (int)$_REQUEST['object_id'];
			}
			$list = new \Form\SubmissionList();
			$subs = $list->find($match);
			if ($list->error()) {
				$this->error($list->error());
			}
			$response = new \APIResponse();
			$response->success(true);
			$response->addElement('submission', $subs);
			$response->print();
		}

		public function duplicateVersion() {
			if (! $this->validCSRFToken()) {
				$this->error("Invalid Request");
			}
			$this->requirePrivilege('manage forms');
			if (empty($_REQUEST['version_id'])) {
				$this->error("version_id required");
			}
			$src = new \Form\Version((int)$_REQUEST['version_id']);
			if (! $src->exists()) {
				$this->error("Version not found");
			}
			$form = $src->form();
			$pc = new \Porkchop();
			$nv = new \Form\Version();
			if (! $nv->add(array(
				'form_id' => $form->id,
				'code' => $pc->biguuid(),
				'name' => 'Copy '.date('Y-m-d H:i'),
				'description' => $src->description,
				'instructions' => $src->instructions,
			))) {
				$this->error($nv->error() ?: "Could not create version");
			}
			if (! $nv->copyQuestionsFrom($src)) {
				$this->error($nv->error() ?: "Could not copy questions");
			}
			$response = new \APIResponse();
			$response->success(true);
			$response->addElement('version', $nv);
			$response->print();
		}

		public function _methods() {
			return array(
				'findForms'	=> array(
				),
				'addForm'	=> array(
					'description'	=> 'Add a form',
					'privilege_required'	=> 'manage forms',
					'token_required'	=> true,
					'return_element'	=> 'form',
					'return_type'		=> 'Form::Form',
					'parameters'	=> array(
						"title"		=> array('required' => true),
						"code"		=> array(),
						'instructions'	=> array(),
						"action"	=> array('required' => true)
					)
				),
				'getForm'	=> array(
					'description'	=> 'Get details regarding a form',
					'privilege_required'	=> 'manage forms',
					'return_element'	=> 'form',
					'return_type'		=> 'Form::Form',
					'parameters'	=> array(
						'code'		=> array(
							'requirement_group'	=> 0,
							'validation_method'	=> 'Form::Form::validCode()'
						),
						'id'		=> array(
							'requirement_group'	=> 1,
							'content-type'	=> 'integer'
						)
					)
				),
				'updateForm'	=> array(
					'description'	=> 'Update a form',
					'privilege_required'	=> 'manage forms',
					'token_required'	=> true,
					'return_element'	=> 'form',
					'return_type'		=> 'Form::Form',
					'parameters'	=> array(
						'title'		=> array('required' => true),
						'code'		=> array(
							'validation_method'	=> 'Form::Form::validCode()'
						),
						'instructions'	=> array(
							'validation_method'	=> 'Form::Form::safeString()'
						),
						'method'	=> array(
							'options' => array(
								'get','post'
							)
						),
						'action'	=> array()
					)
				),
				'findQuestions'	=> array(
					'description'	=> 'Find questions for a form',
					'privilege_required'	=> 'manage forms',
					'return_element'	=> 'question',
					'return_type'		=> 'Form::Question',
					'parameters'	=> array(
						'version_id'		=> array(
							'requirement_group'	=> 0,
							'content-type'	=> 'integer'
						),
						'form_code'		=> array(
							'requirement_group'	=> 1,
							'validation_method'	=> 'Form::Form::validCode()'
						),
						'form_id'		=> array(
							'requirement_group'	=> 2,
							'content-type'	=> 'integer'
						)
					)
				),
				'findVersions'	=> array(
					'description'	=> 'Find versions for a form',
					'privilege_required'	=> 'manage forms',
					'return_element'	=> 'version',
					'return_type'		=> 'Form::Version',
					'parameters'	=> array(
						'form_id'		=> array(
							'requirement_group'	=> 0,
							'content-type'	=> 'integer'
						),
						'form_code'		=> array(
							'requirement_group'	=> 1,
							'validation_method'	=> 'Form::Form::validCode()'
						)
					)
				),
				'addVersion'	=> array(
					'description'	=> 'Add a version to a form',
					'privilege_required'	=> 'manage forms',
					'token_required'	=> true,
					'return_element'	=> 'version',
					'return_type'		=> 'Form::Version',
					'parameters'		=> array(
						'form_id'		=> array(
							'requirement_group'	=> 0,
							'content-type'	=> 'integer'
						),
						'form_code'		=> array(
							'requirement_group'	=> 1,
							'validation_method'	=> 'Form::Form::validCode()'
						),
						'code'			=> array('required' => true),
						'name'			=> array('required' => true),
						'description'	=> array(),
						'instructions'	=> array()
					)
				),
				'addQuestion'	=> array(
					'description'	=> 'Add a question to a form version',
					'privilege_required'	=> 'manage forms',
					'token_required'	=> true,
					'return_element'	=> 'question',
					'return_type'		=> 'Form::Question',
					'parameters'		=> array(
						'form_code'		=> array(
							'requirement_group'	=> 0,
							'validation_method'	=> 'Form::Form::validCode()'
						),
						'form_id'		=> array(
							'requirement_group'	=> 1,
							'content-type'	=> 'integer'
						),
						'version_id'		=> array(
							'required'		=> true,
							'content-type'	=> 'integer'
						),
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
								'radio'
							)
						)
					)
				),
				'updateQuestion'	=> array(
					'description'	=> 'Update a question',
					'privilege_required'	=> 'manage forms',
					'token_required'	=> true,
					'return_element'	=> 'question',
					'return_type'		=> 'Form::Question',
					'parameters'		=> array(
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
								'radio'
							)
						)
					)
				),
				'dropQuestion'	=> array(
					'description'	=> 'Delete a question',
					'privilege_required'	=> 'manage forms',
					'parameters'	=> array(
						'id'		=> array(
							'required' => true,
							'content-type'	=> 'integer'
						)
					)
				),
				'findQuestionOptions'	=> array(
					'description'	=> 'Find options for a question',
					'privilege_required'	=> 'manage forms',
					'return_element'	=> 'option',
					'return_type'		=> 'Form::Question::Option',
					'parameters'	=> array(
						'question_id'		=> array(
							'required' 		=> true,
							'content-type'	=> 'integer'
						)
					)
				),
				'addQuestionOption'	=> array(
					'description'	=> 'Add an option to a question',
					'privilege_required'	=> 'manage forms',
					'token_required'	=> true,
					'return_element'	=> 'option',
					'return_type'		=> 'Form::Question::Option',
					'parameters'		=> array(
						'question_id'		=> array(
							'required' 		=> true,
							'content-type'	=> 'integer'
						),
						'text'			=> array('required' => true),
						'value'			=> array('required' => true),
						'sort_order'	=> array()
					)
				),
				'updateQuestionOption'	=> array(
					'description'	=> 'Update an option',
					'privilege_required'	=> 'manage forms',
					'token_required'	=> true,
					'return_element'	=> 'option',
					'return_type'		=> 'Form::Question::Option',
					'parameters'		=> array(
						'id'			=> array(
							'required' => true,
							'content-type'	=> 'integer'
						),
						'text'			=> array(),
						'value'			=> array(),
						'sort_order'	=> array()
					)
				),
				'dropQuestionOption'	=> array(
					'description'	=> 'Delete an option',
					'privilege_required'	=> 'manage forms',
					'token_required'	=> true,
					'parameters'	=> array(
						'id'			=> array(
							'required' => true,
							'content-type'	=> 'integer'
						)
					)
				),
				'publishVersion'	=> array(
					'description'	=> 'Publish a form version (make it live)',
					'privilege_required'	=> 'manage forms',
					'token_required'	=> true,
					'return_element'	=> 'version',
					'parameters'	=> array(
						'version_id'	=> array(
							'required' => true,
							'content-type'	=> 'integer'
						)
					)
				),
				'unpublishForm'	=> array(
					'description'	=> 'Unpublish active form version',
					'privilege_required'	=> 'manage forms',
					'token_required'	=> true,
					'return_element'	=> 'form',
					'parameters'	=> array(
						'form_code'		=> array(
							'requirement_group'	=> 0,
							'validation_method'	=> 'Form::Form::validCode()'
						),
						'form_id'		=> array(
							'requirement_group'	=> 1,
							'content-type'	=> 'integer'
						)
					)
				),
				'aggregateFormAnswers'	=> array(
					'description'	=> 'Aggregate answer counts by question key and value',
					'privilege_required'	=> 'manage forms',
					'return_element'	=> 'aggregate',
					'parameters'	=> array(
						'form_code'		=> array(
							'requirement_group'	=> 0,
							'validation_method'	=> 'Form::Form::validCode()'
						),
						'form_id'		=> array(
							'requirement_group'	=> 1,
							'content-type'	=> 'integer'
						)
					)
				),
				'findSubmissions'	=> array(
					'description'	=> 'List submissions for a form',
					'privilege_required'	=> 'manage forms',
					'return_element'	=> 'submission',
					'parameters'	=> array(
						'form_code'		=> array(
							'requirement_group'	=> 0,
							'validation_method'	=> 'Form::Form::validCode()'
						),
						'form_id'		=> array(
							'requirement_group'	=> 1,
							'content-type'	=> 'integer'
						),
						'object_type'	=> array(),
						'object_id'	=> array('content-type' => 'integer')
					)
				),
				'duplicateVersion'	=> array(
					'description'	=> 'Clone a form version including questions and options',
					'privilege_required'	=> 'manage forms',
					'token_required'	=> true,
					'return_element'	=> 'version',
					'parameters'	=> array(
						'version_id'	=> array(
							'required' => true,
							'content-type'	=> 'integer'
						)
					)
				)
			);		
		}
	}
