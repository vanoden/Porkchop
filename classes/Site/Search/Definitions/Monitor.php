<?php
namespace Site\Search\Definitions;

class Monitor extends \Site\Search\Definition {

    public function __construct() {
        $this->class = '\Monitor\AssetList';
        $this->customer_url = '/_monitor/';
        $this->admin_url = '/_admin/monitor/';
        $this->admin_privilege = 'monitor_admin';
    }

    public function search($search_string) {
        $monitor_list = new $this->class();
        $parameters = array();
        $parameters['search'] = $search_string;
        return $monitor_list->search($parameters);
    }

    public function summarize($search_string) {
        $results = new \Site\Search\ResultList();
        $monitor_list = $this->search($search_string);
        foreach ($monitor_list as $monitor) {
            $result = new \Site\Search\Result();
            $result->type = 'monitor';
            $result->summary = $monitor->code. " " . $monitor->asset_code;
            $result->customer_url = $this->customer_url . $monitor->asset_code;
            $result->admin_url = $this->admin_url . $monitor->asset_code;
            $result->admin_privilege = $this->admin_privilege;
            $results->addResult($result);
        }
        return $results->searchResults;
    }
}