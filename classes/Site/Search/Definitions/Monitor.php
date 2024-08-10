<?php

namespace Site\Search\Definitions;

class Monitor extends \Site\Search\Definition {

    public function __construct() {
        $this->class = '\Monitor\AssetList';
        $this->customer_url = '/_monitor/asset/';
        $this->customer_privilege = '';
        $this->admin_url = '/_monitor/admin_details/';
        $this->admin_privilege = 'manage monitors';
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
            if ($this->ifPrivilege($this->admin_privilege) || $this->ifBelongsToOrganization($monitor->organization_id)) {
                $result = new \Site\Search\Result();
                $result->type = 'monitor';
                $result->summary = $monitor->code . " " . $monitor->asset_code;
                $result->customer_url = $this->customer_url . $monitor->asset_code;
                $productItem = new \Product\Item($monitor->product_id);
                $result->admin_url = $this->admin_url . $monitor->asset_code . "/" . $productItem->code;
                $result->admin_privilege = $this->admin_privilege;
                $results->addResult($result);
            }
        }
        return $results->searchResults;
    }
}
