<?php
namespace Site\Search\Definitions;

class SupportTicket extends \Site\Search\Definition {

    public function __construct() {
        $this->class = '\Support\Request\ItemList';
        $this->customer_url = '/_support/ticket/';
        $this->admin_url = '/_admin/support/ticket/';
        $this->admin_privilege = 'support_admin';
    }

    public function search($search_string) {
        $ticket_list = new $this->class();
        $parameters = array();
        $parameters['searchTerm'] = $search_string;
        return $ticket_list->search($parameters);
    }

    public function summarize($search_string) {
        $results = new \Site\Search\ResultList();
        $ticket_list = $this->search($search_string);
        foreach ($ticket_list as $ticket) {
            $result = new \Site\Search\Result();
            $result->type = 'ticket';
            $result->summary = $ticket->serial_number. " " . $ticket->description;
            $result->customer_url = $this->customer_url . $ticket->serial_number;
            $result->admin_url = $this->admin_url . $ticket->serial_number;
            $result->admin_privilege = $this->admin_privilege;
            $results->addResult($result);
        }
        return $results->searchResults;
    }
}