<?php
namespace Site;

class SiteMessage extends \BaseClass {

    public $user_created;
    public $recipient_id;
    public $date_created;
    public $important;
    public $subject;
    public $content;
    public $parent_id;

    public function __construct($id = 0) {
            $this->_tableName = 'site_messages';
            $this->_addFields(array('id','user_created','recipient_id','date_created','important','subject','content','parent_id'));
            parent::__construct($id);
    }
}
