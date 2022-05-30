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

			if (! $GLOBALS['_SESSION_']->can('configure site')) $this->deny();
			# Initiate Company List
			$companylist = new \Company\CompanyList();
			
			list($company) = $companylist->find();
	
			# Error Handling
			if ($companylist->error) $this->error($companylist->error);
			else{
				$response = new \HTTP\Response();
				$response->success = 1;
				$response->customer = $company;
			}
	
			# Send Response
			print $this->formatOutput($response);
		}
	
		###################################################
		### Update Company 								###
		###################################################
		public function updateCompany() {
			# Default StyleSheet
			if (! $_REQUEST["stylesheet"]) $_REQUEST["stylesheet"] = 'company.xsl';

			if (! $GLOBALS['_SESSION_']->can('configure site')) $this->deny();
	
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
				$response = new \HTTP\Response();
				$response->company = $company;
				$response->success = 1;
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

		public function _methods() {
			return array(
				'ping'	=> array(),
				'getcompany'	=> array(),
				'updateCompany'	=> array(
					'name'		=> array(),
					'status'	=> array(),
				),
			);
		}
	}
