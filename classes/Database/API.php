<?php
	namespace Database;

	/* Base Class for APIs */
	class API extends \API {

		public function __construct() {
			$this->_name = 'database';
			$this->_version = '0.1.1';
			$this->_release = '2024-11-15';
			parent::__construct();
		}

		public function tables() {
			$schema = new \Database\Schema();
			$tables = $schema->tables();
			if ($schema->error()) {
				$this->error($schema->error());
				return;
			}

			$response = new \APIResponse();
			$response->AddElement('table', $tables);
			$response->print();
		}

		public function columns() {
			$me = 'columns';
			$template = $this->_methods()[$me]['template'];
			$schema = new \Database\Schema();

			if (preg_match('/^\w[\w\_]*$/',$_REQUEST['table'])) {
				$columns = $schema->table($_REQUEST['table'])->columns();
				if ($schema->error()) {
					$this->error($schema->error());
				}
			}
			else {
				$this->error("Invalid table name");
			}
			$response = new \APIResponse();
			$response->stylesheet('/xslt/database.schema.table.columns.xslt');
			$response->AddElement('column', $columns);
			$response->print();
		}
	
		public function _methods() {
			return [
				'ping'			=> array(),
				'tables'	=> [
					'description'			=> 'Get list of tables in database',
					'privilege_required'	=> 'manage database',
					'response_element'		=> 'table',
					'response_type'			=> 'Database::Schema::Table',
					'parameters'			=> [
					]
				],
				'columns'	=> [
					'description'			=> 'Get list of columns in a table',
					'template'				=> '/xslt/database.schema.table.columns.xsl',
					'privilege_required'	=> 'manage database',
					'response_element'		=> 'column',
					'response_type'			=> 'Database::Schema::Column',
					'parameters'			=> [
						'table'	=> [
							'description'	=> 'Table name',
							'required'		=> true,
							'type'			=> 'string'
						]
					]
				]
			];
		}
	}