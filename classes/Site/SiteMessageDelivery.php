<?php
	namespace Site;

	class SiteMessageDelivery extends \ORM\BaseModel {
	
        public $id;
        public $message_id;
        public $user_id;
        public $date_viewed;
        public $date_acknowledged;
        public $tableName = 'site_message_deliveries';
        public $fields = array('id','message_id','user_id','date_viewed','date_acknowledged');

		public function __construct($id = null) {
			if (isset($id)) {
				$this->id = $id;
				$this->details();
			}
		}
        public function get($message_id,$user_id) {
            $get_object_query = "
                SELECT  id
                FROM    site_message_deliveries
                WHERE   message_id = ?
                AND     user_id = ?
            ";
            $rs = $GLOBALS['_database']->Execute($get_object_query,array($message_id,$user_id));
            if (! $rs) {
                $this->error("SQL Error in Site::SiteMessageDelivery::get(): ".$GLOBALS['_database']->ErrorMsg());
                return false;
            }
            
            list($this->id) = $rs->FetchRow();
            return $this->details();
        }
		public function message() {
			return new \Site\SiteMessage($this->message_id);
		}

		public function acknowledge() {
			return $this->update(array('date_acknowledged' => date('Y-m-d H:i:s')));
		}

		public function view() {
			return $this->update(array('date_viewed' => date('Y-m-d H:i:s')));
		}

		public function acknowledged() {
			if (!empty($this->date_acknowledged)) return true;
			return false;
		}

		public function viewed() {
			if (!empty($this->date_viewed)) return true;
			return false;
		}
	}
