<?PHP
	###################################################
	### expire_users.php							###
	### This program identifies users that have not	###
	### logged in recently and marks them expired.	###
	### A. Caravello 3/4/2016						###
	###################################################

	# Security - Only Register Module Operators or Managers can see other customers
	if (role('register manager'))
	{
		$customers = new RegisterCustomers();

		$expires = strtotime("-15 month", time());
		$date = date('m/d/Y',$expires);

		$count = $customers->expire($date);
		if ($customers->error)
			print "<div class=\"form_error\">Error expiring customers: ".$customers->error."</div>";
		else
			print "<div class=\"form_success\">$count Customers Expired before $date</div>";
	}
?>
