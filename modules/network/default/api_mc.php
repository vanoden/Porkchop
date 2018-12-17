<?php
    ###############################################
    ### Handle API Request for Network Info and	###
    ### Management								###
    ### A. Caravello 12/15/2018               	###
    ###############################################

	# Call Requested Event
	#error_log($_REQUEST['method']." Request received");
	#error_log(print_r($_REQUEST,true));
	if ($_REQUEST["method"]) {
		app_log("Method ".$_REQUEST["method"]." called with ".$GLOBALS['_REQUEST_']->query_vars);
		# Call the Specified Method
		$function_name = $_REQUEST["method"];
		$function_name();
		exit;
	}
	# Only Developers Can See The API
	elseif (! $GLOBALS['_SESSION_']->customer->has_role('network editor') && ! $GLOBALS['_SESSION_']->customer->has_role('network viewer')) {
		header("location: /_network/home");
		exit;
	}

	###################################################
	### Just See if Server Is Communicating			###
	###################################################
	function ping() {
		$response = new \HTTP\Response();
		$response->header->session = $GLOBALS['_SESSION_']->code;
		$response->header->method = $_REQUEST["method"];
		$response->message = "PING RESPONSE";
		$response->success = 1;
		api_log('content',$_REQUEST,$response);
		print formatOutput($response);
	}
	###################################################
	### Query Domain List							###
	###################################################
	function findDomains() {
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
		if ($domainList->error()) error($domainList->error());
		else{
			$response->domain = $domains;
			$response->success = 1;
		}

		api_log('network',$_REQUEST,$response);

		# Send Response
		print formatOutput($response);
	}
	###################################################
	### Get Details regarding Specified Domain		###
	###################################################
	function getDomain() {
		# Default StyleSheet
		if (! $_REQUEST["stylesheet"]) $_REQUEST["stylesheet"] = 'network.domain.xsl';
		$response = new \HTTP\Response();

		# Initiate Domain Object
		$domain = new \Network\Domain();
		if (! isset($_REQUEST['name'])) error("name required");

		$domain->get($_REQUEST['name']);

		# Error Handling
		if ($domain->error()) error($domain->error());
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
		print formatOutput($response);
	}
	###################################################
	### Create Domain Record						###
	###################################################
	function addDomain() {
		# Default StyleSheet
		if (! $_REQUEST["stylesheet"]) $_REQUEST["stylesheet"] = 'network.domain.xsl';
		if (! $_REQUEST['name']) error("name required");

		$domain = new \Network\Domain();
		$domain->get($_REQUEST['name']);
		if ($domain->id) error("Domain already exists");
		$domain->add(array('name' => $_REQUEST['name']));
		if ($domain->error) error("Error adding domain: ".$domain->error());

		$response = new \HTTP\Response();
		$response->success = 1;
		$response->domain = $domain;
		print formatOutput($response);
	}

	###################################################
	### Query Host List								###
	###################################################
	function findHosts() {
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
				error("domain not found");
			}
		}
		if (isset($_REQUEST['name'])) $parameters['name'] = $_REQUEST['name'];
		if (isset($_REQUEST['os_name'])) $parameters['os_name'] = $_REQUEST['os_name'];
		if (isset($_REQUEST['os_version'])) $parameters['os_version'] = $_REQUEST['os_version'];
		$hosts = $hostList->find($parameters);

		# Error Handling
		if ($hostList->error()) error($hostList->error());
		else{
			$response->host = $hosts;
			$response->success = 1;
		}

		api_log('network',$_REQUEST,$response);

		# Send Response
		print formatOutput($response);
	}
	###################################################
	### Get Details regarding Specified Host		###
	###################################################
	function getHost() {
		# Default StyleSheet
		if (! $_REQUEST["stylesheet"]) $_REQUEST["stylesheet"] = 'network.host.xsl';
		$response = new \HTTP\Response();

		# Initiate Host Object
		$host = new \Network\Host();

		if (preg_match('/^([\w\-]+)\.(.*)$/',$_REQUEST['name'],$matches)) {
			$_REQUEST['name'] = $matches[1];
			$_REQUEST['domain_name'] = $matches[2];
		}
		if (! isset($_REQUEST['domain_name'])) error('domain name or fully qualified host name required');
		if (! isset($_REQUEST['name'])) error('name required');
		$domain = new \Network\Domain();
		$domain->get($_REQUEST['domain_name']);
		if (! $domain->id) error('Domain not found');

		# Get Host
		$host->get($domain->id,$_REQUEST['name']);

		# Error Handling
		if ($host->error()) error($host->error());
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
		print formatOutput($response);
	}
	###################################################
	### Create Host Record							###
	###################################################
	function addHost() {
		# Default StyleSheet
		if (! $_REQUEST["stylesheet"]) $_REQUEST["stylesheet"] = 'network.host.xsl';

		if (preg_match('/^([\w\-]+)\.(.*)$/',$_REQUEST['name'],$matches)) {
			$_REQUEST['name'] = $matches[1];
			$_REQUEST['domain_name'] = $matches[2];
		}
		if (! $_REQUEST['name']) error("name required");
		if (! $_REQUEST['domain_name']) error("domain name required");

		$domain = new \Network\Domain();
		$domain->get($_REQUEST['domain_name']);
		if (! $domain->id) error("Domain not found");
		
		$host = new \Network\Host();
		$host->add(
			array(
				'domain_id' 	=> $domain->id,
				'name' 			=> $_REQUEST['name'],
				'os_name'		=> $_REQUEST['os_name'],
				'os_version'	=> $_REQUEST['os_version']
			)
		);
		if ($host->error()) error("Error adding host: ".$host->error());

		$response = new \HTTP\Response();
		$response->success = 1;
		$response->host = $host;
		print formatOutput($response);
	}

	###################################################
	### Query Adapter List							###
	###################################################
	function findAdapters() {
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
				error("domain '".$_REQUEST['domain_name']."' not found");
			}
		}
		if (isset($_REQUEST['host_name']) && strlen($_REQUEST['host_name']) > 0) {
			$host = new \Network\Host();
			$host->get($domain->id,$_REQUEST['host_name']);
			if ($host->id) {
				$parameters['host_id'] = $host->id;
			}
			else {
				error("host '".$_REQUEST['host_name']."' for domain '".$domain->name."' not found");
			}
		}
		if (isset($_REQUEST['name'])) $parameters['name'] = $_REQUEST['name'];
		if (isset($_REQUEST['type'])) $parameters['type'] = $_REQUEST['type'];
		$adapters = $adapterList->find($parameters);

		# Error Handling
		if ($adapterList->error()) error($adapterList->error());
		else{
			$response->adapter = $adapters;
			$response->success = 1;
		}

		api_log('network',$_REQUEST,$response);

		# Send Response
		print formatOutput($response);
	}
	###################################################
	### Get Details regarding Specified Adapter	###
	###################################################
	function getAdapter() {
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
					error("domain not found");
				}
			}
			if (isset($_REQUEST['host_name'])) {
				$host = new \Network\Host();
				$host->get($parameters['host_name']);
				if ($host->id) {
					$parameters['host_id'] = $host->id;
				}
				else {
					error("host not found");
				}
			}
			if (! isset($_REQUEST['name'])) error("name required");
		
			$adapter->get($host->id,$_REQUEST['name']);
		}

		# Error Handling
		if ($adapter->error()) error($adapter->error());
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
		print formatOutput($response);
	}
	###################################################
	### Create Adapter Record						###
	###################################################
	function addAdapter() {
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
				error("domain not found");
			}
		}
		if (isset($_REQUEST['host_name'])) {
			$host = new \Network\Host();
			$host->get($domain->id,$_REQUEST['host_name']);
			if ($host->id) {
				$parameters['host_id'] = $host->id;
			}
			else {
				error("host not found");
			}
		}
		if (! isset($_REQUEST['name'])) error("name required");
		if (! isset($_REQUEST['mac_address'])) error("mac_address required");

		$adapter = new \Network\Adapter();
		$adapter->add(
			array(
				'host_id' 		=> $host->id,
				'name' 			=> $_REQUEST['name'],
				'mac_address'	=> $_REQUEST['mac_address'],
				'type'			=> $_REQUEST['type']
			)
		);
		if ($adapter->error()) error("Error adding adapter: ".$adapter->error());

		$response = new \HTTP\Response();
		$response->success = 1;
		$response->adapter = $adapter;
		print formatOutput($response);
	}

	###################################################
	### Query IP Address List						###
	###################################################
	function findAddresses() {
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
				error("domain not found");
			}
		}
		if (isset($_REQUEST['host_name']) && strlen($_REQUEST['host_name']) > 0) {
			$host = new \Network\Host();
			$host->get($domain->id,$_REQUEST['host_name']);
			if ($host->id) {
				$parameters['host_id'] = $host->id;
			}
			else {
				error("host not found");
			}
		}
		if (isset($_REQUEST['adapter_name']) && strlen($_REQUEST['adapter_name']) > 0) {
			$adapter = new \Network\Adapter();
			$adapter->get($host->id,$_REQUEST['adapter_name']);
			if ($adapter->id) {
				$parameters['adapter_id'] = $adapter->id;
			}
			else {
				error("adapter '".$_REQUEST['adapter_name']."' for host '".$host->name."' not found");
			}
		}
		if (isset($_REQUEST['type'])) $parameters['type'] = $_REQUEST['type'];
		$addresses = $addressList->find($parameters);

		# Error Handling
		if ($addressList->error()) error($addressList->error());
		else{
			$response->address = $addresses;
			$response->success = 1;
		}

		api_log('network',$_REQUEST,$response);

		# Send Response
		print formatOutput($response);
	}
	###################################################
	### Get Details regarding Specified IP Address	###
	###################################################
	function getAddress() {
		# Default StyleSheet
		if (! $_REQUEST["stylesheet"]) $_REQUEST["stylesheet"] = 'network.address.xsl';
		$response = new \HTTP\Response();

		# Initiate Domain Object
		$address = new \Network\IPAddress();
		if (! isset($_REQUEST['address'])) error("address required");

		$address->get($_REQUEST['address']);

		# Error Handling
		if ($address->error()) error($address->error());
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
		print formatOutput($response);
	}
	###################################################
	### Create Address Record						###
	###################################################
	function addAddress() {
		if (! $_REQUEST["stylesheet"]) $_REQUEST["stylesheet"] = 'network.address.xsl';

		if (isset($_REQUEST['mac_address']) && strlen($_REQUEST['mac_address']) > 0) {
			$adapter = new \Network\Adapter();
			$adapter->get($_REQUEST['mac_address']);
			if (! $adapter->id) error("Adapter not found");
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
					error("domain not found");
				}
			}
			if (isset($_REQUEST['host_name']) && strlen($_REQUEST['host_name']) > 0) {
				$host = new \Network\Host();
				$host->get($domain->id,$_REQUEST['host_name']);
				if ($host->id) {
					$parameters['host_id'] = $host->id;
				}
				else {
					error("host '".$_REQUEST['host_name']."' for domain '".$domain->name."' not found");
				}
			}
			if (isset($_REQUEST['adapter_name'])) {
				$adapter = new \Network\Adapter();
				$adapter->get($host->id,$_REQUEST['adapter_name']);
				if ($adapter->id) {
					$parameters['adapter_id'] = $adapter->id;
				}
				else {
					error("adapter not found");
				}
			}
		}
		if (! isset($_REQUEST['address'])) error("address required");
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
		elseif(preg_match('/^[a-f0-9\:]+/$',$_REQUEST['address'])) {
			$_REQUEST['type'] = 'ipv6';
		}
		elseif(preg_match('/^(\d+\.\d+\.\d+\.\d+)$/',$_REQUEST['address'])) {
			$_REQUEST['type'] = 'ipv4';
		}
		else error('Invalid ip address');
		if (! isset($_REQUEST['prefix'])) error("prefix required");

		$address = new \Network\IPAddress();
		$address->add(
			array(
				'adapter_id' => $adapter->id,
				'address' => $_REQUEST['address'],
				'prefix' => $_REQUEST['prefix'],
				'type' => $_REQUEST['type']
			)
		);
		if ($address->error) error("Error adding adapter: ".$address->error());

		$response = new \HTTP\Response();
		$response->success = 1;
		$response->address = $address;
		print formatOutput($response);
	}

	###################################################
	### Manage Page Schema							###
	###################################################
	function schemaVersion() {
		$schema = new \Network\Schema();
		if ($schema->error) {
			app_error("Error getting version: ".$schema->error,__FILE__,__LINE__);
		}
		$version = $schema->version();
		$response = new \HTTP\Response();
		$response->success = 1;
		$response->version = $version;
		print formatOutput($response);
	}
	function schemaUpgrade() {
		$schema = new \Page\Schema();
		if ($schema->error) {
			app_error("Error getting version: ".$schema->error,__FILE__,__LINE__);
		}
		$version = $schema->upgrade();
		$response = new \HTTP\Response();
		$response->success = 1;
		$response->version = $version;
		print formatOutput($response);
	}

	###################################################
	### Return Properly Formatted Error Message		###
	###################################################
	function error($message) {
		$_REQUEST["stylesheet"] = '';
		error_log($message);
		$response = new \HTTP\Response();
		$response->error = $message;
		$response->success = 0;
		api_log('content',$_REQUEST,$response);
		print formatOutput($response);
		exit;
	}
	###################################################
	### Application Error							###
	###################################################
	function app_error($message,$file = __FILE__,$line = __LINE__) {
		app_log($message,'error',$file,$line);
		error('Application Error');
	}
	###################################################
	### Convert Object to XML						###
	###################################################
	function formatOutput($object) {
		if (isset($_REQUEST['_format']) && $_REQUEST['_format'] == 'json') {
			$format = 'json';
			header('Content-Type: application/json');
		}
		else {
			$format = 'xml';
			header('Content-Type: application/xml');
		}
		$document = new \Document($format);
		$document->prepare($object);
		return $document->content();
	}
	
	function confirm_customer() {
		if (! in_array('content reporter',$GLOBALS['_SESSION_']->customer->roles)) {
			$this->error = "You do not have permissions for this task.";
			return 0;
		}
	}

?>
