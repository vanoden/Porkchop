<?php
	namespace Network;

	/* Base Class for APIs */
	class API extends \API {

		public function __construct() {
			$this->_name = 'network';
			$this->_version = '0.2.1';
			$this->_release = '2020-06-10';
			$this->_schema = new Schema();
			parent::__construct();
		}

		###################################################
		### Query Domain List							###
		###################################################
		public function findDomains() {
			# Default StyleSheet
			if (! isset($_REQUEST["stylesheet"])) $_REQUEST["stylesheet"] = 'network.domain.xsl';
			$response = new \HTTP\Response();
	
			# Initiate Domain List
			$domainList = new \Network\DomainList();
	
			# Find Matching Threads
			$parameters = array();
			if (isset($_REQUEST['name'])) $parameters['name'] = $_REQUEST['name'];
			$domains = $domainList->find($parameters);
	
			# Error Handling
			if ($domainList->error()) $this->error($domainList->error());
			else{
				$response->domain = $domains;
				$response->success = 1;
			}
	
			api_log('network',$_REQUEST,$response);
	
			# Send Response
			print $this->formatOutput($response);
		}
		###################################################
		### Get Details regarding Specified Domain		###
		###################################################
		public function getDomain() {
			# Default StyleSheet
			if (! $_REQUEST["stylesheet"]) $_REQUEST["stylesheet"] = 'network.domain.xsl';
			$response = new \HTTP\Response();
	
			# Initiate Domain Object
			$domain = new \Network\Domain();
			if (! isset($_REQUEST['name'])) $this->error("name required");
	
			$domain->get($_REQUEST['name']);
	
			# Error Handling
			if ($domain->error()) $this->error($domain->error());
			elseif ($domain->id) {
				$response->domain = $domain;
				$response->success = 1;
			}
			else {
				$response->success = 0;
				$response->error = "Domain not found";
			}
	
			api_log('network',$_REQUEST,$response);
	
			# Send Response
			print $this->formatOutput($response);
		}
		###################################################
		### Create Domain Record						###
		###################################################
		public function addDomain() {
			# Default StyleSheet
			if (! $_REQUEST["stylesheet"]) $_REQUEST["stylesheet"] = 'network.domain.xsl';
			if (! $_REQUEST['name']) $this->error("name required");
	
			$domain = new \Network\Domain();
			$domain->get($_REQUEST['name']);
			if ($domain->id) $this->error("Domain already exists");
			$domain->add(array('name' => $_REQUEST['name']));
			if ($domain->error) $this->error("Error adding domain: ".$domain->error());
	
			$response = new \HTTP\Response();
			$response->success = 1;
			$response->domain = $domain;
			print $this->formatOutput($response);
		}
	
		###################################################
		### Query Host List								###
		###################################################
		public function findHosts() {
			# Default StyleSheet
			if (! isset($_REQUEST["stylesheet"])) $_REQUEST["stylesheet"] = 'network.host.xsl';
			$response = new \HTTP\Response();
	
			# Initiate Host List
			$hostList = new \Network\HostList();
	
			# Find Matching Hosts
			$parameters = array();
			if (isset($_REQUEST['domain_name'])) {
				$domain = new \Network\Domain();
				if ($domain->get($parameters['domain_name'])) {
					$parameters['domain_id'] = $domain->id;
				}
				else {
				 $this->error("domain not found");
				}
			}
			if (isset($_REQUEST['name'])) $parameters['name'] = $_REQUEST['name'];
			if (isset($_REQUEST['os_name'])) $parameters['os_name'] = $_REQUEST['os_name'];
			if (isset($_REQUEST['os_version'])) $parameters['os_version'] = $_REQUEST['os_version'];
			$hosts = $hostList->find($parameters);
	
			# Error Handling
			if ($hostList->error()) $this->error($hostList->error());
			else{
				$response->host = $hosts;
				$response->success = 1;
			}
	
			api_log('network',$_REQUEST,$response);
	
			# Send Response
			print $this->formatOutput($response);
		}
		###################################################
		### Get Details regarding Specified Host		###
		###################################################
		public function getHost() {
			# Default StyleSheet
			if (! $_REQUEST["stylesheet"]) $_REQUEST["stylesheet"] = 'network.host.xsl';
			$response = new \HTTP\Response();
	
			# Initiate Host Object
			$host = new \Network\Host();
	
			if (preg_match('/^([\w\-]+)\.(.*)$/',$_REQUEST['name'],$matches)) {
				$_REQUEST['name'] = $matches[1];
				$_REQUEST['domain_name'] = $matches[2];
			}
			if (! isset($_REQUEST['domain_name'])) $this->error('domain name or fully qualified host name required');
			if (! isset($_REQUEST['name'])) $this->error('name required');
			$domain = new \Network\Domain();
			$domain->get($_REQUEST['domain_name']);
			if (! $domain->id) $this->error('Domain not found');
	
			# Get Host
			$host->get($domain->id,$_REQUEST['name']);
	
			# Error Handling
			if ($host->error()) $this->error($host->error());
			elseif ($host->id) {
				$response->host = $host;
				$response->success = 1;
			}
			else {
				$response->success = 0;
				$response->error = "Host not found";
			}
	
			api_log('network',$_REQUEST,$response);
	
			# Send Response
			print $this->formatOutput($response);
		}
		###################################################
		### Create Host Record							###
		###################################################
		public function addHost() {
			# Default StyleSheet
			if (! $_REQUEST["stylesheet"]) $_REQUEST["stylesheet"] = 'network.host.xsl';
	
			if (preg_match('/^([\w\-]+)\.(.*)$/',$_REQUEST['name'],$matches)) {
				$_REQUEST['name'] = $matches[1];
				$_REQUEST['domain_name'] = $matches[2];
			}
			if (! $_REQUEST['name']) $this->error("name required");
			if (! $_REQUEST['domain_name']) $this->error("domain name required");
	
			$domain = new \Network\Domain();
			$domain->get($_REQUEST['domain_name']);
			if (! $domain->id) $this->error("Domain not found");
			
			$host = new \Network\Host();
			$host->add(
				array(
					'domain_id' 	=> $domain->id,
					'name' 			=> $_REQUEST['name'],
					'os_name'		=> $_REQUEST['os_name'],
					'os_version'	=> $_REQUEST['os_version']
				)
			);
			if ($host->error()) $this->error("Error adding host: ".$host->error());
	
			$response = new \HTTP\Response();
			$response->success = 1;
			$response->host = $host;
			print $this->formatOutput($response);
		}
	
		###################################################
		### Query Adapter List							###
		###################################################
		public function findAdapters() {
			# Default StyleSheet
			if (! isset($_REQUEST["stylesheet"])) $_REQUEST["stylesheet"] = 'network.adapter.xsl';
			$response = new \HTTP\Response();
	
			# Initiate Adapter List
			$adapterList = new \Network\AdapterList();
	
			# Find Matching Hosts
			$parameters = array();
			if (preg_match('/^([\w\-]+)\.(.*)$/',$_REQUEST['host_name'],$matches)) {
				$_REQUEST['host_name'] = $matches[1];
				$_REQUEST['domain_name'] = $matches[2];
			}
			if (isset($_REQUEST['domain_name']) && strlen($_REQUEST['domain_name']) > 0) {
				$domain = new \Network\Domain();
				$domain->get($_REQUEST['domain_name']);
				if ($domain->id) {
					$parameters['domain_id'] = $domain->id;
				}
				else {
				 $this->error("domain '".$_REQUEST['domain_name']."' not found");
				}
			}
			if (isset($_REQUEST['host_name']) && strlen($_REQUEST['host_name']) > 0) {
				$host = new \Network\Host();
				$host->get($domain->id,$_REQUEST['host_name']);
				if ($host->id) {
					$parameters['host_id'] = $host->id;
				}
				else {
				 $this->error("host '".$_REQUEST['host_name']."' for domain '".$domain->name."' not found");
				}
			}
			if (isset($_REQUEST['name'])) $parameters['name'] = $_REQUEST['name'];
			if (isset($_REQUEST['type'])) $parameters['type'] = $_REQUEST['type'];
			$adapters = $adapterList->find($parameters);
	
			# Error Handling
			if ($adapterList->error()) $this->error($adapterList->error());
			else{
				$response->adapter = $adapters;
				$response->success = 1;
			}
	
			api_log('network',$_REQUEST,$response);
	
			# Send Response
			print $this->formatOutput($response);
		}
		###################################################
		### Get Details regarding Specified Adapter	###
		###################################################
		public function getAdapter() {
			# Default StyleSheet
			if (! $_REQUEST["stylesheet"]) $_REQUEST["stylesheet"] = 'network.adapter.xsl';
			$response = new \HTTP\Response();
	
			# Initiate Adapter Object
			$adapter = new \Network\Adapter();
	
			if (isset($_REQUEST['mac_address']) && strlen($_REQUEST['mac_address']) > 0) {
				$adapter->get($_REQUEST['mac_address']);
			}
			else {
				if (preg_match('/^([\w\-]+)\.(.*)$/',$_REQUEST['host_name'],$matches)) {
					$_REQUEST['host_name'] = $matches[1];
					$_REQUEST['domain_name'] = $matches[2];
				}
				if (isset($_REQUEST['domain_name'])) {
					$domain = new \Network\Domain();
					if ($domain->get($parameters['domain_name'])) {
						$parameters['domain_id'] = $domain->id;
					}
					else {
					 $this->error("domain not found");
					}
				}
				if (isset($_REQUEST['host_name'])) {
					$host = new \Network\Host();
					$host->get($parameters['host_name']);
					if ($host->id) {
						$parameters['host_id'] = $host->id;
					}
					else {
					 $this->error("host not found");
					}
				}
				if (! isset($_REQUEST['name'])) $this->error("name required");
			
				$adapter->get($host->id,$_REQUEST['name']);
			}
	
			# Error Handling
			if ($adapter->error()) $this->error($adapter->error());
			elseif ($adapter->id) {
				$response->adapter = $adapter;
				$response->success = 1;
			}
			else {
				$response->success = 0;
				$response->error = "Adapter not found";
			}
	
			api_log('network',$_REQUEST,$response);
	
			# Send Response
			print $this->formatOutput($response);
		}
		###################################################
		### Create Adapter Record						###
		###################################################
		public function addAdapter() {
			if (! $_REQUEST["stylesheet"]) $_REQUEST["stylesheet"] = 'network.adapter.xsl';
	
			if (preg_match('/^([\w\-]+)\.(.*)$/',$_REQUEST['host_name'],$matches)) {
				$_REQUEST['host_name'] = $matches[1];
				$_REQUEST['domain_name'] = $matches[2];
			}
			if (isset($_REQUEST['domain_name'])) {
				$domain = new \Network\Domain();
				$domain->get($_REQUEST['domain_name']);
				if ($domain->id > 0) {
					$parameters['domain_id'] = $domain->id;
				}
				else {
				 $this->error("domain not found");
				}
			}
			if (isset($_REQUEST['host_name'])) {
				$host = new \Network\Host();
				$host->get($domain->id,$_REQUEST['host_name']);
				if ($host->id) {
					$parameters['host_id'] = $host->id;
				}
				else {
				 $this->error("host not found");
				}
			}
			if (! isset($_REQUEST['name'])) $this->error("name required");
			if (! isset($_REQUEST['mac_address'])) $this->error("mac_address required");
	
			$adapter = new \Network\Adapter();
			$adapter->add(
				array(
					'host_id' 		=> $host->id,
					'name' 			=> $_REQUEST['name'],
					'mac_address'	=> $_REQUEST['mac_address'],
					'type'			=> $_REQUEST['type']
				)
			);
			if ($adapter->error()) $this->error("Error adding adapter: ".$adapter->error());
	
			$response = new \HTTP\Response();
			$response->success = 1;
			$response->adapter = $adapter;
			print $this->formatOutput($response);
		}
	
		###################################################
		### Query IP Address List						###
		###################################################
		public function findAddresses() {
			# Default StyleSheet
			if (! isset($_REQUEST["stylesheet"])) $_REQUEST["stylesheet"] = 'network.address.xsl';
			$response = new \HTTP\Response();
	
			# Initiate Address List
			$addressList = new \Network\AddressList();
	
			# Find Matching Addresses
			$parameters = array();
			if (preg_match('/^([\w\-]+)\.(.*)$/',$_REQUEST['host_name'],$matches)) {
				$_REQUEST['host_name'] = $matches[1];
				$_REQUEST['domain_name'] = $matches[2];
			}
			if (isset($_REQUEST['domain_name']) && strlen($_REQUEST['domain_name']) > 0) {
				$domain = new \Network\Domain();
				$domain->get($_REQUEST['domain_name']);
				if ($domain->id) {
					$parameters['domain_id'] = $domain->id;
				}
				else {
				 $this->error("domain not found");
				}
			}
			if (isset($_REQUEST['host_name']) && strlen($_REQUEST['host_name']) > 0) {
				$host = new \Network\Host();
				$host->get($domain->id,$_REQUEST['host_name']);
				if ($host->id) {
					$parameters['host_id'] = $host->id;
				}
				else {
				 $this->error("host not found");
				}
			}
			if (isset($_REQUEST['adapter_name']) && strlen($_REQUEST['adapter_name']) > 0) {
				$adapter = new \Network\Adapter();
				$adapter->get($host->id,$_REQUEST['adapter_name']);
				if ($adapter->id) {
					$parameters['adapter_id'] = $adapter->id;
				}
				else {
				 $this->error("adapter '".$_REQUEST['adapter_name']."' for host '".$host->name."' not found");
				}
			}
			if (isset($_REQUEST['type'])) $parameters['type'] = $_REQUEST['type'];
			$addresses = $addressList->find($parameters);
	
			# Error Handling
			if ($addressList->error()) $this->error($addressList->error());
			else{
				$response->address = $addresses;
				$response->success = 1;
			}
	
			api_log('network',$_REQUEST,$response);
	
			# Send Response
			print $this->formatOutput($response);
		}
		###################################################
		### Get Details regarding Specified IP Address	###
		###################################################
		public function getAddress() {
			# Default StyleSheet
			if (! $_REQUEST["stylesheet"]) $_REQUEST["stylesheet"] = 'network.address.xsl';
			$response = new \HTTP\Response();
	
			# Initiate Domain Object
			$address = new \Network\IPAddress();
			if (! isset($_REQUEST['address'])) $this->error("address required");
	
			$address->get($_REQUEST['address']);
	
			# Error Handling
			if ($address->error()) $this->error($address->error());
			elseif ($address->id) {
				$response->address = $address;
				$response->success = 1;
			}
			else {
				$response->success = 0;
				$response->error = "Address not found";
			}
	
			api_log('network',$_REQUEST,$response);
	
			# Send Response
			print $this->formatOutput($response);
		}
		###################################################
		### Create Address Record						###
		###################################################
		public function addAddress() {
			if (! $_REQUEST["stylesheet"]) $_REQUEST["stylesheet"] = 'network.address.xsl';
	
			if (isset($_REQUEST['mac_address']) && strlen($_REQUEST['mac_address']) > 0) {
				$adapter = new \Network\Adapter();
				$adapter->get($_REQUEST['mac_address']);
				if (! $adapter->id) $this->error("Adapter not found");
			}
			else {
				if (preg_match('/^([\w\-]+)\.(.*)$/',$_REQUEST['host_name'],$matches)) {
					$_REQUEST['host_name'] = $matches[1];
					$_REQUEST['domain_name'] = $matches[2];
				}
				if (isset($_REQUEST['domain_name'])) {
					$domain = new \Network\Domain();
					if ($domain->get($_REQUEST['domain_name'])) {
						$parameters['domain_id'] = $domain->id;
					}
					else {
					 $this->error("domain not found");
					}
				}
				if (isset($_REQUEST['host_name']) && strlen($_REQUEST['host_name']) > 0) {
					$host = new \Network\Host();
					$host->get($domain->id,$_REQUEST['host_name']);
					if ($host->id) {
						$parameters['host_id'] = $host->id;
					}
					else {
					 $this->error("host '".$_REQUEST['host_name']."' for domain '".$domain->name."' not found");
					}
				}
				if (isset($_REQUEST['adapter_name'])) {
					$adapter = new \Network\Adapter();
					$adapter->get($host->id,$_REQUEST['adapter_name']);
					if ($adapter->id) {
						$parameters['adapter_id'] = $adapter->id;
					}
					else {
					 $this->error("adapter not found");
					}
				}
			}
			if (! isset($_REQUEST['address'])) $this->error("address required");
			if (preg_match('/^(\d+\.\d+\.\d+\.\d+)\/(\d+)$/',$_REQUEST['address'],$matches)) {
				$_REQUEST['address'] = $matches[1];
				$_REQUEST['prefix'] = $matches[2];
				$_REQUEST['type'] = 'ipv4';
			}
			elseif(preg_match('/^([a-f0-9\:]+)\/(\d+)$/',$_REQUEST['address'],$matches)) {
				$_REQUEST['address'] = $matches[1];
				$_REQUEST['prefix'] = $matches[2];
				$_REQUEST['type'] = 'ipv6';
			}
			elseif(preg_match('/^[a-f0-9\:]+$/',$_REQUEST['address'])) {
				$_REQUEST['type'] = 'ipv6';
			}
			elseif(preg_match('/^(\d+\.\d+\.\d+\.\d+)$/',$_REQUEST['address'])) {
				$_REQUEST['type'] = 'ipv4';
			}
			else $this->error('Invalid ip address');
			if (! isset($_REQUEST['prefix'])) $this->error("prefix required");
	
			$address = new \Network\IPAddress();
			app_log("Adding Address",'info');
			$address->add(
				array(
					'adapter_id' => $adapter->id,
					'address' => $_REQUEST['address'],
					'prefix' => $_REQUEST['prefix'],
					'type' => $_REQUEST['type']
				)
			);
			if ($address->error()) {
				print $address->error()."<br>\n";
				$this->error("Error adding adapter: ".$address->error());
			}
	
			$response = new \HTTP\Response();
			$response->success = 1;
			$response->address = $address;
			print $this->formatOutput($response);
		}

		public function addSubnet() {
			$subnet = new \Network\Subnet();

			$params = array();
			if (!empty($_REQUEST['address'])) $params['address'] = $_REQUEST['address'];
			if (!empty($_REQUEST['mask'])) $params['mask'] = $_REQUEST['mask'];
			if (!empty($_REQUEST['prefix'])) $params['prefix'] = $_REQUEST['prefix'];
			if (!empty($_REQUEST['type'])) $params['type'] = $_REQUEST['type'];

			if (! $subnet->add($params)) {
				$this->error($subnet->error());
			}

			$response = new \HTTP\Response();
			$response->success = 1;
			$response->subnet = $subnet;
			print $this->formatOutput($response);
		}

		public function _methods() {
			return array(
				'ping'	=> array(),
				'findDomains'	=> array(
					'name'		=> array()
				),
				'getDomain'	=> array(
					'name'		=> array('required' => true)
				),
				'addDomain'	=> array(
					'name'		=> array('required' => true)
				),
				'findHosts'		=> array(
					'domain_id'		=> array(),
					'name'			=> array(),
					'os_name'		=> array(),
					'os_version'	=>array(),
				),
				'getHost'	=> array(
					'domain_name'	=> array(),
					'name'			=> array(),
				),
				'addHost'	=> array(
					'domain_name'	=> array('required' => true),
					'name'			=> array('required'	=> true),
					'os_name'		=> array(),
					'os_version'	=>array(),
				),
				'findAdapters'	=> array(
					'domain_name'	=> array(),
					'host_name'		=> array(),
					'name'			=> array(),
					'type'			=>array(),
				),
				'getAdapter'	=> array(
					'domain_name'	=> array(),
					'host_name'		=> array(),
					'name'			=> array(),
					'mac_address'	=> array(),
				),
				'addAdapter'	=> array(
					'domain_name'	=> array('required' => true),
					'host_name'		=> array('required'	=> true),
					'name'			=> array(),
					'mac_address'	=> array(),
					'type'			=> array(),
				),
				'findAddresses'	=> array(
					'domain_name'	=> array(),
					'host_name'		=> array(),
					'adapter_name'	=> array(),
					'type'			=>array(),
				),
				'getAddress'	=> array(
					'address'	=> array('required' => true),
				),
				'addAddress'	=> array(
					'domain_name'	=> array('required' => true),
					'host_name'		=> array('required'	=> true),
					'adapter_name'	=> array(),
					'address'		=> array(),
					'subnet_id'		=> array(),
					'type'			=> array(),
				),
				'addSubnet'	=> array(
					'address'		=> array(),
					'subnet_id'		=> array(),
					'type'			=> array(),
				),
			);
		}
	}
