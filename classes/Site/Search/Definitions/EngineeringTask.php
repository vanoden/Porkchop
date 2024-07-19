<?php
namespace Site\Search\Definitions;

class EngineeringTask extends \Site\Search\Definition {
    public function __construct() {
        $this->class = '\Engineering\TaskList';
        $this->customer_url = '';
        $this->customer_privilege = '';
        $this->admin_url = '/_engineering/task/';
        $this->admin_privilege = 'browse engineering objects';
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
            if ($this->ifPrivilege($this->admin_privilege)) {
                $result = new \Site\Search\Result();
                $result->type = 'task';
                $result->summary = $task->code. " " . $task->title;
                $result->customer_url = $this->customer_url;
                $result->admin_url = $this->admin_url . $task->code;
                $result->admin_privilege = $this->admin_privilege;
                $results->addResult($result);
            }
        }
        return $results->searchResults;
    }
}