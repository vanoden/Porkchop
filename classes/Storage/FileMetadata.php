<?php
	namespace Storage;

	class FileMetadata extends \BaseModel {

        public $key;
        public $value;

		public function __construct($id = 0,$key = null) {
            $this->_tableName = 'storage_file_metadata';
            $this->_addFields(array('file_id','key','value'));
            $this->key = $key;
            parent::__construct($id);
		}

        /**
         * get by file
         *
         * @param \Storage\File $file
         */
		public function getByFile($file) {
	        if ($file->id) {
		        $getObjectQuery = "SELECT `file_id` FROM `$this->_tableName` WHERE `file_id` = ?";

                $rs = $this->execute($getObjectQuery, array($file->id));
                if ($rs) {
                    list($file_id) = $rs->FetchRow();
                    if ($file_id) {
                        $this->id = $file_id;
                        return $this->details();
                    }
                }
                $this->error("ERROR: no records found for this value.");
                return false;
	        }
	        return false;
		}
	}
