<?php
namespace Site\Search;

class ResultList {

    public $searchResults = array();

    public function addResult(\Site\Search\Result $result) {
        $this->searchResults[] = $result;
    }
}
