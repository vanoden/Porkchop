<?PHP
	###################################################
	### accounts_mc.php								###
	### This program collects registration info		###
	### for the user.								###
	### A. Caravello 11/12/2002						###
	###################################################

	# Customers to display at a time
	if (preg_match('/^\d+$/',$_REQUEST['page_size']))
		$customers_per_page = $_REQUEST['page_size'];
	else
		$customers_per_page = 15;

	# Security - Only Register Module Operators or Managers can see other customers
	if (role('register reporter') or role('register manager'))
	{
		if (! preg_match('/^\d+$/',$_REQUEST['start'])) $_REQUEST['start'] = 0;
		$_customer = new RegisterCustomer();
		$customers = $_customer->find(array("_limit" => $customers_per_page, "_offset" => $_REQUEST['start']));
		$total_customers = $_customer->count();
		if ($_customer->error) $GLOBALS['_page']->error = "Error finding customers: ".$_customer->error;
		
		if ($_REQUEST['start'] < $customers_per_page)
			$prev_offset = 0;
		else
			$prev_offset = $_REQUEST['start'] - $customers_per_page;
		$next_offset = $_REQUEST['start'] + $customers_per_page;
		$last_offset = $total_customers - $customers_per_page;
	}
	else $customers = array();
?>