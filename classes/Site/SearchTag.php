<?php
namespace Site;

class SearchTag extends \BaseModel {

    public $class;
    public $category;
    public $value;

    public function __construct($id = 0) {
        $this->_tableName = 'search_tags';
        $this->_addFields(array('id', 'class', 'category', 'value'));
        parent::__construct($id);
    }
}