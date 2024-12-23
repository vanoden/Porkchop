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

			$company = $GLOBALS['_SESSION_']->company;
			if (! $company->exists()) $this->notFound();
	
			# Error Handling
			$response = new \APIResponse();
			$response->addElement('customer',$company);
			$response->print();
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
			$company = $GLOBALS['_SESSION_']->company;
			if (! $company->exists()) $this->notFound();
	
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
			else if ($company->error()) {
				$this->error($company->error());
			}
			else{
				$this->error("Unhandled exception");
			}

			# Send Response
			$response->print();
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

		public function updateLocation() {
			if (! $GLOBALS['_SESSION_']->customer->can('configure site')) $this->deny();

			if (!empty($_REQUEST['code'])) {
				$location = new \Company\Location();
				$location->get($_REQUEST['code']);
				if ($location->error()) $this->error($location->error());
				if (! $location->exists()) $this->notFound();
			}
			elseif (!empty($_REQUEST['id'])) {
				$location = new \Company\Location($_REQUEST['id']);
				if ($location->error()) $this->error($location->error());
				if (! $location->exists()) $this->notFound();
			}
			else $this->invalidRequest("code or id required");

			$parameters = array();
			if (!empty($_REQUEST['name'])) {
				if ($location->validName($_REQUEST['name'])) $parameters['name'] = $_REQUEST['name'];
				else $this->invalidRequest("Invalid name");
			}
			if (!empty($_REQUEST['host'])) {
				if ($location->validHost($_REQUEST['host'])) $parameters['host'] = $_REQUEST['host'];
				else $this->invalidRequest("Invalid host");
			}

			if (!empty($_REQUEST['domain_code'])) {
				$domain = new \Company\Domain();
				$domain->get($_REQUEST['domain_code']);
				if ($domain->error()) $this->error($domain->error());
				if (!$domain->exists()) $this->notFound();
				$parameters['domain_id'] = $domain->id;
			}
			elseif (!empty($_REQUEST['domain_id'])) {
				$domain = new \Company\Domain($_REQUEST['domain_id']);
				if ($domain->error()) $this->error($domain->error());
				if (!$domain->exists()) $this->notFound();
				$parameters['domain_id'] = $domain->id;
			}

			$location->update($parameters);
			if ($location->error()) $this->error($location->error());

			$response = new \APIResponse();
			$response->addElement('location',$location);
			$response->print();
		}

		public function findDomains() {
			if (! $GLOBALS['_SESSION_']->customer->can('configure site')) $this->deny();

			$parameters = [];
			if (!empty($_REQUEST['code'])) $parameters['code'] = $_REQUEST['code'];
			if (!empty($_REQUEST['status'])) $parameters['status'] = $_REQUEST['status'];
			if (!empty($_REQUEST['name'])) $parameters['name'] = $_REQUEST['name'];
			if (!empty($_REQUEST['location_id'])) $parameters['location_id'] = $_REQUEST['location_id'];
			if (!empty($_REQUEST['registrar'])) $parameters['registrar'] = $_REQUEST['registrar'];

			$domainList = new \Company\DomainList();
			$domains = $domainList->find($parameters);

			$response = new \APIResponse();
			$response->addElement('domain',$domains);
			$response->print();
		}

		public function addDomain() {
			if (! $GLOBALS['_SESSION_']->customer->can('configure site')) $this->deny();

			$companyList = new \Company\CompanyList();
			list($company) = $companyList->find();

			if (!empty($_REQUEST['location_code'])) {
				$location = new \Company\Location();
				$location->get($_REQUEST['location_code']);
				if (!$location->error()) $this->error($location->error());
				if (!$location->exists()) $this->notFound();
			}

			$domain = new \Company\Domain();
			$parameters = array(
				'company_id'			=> $company->id,
				'status'				=> $_REQUEST["status"],
				'comments'				=> $_REQUEST["comments"],
				'name'					=> $_REQUEST["name"],
				'date_registered'		=> $_REQUEST["date_registered"],
				'date_created'			=> $_REQUEST["date_created"],
				'date_expires'			=> $_REQUEST["date_expires"],
				'registration_period'	=> $_REQUEST["registration_period"],
				'registrar'				=> $_REQUEST["register"]
			);
			if (!empty($location->id)) {
				$parameters['location_id'] = $location->id;
			}

			$domain->add($parameters);
			if ($domain->error()) $this->error($domain->error());

			$response = new \APIResponse();
			$response->addElement('domain',$domain);
			$response->print();
		}

		public function _methods() {
			return array(
				'ping'	=> array(),
				'getcompany'	=> array(
					'description'	=> 'Get details of company to which the site belongs',
					'privilege_required'	=> 'configure site',
					'parameters'	=> []
				),
				'updateCompany'	=> array(
					'description'	=> 'Update details of company to which the site belongs',
					'authentication_required'	=> true,
					'token_required' 			=> true,
					'privilege_required'		=> 'manage company',
					'return_element'			=> 'company',
					'return_type'				=> 'Company::Company',
					'parameters'				=> array(
						'name'		=> array(
							'required'	=> true
						),
						'status'	=> array(),
					)
				),
				'findLocations'	=> array(
					'description'	=> 'Get locations associated with this company.',
					'privilege_required'	=> 'configure site',
					'parameters'	=> []
				),
				'addLocation'	=> array(
					'description'	=> 'Add a location to the sites company',
					'privilege_required'	=> 'configure site',
					'token_required'	=> true,
					'parameters'		=> array(
						'code'	=> array(
							'description'	=> 'Unique identifier for company location.  Can be auto-generated',
							'validation_method'	=> 'Company::Location::validCode()'
						),
						'name'	=> array(
							'description'	=> 'Name of company location',
							'validation_method'	=> 'Company::Location::validName()',
							'required'		=> true
						),
						'host'	=> array(
							'description'	=> 'Hostname for location - used for website locations',
							'regex'			=> '/^[\w\-\.\_\:]+$/'
						),
						'domain_id'	=> array(
							'description'	=> 'Domain name associated with website',
							'validation_method'	=> 'Company::Domain::validHostname()',
						)
					)
				),
				'updateLocation'	=> array(
					'description'	=> 'Update company location details',
					'privilege_required'	=> 'configure site',
					'token_required'		=> true,
					'parameters'	=> array(
						'code'	=> array(
							'required' => true,
							'validation_method'	=> 'Company::Location::validCode()'
						),
						'name'	=> array(
							'validation_method'	=> 'Company::Location::validName()'
						),
						'host'	=> array(
							'validation_method'	=> 'Company::Location::validHostname()'
						),
						'domain_id'	=> array(
							'content-type'	=> 'int'
						),
						'domain_code'	=> array(
							'validation_method'	=> 'Company::Location::validHostname()'
						)
					)
				),
				'findDomains'	=> array(
					'description'	=> 'List matching domains',
					'privilege_required'	=> 'configure site',
					'parameters'	=> array(
						'code'					=> array(
							'validation_method'	=> 'Company::Domain::validCode()'
						),
						'status'				=> array(
							'validation_method' => 'Company::Domain::validStatus()'
						),
						'name'					=> array(
							'validation_method'	=> 'Company::Domain::validName()'
						),
						'location_id'			=> array(
							'content-type'	=> 'int'
						),
						'registrar'				=> array(
							'validation_method'	=> 'Company::Domain::validRegistrar()'
						)
					)
				),
				'addDomain'	=> array(
					'description'	=> 'Add domain to company',
					'privilege_required'	=> 'configure site',
					'token_required'		=> true,
					'parameters'	=> array(
						'code'					=> array(
							'validation_method'	=> 'Company::Domain::validCode()'
						),
						'status'				=> array(
							'validation_method'	=> 'Company::Domain::validStatus()'
						),
						'comments'				=> array(),
						'name'					=> array(
							'validation_method'	=> 'Company::Domain::validName()'
						),
						'date_registered'		=> array(
							'validation_method'	=> 'Porkchop::validDate()'
						),
						'date_created'			=> array(
							'validation_method'	=> 'Porkchop::validDate()'
						),
						'date_expires'			=> array(
							'validation_method'	=> 'Porkchop::validDate()'
						),
						'registration_period'	=> array(
							'content-type'	=> 'int'
						),
						'location_id'			=> array(
							'content-type'	=> 'int'
						),
						'registrar'				=> array(
							'validation_method'	=> 'Company::Domain::validRegistrar()'
						)
					)
				),
			);
		}
	}
