<?php
namespace Site\Search\Definitions;

class EngineeringTask extends \Site\Search\Definition {
    public function __construct() {
        $this->class = '\Engineering\TaskList';
        $this->customer_url = '/_task/';
        $this->admin_url = '/_admin/task/';
        $this->admin_privilege = 'task_admin';
    }

    public function search($search_string) {
        $task_list = new $this->class();
        $parameters = array();
        $parameters['searchTerm'] = $search_string;
        return $task_list->search($parameters);
    }

    public function summarize($search_string) {
        $results = new \Site\Search\ResultList();
        $task_list = $this->search($search_string);
        foreach ($task_list as $task) {
            $result = new \Site\Search\Result();
            $result->type = 'task';
            $result->summary = $task->code. " " . $task->title;
            $result->customer_url = $this->customer_url . $task->code;
            $result->admin_url = $this->admin_url . $task->code;
            $result->admin_privilege = $this->admin_privilege;
            $results->addResult($result);
        }
        return $results->searchResults;
    }
}