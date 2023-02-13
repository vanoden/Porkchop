<?php
	namespace Site\Page;

use Aws\Iam\Exception\DeleteConflictException;
use Elasticsearch\Endpoints\Indices\Alias\Delete;

class Metadata Extends \BaseClass {
		public $id;
		public $template;
		public $page_id;
		public $key;
		public $value;

		public function __construct($id = null) {
			$this->_tableName = 'page_metadata';

			if (!empty($id)) {
				$this->id = $id;
				$this->details();
			}
		}

		###################################################
		### Get Page Metadata							###
		###################################################
		public function __call($name,$parameters) {
			if ($name == "get") $this->getMeta($parameters[0],$parameters[1]);
		}

		public function getMeta($page_id,$key) {
			$this->page_id = $page_id;
			$this->key = $key;

			$get_object_query = "
				SELECT	id
				FROM	page_metadata
				WHERE	page_id = ?
				AND		`key` = ?
			";

			$rs = $GLOBALS['_database']->Execute(
				$get_object_query,
				array($page_id,$key)
			);
			if (! $rs) {
				$this->SQLError($GLOBALS['_database']->ErrorMsg());
				return false;
			}
			list($id) = $rs->FetchRow();
			$this->id = $id;
			return $this->details();
		}

		public function set($value) {
			if (! $this->page_id) {
				$this->error("Invalid page id");
				return false;
			}
			if (! isset($this->key)) {
				$this->error("Invalid key name");
				return false;
			}

			$set_data_query = "
				REPLACE
				INTO	page_metadata
				(		page_id,`key`,value)
				VALUES
				(		?,?,?)
			";
			$GLOBALS['_database']->Execute(
				$set_data_query,
				array($this->page_id,$this->key,$value)
			);
			if ($GLOBALS['_database']->ErrorMsg()) {
				$this->SQLError($GLOBALS['_database']->ErrorMsg());
				return false;
			}
			return true;
		}

		public function update($value): bool {
			$update_metadata_query = "
				UPDATE	page_metadata
				SET		value = ?
				WHERE	id = ?
			";
			$GLOBALS['_database']->Execute($update_metadata_query,array($value,$this->id));
			if ($GLOBALS['_database']->ErrorMsg()) {
				$this->SQLError($GLOBALS['_database']->ErrorMsg());
				return false;
			}
			return $this->details();
		}

		public function drop() {
			if (empty($this->id)) {
				$this->error("Metadata id not set");
				return false;
			}
			$drop_key_query = "
				DELETE
				FROM	page_metadata
				WHERE	id = ?
			";
			query_log($drop_key_query,array($this->id),true);
			$GLOBALS['_database']->Execute($drop_key_query,array($this->id));
			if ($GLOBALS['_database']->ErrorMsg()) {
				$this->SQLError($GLOBALS['_database']->ErrorMsg());
				return false;
			}
			return true;
		}
	}
