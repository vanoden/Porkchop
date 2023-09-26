<?php
	namespace Site;

	class TermsOfUse Extends \BaseModel {
		public $code = '';
		public $name = '';
		public $description = '';
	
		public function __construct($id = null) {
			$this->_tableName = 'site_terms_of_use';
			$this->_cacheKeyPrefix = $this->_tableName;
			$this->_addStatus(array('NEW','PUBLISHED','RETRACTED'));
			parent::__construct($id);
		}

		public function getByCode($code) {
			$get_object_query = "
				SELECT	id
				FROM	site_terms_of_use
				WHERE	code = ?
			";
			$rs = $GLOBALS['_database']->Execute($get_object_query,array($code));
			if (! $rs) {
				$this->error = $GLOBALS['_database']->ErrorMsg();
				return null;
			}
			list($id) = $rs->FetchRow();
			if ($id > 0) {
				$this->id = $id;
				return $this->details();
			}
			return null;
		}

		public function add($params = []): bool {
			$this->clearError();

			$termsList = new \Site\TermsOfUseList();
			list($found) = $termsList->find(array('name' => $params['name']));
			if ($found->id) {
				$this->error("Duplicate Name");
				return false;
			}
			$porkchop = new \Porkchop();
			if (!$this->validName($params['name'])) {
				$this->error("valid name required");
				return false;
			}
			if (empty($params['code'])) $params['code'] = $porkchop->uuid();

			if (!$this->validCode($params['code'])) {
				$this->error("Invalid code '".$params['code']."'");
				return false;
			}

			if ($this->_ukExists($params['code'])) {
				$this->error("Code already used");
				return false;
			}

			$params['description'] = noXSS($params['description']);

			$database = new \Database\Service();

			$add_object_query = "
				INSERT
				INTO	`".$this->_tableName."`
				(		code,name)
				VALUES
				(		?,?)
			";

			$database->AddParam($params['code']);
			$database->AddParam($params['name']);

			$rs = $database->Execute($add_object_query);
			if (! $rs) {
				$this->SQLError($database->ErrorMsg());
				return false;
			}
			$this->id = $database->Insert_ID();
			return $this->update($params);
		}

		public function update($params = []): bool {
			$this->clearError();

			$database = new \Database\Service();
			$cache = $this->cache();

			$update_object_query = "
				UPDATE	`$this->_tableName`
				SET		`$this->_tableIDColumn` = `$this->_tableIDColumn`";

			if (isset($params['name']) && !$this->validName($params['name'])) {
				$this->error("Invalid name");
				return false;
			}
			elseif (isset($params['name']) && $params['name'] != $this->name) {
				$update_object_query .= ",
					name = ?";
				$database->AddParam($params['name']);
			}
			
			if (isset($params['description']) && $params['description'] != $this->description) {
				$update_object_query .= ",
						description = ?";
				$database->AddParam($params['description']);
			}

			$update_object_query .= "
				WHERE	`$this->_tableIDColumn` = ?";
			$database->AddParam($this->id);

			$database->Execute($update_object_query);
			if ($database->error()) {
				$this->SQLError($database->error());
				return false;
			}

	        // Bust Cache
			$this->clearCache();
			return $this->details();
		}

		public function addVersion($params) {
			$version = new TermsOfUseVersion();
			if ($version->add(array('tou_id' => $this->id, 'content' => $params['content']))) {
				return $version;
			}
			else {
				$this->error($version->error());
				return new \stdClass();
			}
		}

		public function latestVersion(): TermsOfUseVersion {
		
			// See if Latest Version is in Cache
			$cache = new \Cache\Item($GLOBALS['_CACHE_'], "latest_tou[".$this->id."]");
			if ($cache->exists()) {
				app_log("TOU Cache Returned");
				$object = $cache->get();
				if ($object) return $object;
			}

			// Find the Latest Version by looping through all PUBLISHED version and finding the one with the latest date
			app_log("Finding latest version of TOU");
			$versionList = new TermsOfUseVersionList();
			$versions = $versionList->find(array('tou_id' => $this->id,'status' => 'PUBLISHED'));
			if ($versionList->error()) $this->error($versionList->error());
			$date_published = '0000-00-00 00:00:00';
			$latest_id = 0;
			foreach ($versions as $version) {
				app_log("Is version ".$version->id." the latest?");
				$version_published = $version->date_published();
				app_log($version_published." vs ".$date_published);
				if ($version_published > $date_published) {
					app_log("Yes, ".$version->id." is newer");
					$date_published = $version_published;
					$latest_id = $version->id;
				} else {
					app_log("No, it is not");
				}
			}
			$version = new TermsOfUseVersion($latest_id);
			if (! $version) return new TermsOfUseVersion();
			else {
				$cache->set($version);
				return $version;
			}
		}

		public function versions() {
			$list = new TermsOfUseVersionList();
			return $list->find(array('tou_id' => $this->id), array('sort' => 'id','order' => 'desc'));
		}
	}
