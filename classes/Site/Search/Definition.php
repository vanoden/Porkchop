<?php
namespace Site\Search;

class Definition extends \BaseClass {

    public $class = '';
    public $search_parameters = array();
    public $summary_field = '';
    public $customer_url = '';
    public $customer_privilege = '';
    public $admin_url = '';
    public $admin_privilege = '';

    public function ifPrivilege($privilege) {
        return $GLOBALS['_SESSION_']->customer->can($privilege);
    }

    public function ifBelongsToOrganization($organization_id) {
        return ($organization_id == $GLOBALS['_SESSION_']->customer->organization_id);
    }
}