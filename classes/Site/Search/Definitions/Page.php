<?php
namespace Site\Search\Definitions;

class Page extends \Site\Search\Definition {

    public function __construct() {
        $this->class = 'Page';
        $this->summary_field = 'title';
        $this->customer_url = '/_page/';
        $this->admin_url = '/_admin/page/';
        $this->admin_privilege = 'page_admin';
    }

    public function search($search_string) {
        
    }

    public function summarize($search_string) {
       return array();
    }
}