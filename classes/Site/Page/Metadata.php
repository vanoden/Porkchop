<?php
	namespace Site\Page;

use Aws\Iam\Exception\DeleteConflictException;
use Elasticsearch\Endpoints\Indices\Alias\Delete;

class Metadata {
		public $id;
		public $template;
		public $page_id;
		public $key;

		public function __construct($id = null) {
			if (!empty($id)) {
				$this->id = $id;
				$this->details();
			}
		}

		###################################################
		### Get Page Metadata							###
		###################################################
		public function get($page_id,$key) {
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
				$this->error = "SQL Error in Site::Page::Metadata::get(): ".$GLOBALS['_database']->ErrorMsg();
				return 0;
			}
			list($id) = $rs->FetchRow();
			$this->id = $id;
			return $this->details();
		}

		public function set($value) {
			if (! $this->page_id) {
				$this->error = "Invalid page id in Site::Page::Metadata::set";
				return null;
			}
			if (! isset($this->key)) {
				$this->error = "Invalid key name in Site::Page::Metadata::set";
				return null;
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
				$this->error = "SQL Error in Site::Page::Metadata::add(): ".$GLOBALS['_database']->ErrorMsg();
				return null;
			}
			return true;
		}

		public function update($value) {
			$update_metadata_query = "
				UPDATE	page_metadata
				SET		value = ?
				WHERE	id = ?
			";
			$GLOBALS['_database']->Execute($update_metadata_query,array($value,$this->id));
			if ($GLOBALS['_database']->ErrorMsg()) {
				$this->error = "SQL Error in Site::Page::Metadata::update(): ".$GLOBALS['_database']->ErrorMsg();
				return false;
			}
			return $this->details();
		}

		###################################################
		### Get Details for Metadata					###
		###################################################
		public function details() {
			$get_object_query = "
				SELECT	page_id,
						`key`,
						value
				FROM	page_metadata
				WHERE	id = ?
			";
			$rs = $GLOBALS['_database']->Execute(
				$get_object_query,
				array($this->id)
			);
			if (! $rs)
			{
				$this->error = "SQL Error in Site::Page::Metadata::details(): ".$GLOBALS['_database']->ErrorMsg();
				return 0;
			}
			if ($object = $rs->FetchNextObject(false)) {
				$this->page_id = $object->page_id;
				$this->key = $object->key;
				$this->value = $object->value;
				return true;
			}
            return false;
		}

		public function drop() {
			if (empty($this->id)) {
				$this->error = "Metadata id not set";
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
				$this->error = "SQL Error in Site::Page::Metadata::drop(): ".$GLOBALS['_database']->ErrorMsg();
				return false;
			}
			return true;
		}

		public function error() {
			return $this->error;
		}
	}
