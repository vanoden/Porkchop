<?php
	$page = new \Site\Page();
	$page->fromRequest();
	$page->requirePrivilege('see sales quotes');
		
	// Security - Only Register Module Operators or Managers can see other customers
	$organizationlist = new \Register\OrganizationList();
	
	// Initialize Parameter Array
	$find_parameters = array();
	$find_parameters['status'] = array('NEW','ACTIVE');

	// Get Count before Pagination
	$organizations = $organizationlist->find($find_parameters,true);
	if ($organizationlist->error()) $page->addError($organizationlist->error());

    // get members for organization
    if (isset($_REQUEST['organization_id']) && intval($_REQUEST['organization_id'])) {
        $organization = new \Register\Organization($_REQUEST['organization_id']);
        if ($organization->error()) $page->addError($organization->error());
        $members = $organization->members('human',array('NEW','ACTIVE'));
    } else {
        $members = array();
    }

    // get contact info for selected member
    if (isset($_REQUEST['member_id']) && intval($_REQUEST['member_id'])) {
        $registerPerson = new \Register\Person($_REQUEST['member_id']);        
        $contacts = $registerPerson->contacts();      
        $contactMethods = array('phone' => array(), 'email' => array(), 'sms' => array(), 'facebook' => array(), 'insite' => array());
        foreach ($contacts as $contact) $contactMethods[$contact->type][] = $contact->value;
    } else {
        $contacts = array();
    }
