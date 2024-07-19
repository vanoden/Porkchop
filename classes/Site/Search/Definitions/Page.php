<?php
namespace Site\Search\Definitions;

class Page extends \Site\Search\Definition {

    public function __construct() {
        $this->class = '\Content\MessageList';
        $this->customer_url = '/';
        $this->customer_privilege = '';
        $this->admin_url = '/_site/content_block/';
        $this->admin_privilege = 'edit content messages';
    }

    public function search($search_string) {
        $page_list = new $this->class();
        $parameters = array();
        $parameters['string'] = $search_string;
        return $page_list->search($parameters);
    }

    public function summarize($search_string) {
        $results = new \Site\Search\ResultList();
        $page_list = $this->search($search_string);
        foreach ($page_list as $page) {
            $result = new \Site\Search\Result();
            $result->type = 'page';
            $result->summary = $page->name. ":" . $page->title;
            $result->customer_url = $this->customer_url . $page->target;
            $result->admin_url = $this->admin_url . $page->target;
            $result->admin_privilege = $this->admin_privilege;
            $results->addResult($result);
        }
        return $results->searchResults;
    }
}