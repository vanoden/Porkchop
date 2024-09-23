<?php
namespace Media;

class File {

	public $error;
	public $id;
	public $item_id;
	public $index;
	public $mime_type;
	public $code;
	public $size;
	public $original_file;
	public $owner_id;
	public $date_uploaded;

	public function __construct($id = 0) {
		if ($id > 0) {
			$this->id = $id;
			$this->details();
		} else {
			$this->code = uniqid();
			$this->mime_type = 'text/plain';
		}
	}
	public function get($code) {
		# Get Code From Table
		$get_code_query = "
				SELECT	id
				FROM	media_files
				WHERE	code = ?
			";
		$rs = $GLOBALS['_database']->Execute(
			$get_code_query,
			array($code)
		);
		if (! $rs) {
			$this->error = "SQL Error in Media::File::get(): " . $GLOBALS['_database']->ErrorMsg();
		}
		list($id) = $rs->FetchRow();
		$this->id = $id;
		return $this->details();
	}

	public function details() {
		# Get Code From Table
		$get_code_query = "
				SELECT	id,
						code,
						size,
						timestamp,
						mime_type,
						original_file,
						date_uploaded,
						disposition,
						unix_timestamp(date_uploaded) `timestamp`
				FROM	media_files
				WHERE	id = ?
			";
		$rs = $GLOBALS['_database']->Execute(
			$get_code_query,
			array($this->id)
		);
		if (! $rs) {
			$this->error = "SQL Error in Media::File::details(): " . $GLOBALS['_database']->ErrorMsg();
			return null;
		}
		return $rs->FetchNextObject(false);
	}

	public function save($tmp_file) {
		$code = uniqid();
		if (! $index) $index = '';

		$add_object_query = "
				INSERT
				INTO	media_files
				(		`item_id`,
						`index`,
						`mime_type`,
						`code`,
						`size`,
						`original_file`,
						`owner_id`,
						`date_uploaded`
				)
				VALUES
				(		?,?,?,?,?,?,?,sysdate())
				ON DUPLICATE KEY UPDATE
					mime_type = ?,
					size = ?,
					original_file = ?
			";
		$GLOBALS['_database']->Execute(
			$add_object_query,
			array(
				$this->id,
				$this->index,
				$this->mime_type,
				$this->code,
				$this->size,
				$this->original_file,
				$GLOBALS['_SESSION_']->customer->id,
				$this->mime_type,
				$this->size,
				$this->original_file
			)
		);
		if ($GLOBALS['_database']->ErrorMsg()) {
			$this->error = "SQL Error in Media::File::save(): " . $GLOBALS['_database']->ErrorMsg();
			return null;
		}
		$id = $GLOBALS['_database']->Insert_ID();
		$this->id = $id;
		$details = $this->details();

		# Save File
		$storage_path = RESOURCES . "/_media/" . $details->code;
		app_log("Storing '$tmp_file' as '$storage_path'", 'debug', __FILE__, __LINE__);
		if (move_uploaded_file($tmp_file, $storage_path))
			return 1;
		else {
			$this->error = "Failed to add file to repository";
			return 0;
		}
	}
	public function load($code) {
		# Get File Info
		$object = $this->get($code);

		# Save File
		$path = RESOURCES . "/_media/" . $object->code;
		if (! file_exists($path)) {
			$this->error = "File not found";
			return null;
		}
		$content = file_get_contents($path);
		$object->content = $content;
		return $object;
	}
}
