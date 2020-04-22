<?php
	namespace Storage;

	class FileType extends \ORM\BaseModel {

		public $id;
		public $code;
		public $type;
		public $ref_id;
		public $tableName = 'storage_files_types';
        public $fields = array('id','code', 'type', 'ref_id');
        public $referenceTypes = array('support request','support ticket','support action','support rma','support warranty','engineering task','engineering release','engineering project','engineering product');
        
        /**
         * construct a new fileType
         * 
         * @param int $id
         * @param array $parameters, name value pairs to add and populate new object by
         */
		public function __construct($id = 0,$parameters = array()) {
			parent::__construct($id);
		}

        /**
         * add by params
         * 
         * @param array $parameters, name value pairs to add and populate new object by
         */
		public function add($parameters = array()) {
		    if (isset($parameters['code']) && isset($parameters['type']) && isset($parameters['ref_id'])) parent::add($parameters);
		    return false;
		}
	}
