<?php
	###################################################
	### expire_users.php							###
	### This program identifies users that have not	###
	### logged in recently and marks them expired.	###
	### A. Caravello 3/4/2016						###
	###################################################

	# Security - Only Register Module Operators or Managers can see other customers
	if (role('register manager'))
	{
		$expires = strtotime("-12 month", time());
		$date = date('m/d/Y',$expires);

		# Initialize Customers
		$customers = new RegisterCustomers();

		# Expire Aged Customers
		$count = $customers->expire($date);
		if ($customers->error)
			print "<div class=\"form_error\">Error expiring customers: ".$customers->error."</div>";
		else
			print "<div class=\"form_success\">$count Customers Expired before $date</div>";

		# Initialize Organizations
		$organizations = new RegisterOrganizations();
		
		# Expire Organizations w/o Active Users
		$count = $organizations->expire($date);
		if ($organizations->error)
			print "<div class=\"form_error\">Error expiring organizations: ".$organizations->error."</div>";
		else
			print "<div class=\"form_success\">$count Organizations Expired before $date</div>";
	}
