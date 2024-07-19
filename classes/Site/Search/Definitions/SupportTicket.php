<?php

namespace Site\Search\Definitions;

class SupportTicket extends \Site\Search\Definition {

    public function __construct() {
        $this->class = '\Support\Request\ItemList';
        $this->customer_url = '/_support/ticket/';
        $this->customer_privilege = 'browse support tickets';
        $this->admin_url = '/_support/request_item/';
        $this->admin_privilege = 'manage support requests';
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
            $supportRequestItem = new \Support\Request($ticket->request_id);
            if ($this->ifPrivilege($this->admin_privilege) || ($this->ifPrivilege($this->customer_privilege) && $this->ifBelongsToOrganization($supportRequestItem->organization_id))) {
                $result = new \Site\Search\Result();
                $result->type = 'support';
                $result->summary = $ticket->serial_number . " " . $ticket->description;
                $result->customer_url = $this->customer_url . $ticket->request_id;
                $result->admin_url = ($this->ifPrivilege($this->admin_privilege)) ? $this->admin_url . $ticket->request_id : '';
                $result->admin_privilege = $this->admin_privilege;
                $result->customer_privilege = $this->customer_privilege;
                $results->addResult($result);
            }
        }
        return $results->searchResults;
    }
}
