<?php
	namespace Form\Submission;

	class Answer extends \BaseModel {
		public $submission_id;
		public $question_id;
		public $aggregate_key;
		public $value;

		public function __construct($id = 0) {
			$this->_tableName = 'form_submission_answers';
			$this->_cacheKeyPrefix = $this->_tableName;
			parent::__construct($id);
		}

	/**
	 * Aggregate answer counts by aggregate_key + value for a form.
	 * @return \stdClass[] Each row has aggregate_key, value, count
	 */
	public function aggregateByFormId(int $form_id): array {
		$db = $GLOBALS['_database'];
		$sql = "
			SELECT a.aggregate_key, a.value, COUNT(*) AS cnt
			FROM form_submission_answers a
			INNER JOIN form_submissions s ON s.id = a.submission_id
			WHERE s.form_id = ?
			GROUP BY a.aggregate_key, a.value
			ORDER BY a.aggregate_key, cnt DESC
		";
		$rs = $db->Execute($sql, array($form_id));
		if (! $rs) {
			$this->error($db->ErrorMsg());
			return array();
		}
		$rows = array();
		while ($row = $rs->FetchRow()) {
			$o = new \stdClass();
			$o->aggregate_key = $row['aggregate_key'] ?? $row[0] ?? '';
			$o->value = $row['value'] ?? $row[1] ?? '';
			$o->count = (int)($row['cnt'] ?? $row[2] ?? 0);
			$rows[] = $o;
		}
		return $rows;
	}
	}
