<?php
	namespace Site;

	class TermsOfUse Extends \BaseClass {
		public $code = '';
		public $name = '';
		public $description = '';
	
		public function __construct(int $id = null) {
			$this->_tableName = 'site_terms_of_use';
			$this->_cacheKeyPrefix = $this->_tableName;

			parent::__construct($id);
		}

		public function add(array $params): bool {
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

		public function addVersion($params): int {
			$version = new TermsOfUseVersion();
			if ($version->add(array('tou_id' => $this->id, 'version' => $params['version'], 'content' => $params['content']))) {
				return $version->id;
			}
			else {
				$this->error($version->error());
				return 0;
			}
		}

		public function latestVersion(): TermsOfUseVersion {
			$versionList = new TermsOfUseVersionList();
			list($version) = $versionList->find(array('tou_id' => $this->id, 'status' => 'PUBLISHED', '_sort' => 'date_published', '_order' => 'desc', '_limit' => 1));
			if (! $version) return new TermsOfUseVersion();
			else return $version;
		}
	}