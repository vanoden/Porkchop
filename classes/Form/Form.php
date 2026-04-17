<?php
	/** @class Form\Form
	 * Represents a form, which can have multiple versions.  Only one version of a form can be active at a time.  A form has a code, title, description, instructions, action, and method.  The code is used to load the form, and must be unique.
	 */
	namespace Form;

	class Form Extends \BaseModel {
		public $code;				// Unique code for this form, used to load specific forms
		public $title;				// Title of the form, used for display purposes
		public $instructions;		// Instructions for the form, used for display purposes
		public $description;		// Description of the form, used for display purposes
		public $action;				// Action URL for the form, where the form will be submitted
		public $method = 'post';	// Method for the form, either 'get' or 'post'
		public $active_version_id;	// Published version shown to visitors (nullable)

		public function __construct($id = null) {
			$this->_tableName = 'form_forms';
			$this->_cacheKeyPrefix = $this->_tableName;

			parent::__construct($id);
			// BaseModel::update() only writes keys in $this->_fields; that list stays empty until populated.
			$this->_fields();
		}

		public function validTitle($string): bool {
			return is_string($string) && preg_match('/^[\w\s\-\.\']+$/u', $string);
		}

		public function validMethod($string) {
			if (preg_match('/^(get|post)$/i',$string)) return true;
			return false;
		}

		public function validAction($url) {
			if ($url === null || $url === '') {
				return true;
			}
			if (! is_string($url)) {
				return false;
			}
			$url = trim($url);
			if ($url === '') {
				return true;
			}
			// Absolute http(s): host, optional port, path, optional query/fragment
			if (preg_match($this->_patterns['absolute_http'], $url)) {
				return true;
			}
			// Porkchop internal path, e.g. _form/show
			if (preg_match($this->_patterns['internal_path'], $url)) {
				return true;
			}
			// Relative URLs: /foo, ./bar, ../baz, thank-you, path/to/page?x=1#h
			if (preg_match($this->_patterns['disallowed_scheme'], $url)) {
				return false;
			}
			if (strncmp($url, '//', 2) === 0) {
				return false;
			}
			if (strpos($url, '://') !== false) {
				return false;
			}
			if (preg_match($this->_patterns['disallowed_chars'], $url)) {
				return false;
			}
			return true;
		}

		/** @return Version|null Published version for public display and submissions */
		public function activeVersion(): ?Version {
			if (empty($this->active_version_id)) {
				return null;
			}
			$v = new Version((int)$this->active_version_id);
			return $v->exists() ? $v : null;
		}

		/** @return Version[] */
		public function versions(): array {
			$list = new VersionList();
			return $list->find(array(
				'form_id' => $this->id,
				'_sort' => 'id',
				'_order' => 'DESC',
			));
		}

		/** @return Question[] Questions on the active published version */
		public function questions(): array {
			$av = $this->activeVersion();
			if (! $av) {
				return array();
			}
			return $av->questions();
		}

		/**
		 * Add a question to a specific version (typically a draft being edited).
		 * @param array $parameters version_id required; text or name for label; aggregate_key optional (for cross-version stats)
		 */
		public function addQuestion(array $parameters): ?Question {
			if (empty($parameters['version_id'])) {
				$this->error('version_id required');
				return null;
			}
			$ver = new Version((int)$parameters['version_id']);
			if (! $ver->exists() || (int)$ver->form_id !== (int)$this->id) {
				$this->error('Invalid version for this form');
				return null;
			}
			if (! empty($parameters['name']) && empty($parameters['text'])) {
				$parameters['text'] = $parameters['name'];
			}
			if (empty($parameters['text'])) {
				$this->error('Question text required');
				return null;
			}
			if (empty($parameters['aggregate_key'])) {
				$pc = new \Porkchop();
				$parameters['aggregate_key'] = substr(str_replace(array('-', '_'), '', $pc->biguuid()), 0, 32);
			}
			$q = new Question();
			if (! $q->add($parameters)) {
				$this->error($q->error());
				return null;
			}
			return $q;
		}

		/** Point the live form at this version (publish). */
		public function setActiveVersion(Version $version): bool {
			if ((int)$version->form_id !== (int)$this->id) {
				$this->error('Version does not belong to this form');
				return false;
			}
			return $this->update(array(
				'active_version_id' => $version->id,
			));
		}

		/** Take form offline (no published version). */
		public function clearActiveVersion(): bool {
			return $this->update(array('active_version_id' => null));
		}

		/**
		 * Validate POSTed answers against the active version and persist submission + answers.
		 * @param array $answerInput question_id => string|array
		 * @return array{success:bool,errors:string[],submission:?Submission}
		 */
		public function submitAnswers(array $answerInput, ?string $object_type = null, ?int $object_id = null): array {
			$version = $this->activeVersion();
			if (! $version) {
				return array(
					'success' => false,
					'errors' => array('This form is not accepting submissions.'),
					'submission' => null,
				);
			}
			return $this->submitAnswersForVersion($version, $answerInput, $object_type, $object_id);
		}

		/**
		 * Validate POSTed answers for a specific version (same rules as submitAnswers).
		 * Used for staff preview of a draft; public site uses submitAnswers + active version only.
		 */
		public function submitAnswersForVersion(Version $version, array $answerInput, ?string $object_type = null, ?int $object_id = null): array {
			$out = array('success' => false, 'errors' => array(), 'submission' => null);
			if ((int)$version->form_id !== (int)$this->id) {
				$out['errors'][] = 'Version does not belong to this form.';
				return $out;
			}
			$questions = $version->questions();
			$normalized = array();
			foreach ($questions as $q) {
				if ($q->type === 'hidden') {
					$normalized[$q->id] = $answerInput[$q->id] ?? $q->text;
					continue;
				}
				if ($q->type === 'submit') {
					continue;
				}
				$raw = $answerInput[$q->id] ?? null;
				if ($q->type === 'checkbox') {
					$raw = isset($answerInput[$q->id]) && is_array($answerInput[$q->id]) ? $answerInput[$q->id] : array();
				}
				if ($q->required) {
					if ($q->type === 'checkbox') {
						if (! is_array($raw) || count($raw) < 1) {
							$out['errors'][] = 'A required question was not answered (question '.$q->id.').';
							continue;
						}
					} elseif ($raw === null || $raw === '') {
						$out['errors'][] = 'A required question was not answered (question '.$q->id.').';
						continue;
					}
				}
				if ($raw === null || $raw === '') {
					continue;
				}
				if (is_string($raw)) {
					$raw = trim($raw);
					if (strlen($raw) > 65535) {
						$out['errors'][] = 'Answer too long.';
						continue;
					}
				}
				if (! empty($q->validation_pattern) && $q->type !== 'checkbox') {
					$check = is_array($raw) ? implode(',', $raw) : $raw;
					if (! preg_match($q->validation_pattern, $check)) {
						$out['errors'][] = 'Invalid answer format for: '.$q->text;
						continue;
					}
				}
				$normalized[$q->id] = $raw;
			}
			if (count($out['errors']) > 0) {
				return $out;
			}
			$sub = new Submission();
			$addr = $_SERVER['REMOTE_ADDR'] ?? null;
			if (! $sub->add(array(
				'form_id' => $this->id,
				'version_id' => $version->id,
				'date_submitted' => date('Y-m-d H:i:s'),
				'object_type' => $object_type,
				'object_id' => $object_id,
				'remote_addr' => is_string($addr) ? substr($addr, 0, 45) : null,
			))) {
				$out['errors'][] = $sub->error() ?: 'Could not save submission.';
				return $out;
			}
			if (! $sub->recordAnswers($normalized)) {
				$out['errors'][] = $sub->error() ?: 'Could not save answers.';
				return $out;
			}
			$out['success'] = true;
			$out['submission'] = $sub;
			return $out;
		}

		/**
		 * @param array $extraHiddens name => value for optional passthrough fields (e.g. object_type, object_id for linking)
		 * @param Version|null $versionOverride render this version instead of the published one; adds preview_version_id hidden field when set
		 */
		public function render(array $extraHiddens = array(), ?Version $versionOverride = null): void {
			$activeVersion = $versionOverride ?? $this->activeVersion();
			if (! $activeVersion) {
				print '<p class="form_error">This form is not available.</p>';
				return;
			}
			if ($versionOverride !== null && (int)$versionOverride->form_id !== (int)$this->id) {
				print '<p class="form_error">Invalid form version.</p>';
				return;
			}
			$questions = $activeVersion->questions();
			$groups = array();
			$groupList = new \Form\GroupList();
			$loadedGroups = $groupList->find(array(
				'version_id' => (int)$activeVersion->id,
				'_sort' => 'sort_order',
				'_order' => 'ASC',
			));
			if (is_array($loadedGroups)) {
				$groups = $loadedGroups;
			}
			print '<div class="form_instructions">'.htmlspecialchars((string)$activeVersion->instructions, ENT_QUOTES, 'UTF-8').'</div>'."\n";
			$action = strlen((string)$this->action) ? htmlspecialchars((string)$this->action, ENT_QUOTES, 'UTF-8') : '';
			print '<form class="porkchop-form" action="'.$action.'" method="'.htmlspecialchars((string)$this->method, ENT_QUOTES, 'UTF-8').'">';
			foreach ($extraHiddens as $hk => $hv) {
				print '<input type="hidden" name="'.htmlspecialchars((string)$hk, ENT_QUOTES, 'UTF-8').'" value="'.htmlspecialchars((string)$hv, ENT_QUOTES, 'UTF-8').'">';
			}
			if ($versionOverride !== null) {
				print '<input type="hidden" name="preview_version_id" value="'.(int)$versionOverride->id.'">';
			}
			$renderQuestion = function ($question): void {
				print '<div class="formQuestion">';
				print '<label>'.htmlspecialchars((string)$question->text, ENT_QUOTES, 'UTF-8').'</label>';
				if (!empty($question->help)) {
					print '<div class="formQuestionHelp">'.htmlspecialchars((string)$question->help, ENT_QUOTES, 'UTF-8').'</div>';
				}
				$prompt = trim((string)($question->prompt ?? ''));
				if ($prompt !== '' && preg_match('/^(select|radio|checkbox)$/i', (string)$question->type)) {
					print '<div class="formQuestionPrompt">'.htmlspecialchars($prompt, ENT_QUOTES, 'UTF-8').'</div>';
				}
				if ($question->type == 'text') {
					print '<input type="text" name="answer['.$question->id.']"';
					if ($question->required) print ' required';
					print '>';
				}
				elseif ($question->type == 'textarea') {
					print '<textarea name="answer['.$question->id.']"';
					if ($question->required) print ' required';
					print '></textarea>';
				}
				elseif ($question->type == 'select') {
					$opts = $question->options();
					if (count($opts) < 1) {
						print '<p class="form_error">No choices configured for this question.</p>';
					} else {
						print '<select name="answer['.$question->id.']"';
						if ($question->required) print ' required';
						print '>';
						foreach ($opts as $option) {
							print '<option value="'.htmlspecialchars((string)$option->value, ENT_QUOTES, 'UTF-8').'">'.htmlspecialchars((string)$option->text, ENT_QUOTES, 'UTF-8').'</option>';
						}
						print '</select>';
					}
				}
				elseif ($question->type == 'radio') {
					$opts = $question->options();
					if (count($opts) < 1) {
						print '<p class="form_error">No choices configured for this question.</p>';
					} else {
						$firstRadio = true;
						foreach ($opts as $option) {
							print '<label><input type="radio" name="answer['.$question->id.']" value="'.htmlspecialchars((string)$option->value, ENT_QUOTES, 'UTF-8').'"';
							if ($question->required && $firstRadio) print ' required';
							$firstRadio = false;
							print '>'.htmlspecialchars((string)$option->text, ENT_QUOTES, 'UTF-8').'</label>';
						}
					}
				}
				elseif ($question->type == 'checkbox') {
					$opts = $question->options();
					if (count($opts) < 1) {
						print '<p class="form_error">No choices configured for this question.</p>';
					} else {
						foreach ($opts as $option) {
							print '<label><input type="checkbox" name="answer['.$question->id.'][]" value="'.htmlspecialchars((string)$option->value, ENT_QUOTES, 'UTF-8').'"';
							print '>'.htmlspecialchars((string)$option->text, ENT_QUOTES, 'UTF-8').'</label>';
						}
					}
				}
				elseif ($question->type == 'hidden') {
					print '<input type="hidden" name="answer['.$question->id.']" value="'.htmlspecialchars((string)$question->text, ENT_QUOTES, 'UTF-8').'">';
				}
				print '</div>';
			};

			$groupsById = array();
			$questionsByGroup = array();
			foreach ($groups as $group) {
				$gid = (int)($group->id ?? 0);
				if ($gid < 1) continue;
				$groupsById[$gid] = $group;
				$questionsByGroup[$gid] = array();
			}
			$ungrouped = array();
			foreach ($questions as $question) {
				$gid = (int)($question->group_id ?? 0);
				if ($gid > 0 && isset($groupsById[$gid])) {
					$questionsByGroup[$gid][] = $question;
				}
				else {
					$ungrouped[] = $question;
				}
			}

			$sortQuestions = function (&$arr): void {
				usort($arr, function ($a, $b): int {
					$aOrder = (int)($a->sort_order ?? 50);
					$bOrder = (int)($b->sort_order ?? 50);
					if ($aOrder === $bOrder) {
						return (int)($a->id ?? 0) <=> (int)($b->id ?? 0);
					}
					return $aOrder <=> $bOrder;
				});
			};

			foreach ($questionsByGroup as $gid => $groupQuestions) {
				$sortQuestions($groupQuestions);
				$questionsByGroup[$gid] = $groupQuestions;
			}
			$sortQuestions($ungrouped);

			foreach ($groups as $group) {
				$gid = (int)($group->id ?? 0);
				if ($gid < 1 || empty($questionsByGroup[$gid])) {
					continue;
				}
				print '<div class="formGroup">';
				$title = trim((string)($group->title ?? ''));
				if ($title !== '') {
					print '<h3 class="formGroupTitle">'.htmlspecialchars($title, ENT_QUOTES, 'UTF-8').'</h3>';
				}
				$instructions = trim((string)($group->instructions ?? ''));
				if ($instructions !== '') {
					print '<div class="formGroupInstructions">'.htmlspecialchars($instructions, ENT_QUOTES, 'UTF-8').'</div>';
				}
				foreach ($questionsByGroup[$gid] as $question) {
					$renderQuestion($question);
				}
				print '</div>';
			}
			foreach ($ungrouped as $question) {
				$renderQuestion($question);
			}
			$csrf = '';
			if (isset($GLOBALS['_SESSION_']) && is_object($GLOBALS['_SESSION_']) && method_exists($GLOBALS['_SESSION_'], 'getCSRFToken')) {
				$csrf = $GLOBALS['_SESSION_']->getCSRFToken();
			}
			print '<input type="hidden" name="csrfToken" value="'.htmlspecialchars($csrf, ENT_QUOTES, 'UTF-8').'">';
			print '<input type="hidden" name="form_code" value="'.htmlspecialchars((string)$this->code, ENT_QUOTES, 'UTF-8').'">';
			print '<p class="formSubmit"><button type="submit" name="form_submit" value="1">Submit</button></p>';
			print '</form>';
		}
	}
