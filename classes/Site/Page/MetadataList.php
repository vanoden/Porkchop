<?php
	namespace Site\Page;

	class MetadataList {
		public $error;
		public $count = 0;

		public function find($parameters = array()) {
			$bind_params = array();
			$get_object_query = "
				SELECT	id
				FROM	page_metadata
				WHERE	id = id
			";

			if (!empty($parameters['page_id'])) {
				$get_object_query .= "
				AND		page_id = ?";
				array_push($bind_params,$parameters['page_id']);
			}

			$rs = $GLOBALS['_database']->Execute($get_object_query,$bind_params);
			if (! $rs) {
				$this->error = "SQL Error in Site::Page::MetadataList::find(): ".$GLOBALS['_database']->ErrorMsg();
				return null;
			}
			$metadatas = array();
			while(list($id) = $rs->FetchRow()) {
				$metadata = new \Site\Page\Metadata($id);
				if (preg_match('/^[\w\_][\w\-\_\.]*$/',$metadata->key)) {
					array_push($metadatas,$metadata);
					$this->count ++;
				}
			}
			return $metadatas;
		}

		public function error() {
			return $this->error;
		}

		public function count() {
			return $this->count();
		}
	}
