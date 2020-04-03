<?php
	namespace Storage;

	class FileMetaData extends \ORM\BaseModel {

        public $file_id;
        public $key;
        public $value;
		public $tableName = 'storage_file_metadata';
        public $fields = array('file_id','key','value');
	}
