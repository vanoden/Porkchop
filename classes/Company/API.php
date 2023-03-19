<?php
	namespace Company;

	/* Base Class for APIs */
	class API extends \API {

		public function __construct() {
			$this->_admin_role = 'administrator';
			$this->_name = 'company';
			$this->_version = '0.2.1';
			$this->_release = '2020-06-10';
			$this->_schema = new Schema();
			parent::__construct();
		}
	
		###################################################
		### Get Details regarding Specified Company		###
		###################################################
		public function getCompany() {
			# Default StyleSheet
			if (! $_REQUEST["stylesheet"]) $_REQUEST["stylesheet"] = 'company.xsl';

			if (! $GLOBALS['_SESSION_']->customer->can('configure site')) $this->deny();
			# Initiate Company List
			$companylist = new \Company\CompanyList();
			
			list($company) = $companylist->find();
	
			# Error Handling
			if ($companylist->error) $this->error($companylist->error);
			else{
				$response = new \APIResponse();
				$response->addElement('customer',$company);
				$response->print();
			}
		}
	
		###################################################
		### Update Company 								###
		###################################################
		public function updateCompany() {
			# Default StyleSheet
			if (! $_REQUEST["stylesheet"]) $_REQUEST["stylesheet"] = 'company.xsl';

			if (!$this->validCSRFToken()) $this->error("Invalid Request");

			if (! $GLOBALS['_SESSION_']->customer->can('configure site')) $this->deny();
	
			# Initiate Company Object
			$companylist = new \Company\CompanyList();
			list($company) = $companylist->find();
	
			# Update Company
			if ($company->update(
				array(
					"name"			=> $_REQUEST["name"],
					"status"		=> $_REQUEST["category"]
				)
			)) {
				$response = new \APIResponse();
				$response->addElement('company',$company);
			}
			else if ($company->error) {
				$this->error($company->error);
			}
			else{
				$this->error("Unhandled exception");
			}

			# Send Response
			print $this->formatOutput($response);
		}

		public function findLocations() {
			if (! $GLOBALS['_SESSION_']->customer->can('configure site')) $this->deny();

			$companyList = new \Company\CompanyList();
			list($company) = $companyList->find();

			$locations = $company->locations();

			$response = new \APIResponse();
			$response->addElement('location',$locations);
			$response->print();
		}

		public function addLocation() {
			if (! $GLOBALS['_SESSION_']->customer->can('configure site')) $this->deny();

			$companyList = new \Company\CompanyList();
			list($company) = $companyList->find();

			if (!empty($_REQUEST['domain_code'])) {
				$domain = new \Company\Domain();
				$domain->get($_REQUEST['domain_code']);
				if (!$domain->error()) $this->error($domain->error());
				if (!$domain->exists()) $this->notFound();
			}

			$location = new \Company\Location();
			$parameters = array(
				'company_id'	=> $company->id,
				'code'			=> $_REQUEST['code'],
				'name'			=> $_REQUEST['name'],
				'host'			=> $_REQUEST['host']
			);
			if (!empty($domain->id)) {
				$parameters['domain_id'] = $domain->id;
			}

			$location->add($parameters);
			if ($location->error()) $this->error($location->error());

			$response = new \APIResponse();
			$response->addElement('location',$location);
			$response->print();
		}

		public function _methods() {
			return array(
				'ping'	=> array(),
				'getcompany'	=> array(),
				'updateCompany'	=> array(
					'name'		=> array(),
					'status'	=> array(),
				),
				'findLocations'	=> array(
				),
				'addLocation'	=> array(
					'code'	=> array(),
					'name'	=> array(),
					'host'	=> array()
				)
			);
		}
	}
