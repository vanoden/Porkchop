<?php
	namespace Storage;

	class FileMetadata extends \ORM\BaseModel {

        public $file_id;
        public $key;
        public $value;
		public $tableName = 'storage_file_metadata';
        public $fields = array('file_id','key','value');

		public function __construct($file_id,$key = null) {
			$this->file_id = $file_id;
			if (defined($key)) {
				$this->key = $key;
			}
		}

        /**
         * get by file
         *
         * @param \Storage\File $file
         */
		public function getByFile($file) {
	      if ($file->id) {
		    $getObjectQuery = "SELECT `file_id` FROM `$this->tableName` WHERE `file_id` = ?";
		    print $getObjectQuery;
		    $rs = $this->execute($getObjectQuery, array($file->id));
            if ($rs) {
                list($file_id) = $rs->FetchRow();
                if ($file_id) {
                    $this->file_id = $file_id;
                    return $this->details();
                }
            }
            $this->_error = "ERROR: no records found for this value.";
            return false;		      
	      }
	      return false;
		}
        
	}
