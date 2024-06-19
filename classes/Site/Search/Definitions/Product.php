<?php
namespace Site\Search\Definitions;

class Product extends \Site\Search\Definition {

    public function __construct() {
        $this->class = '\Product\ItemList';
        $this->customer_url = '/_product/';
        $this->admin_url = '/_admin/product/';
        $this->admin_privilege = 'product_admin';
    }

    public function search($search_string) {
        $product_list = new $this->class();
        $parameters = array();
        $parameters['search'] = $search_string;
        return $product_list->search($parameters);
    }

    public function summarize($search_string) {
        $results = new \Site\Search\ResultList();
        $product_list = $this->search($search_string);
        foreach ($product_list as $product) {
            $result = new \Site\Search\Result();
            $result->type = 'product';
            $result->summary = $product->code. " " . $product->name;
            $result->customer_url = $this->customer_url . $product->code;
            $result->admin_url = $this->admin_url . $product->code;
            $result->admin_privilege = $this->admin_privilege;
            $results->addResult($result);
        }
        return $results->searchResults;
    }
}