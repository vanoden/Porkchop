<?php
namespace Site\Search\Definitions;

class Customer extends \Site\Search\Definition {

    public function __construct() {        
        $this->class = '\Register\CustomerList';
        $this->customer_url = '/_register/account?customer_id=';
        $this->customer_privilege = '';
        $this->admin_url = '/_register/admin_account?customer_id=';
        $this->admin_privilege = 'manage customers';
    }

    public function search($search_string) {
        $customer_list = new $this->class();
        return $customer_list->search($search_string,0,0,true);
    }

    public function summarize($search_string) {
        $results = new \Site\Search\ResultList();
        $customer_list = $this->search($search_string);
        foreach ($customer_list as $customer) {
            if ($this->ifPrivilege($this->admin_privilege) || $this->ifBelongsToOrganization($customer->organization_id)) {
                $result = new \Site\Search\Result();
                $result->type = 'customer';
                $result->summary = $customer->first_name. " " . $customer->last_name;
                $result->customer_url = $this->customer_url . $customer->id;
                $result->admin_url = ($this->ifPrivilege($this->admin_privilege)) ? $this->admin_url . $customer->id : "";
                $result->admin_privilege = $this->admin_privilege;
                $results->addResult($result);
            }
            
        }
        return $results->searchResults;
    }
}