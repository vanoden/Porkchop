<?php
namespace Site\Search\Definitions;

class Customer extends \Site\Search\Definition {

    public function __construct() {        
        $this->class = '\Register\CustomerList';
        $this->customer_url = '/_customer/';
        $this->admin_url = '/_admin/customer/';
        $this->admin_privilege = 'customer_admin';
    }

    public function search($search_string) {
        $customer_list = new $this->class();
        return $customer_list->search($search_string);
    }

    public function summarize($search_string) {
        $results = new \Site\Search\ResultList();
        $customer_list = $this->search($search_string);
        foreach ($customer_list as $customer) {
            $result = new \Site\Search\Result();
            $result->type = 'customer';
            $result->summary = $customer->first_name. " " . $customer->last_name;
            $result->customer_url = $this->customer_url . $customer->id;
            $result->admin_url = $this->admin_url . $customer->id;
            $result->admin_privilege = $this->admin_privilege;
            $results->addResult($result);
        }
        return $results->searchResults;
    }
}