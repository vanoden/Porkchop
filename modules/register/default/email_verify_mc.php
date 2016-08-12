<?	
	if (preg_match("/^\w{32}$/",$target))
	{
		# Confirm Correct Email Sent
		$confirm_key_query = "
			SELECT	c.customer_id,
					v.target_url
			FROM	registration.email_verify v,
					cart.customers c
			WHERE	c.company_id = '$company_id'
			AND		v.customer_id = c.customer_id
			AND		v.confirm_key = '$target'
			AND		c.active = 0
		";
		list($customer_id,$target_url) = exec_query_row($confirm_key_query);
		if ($customer_id)
		{
			$drop_confirm_query = "
				DELETE
				FROM	registration.email_verify
				WHERE	customer_id = '$customer_id'
				";
			exec_query($drop_confirm_query);

			# Update Customer Status
			$update_customer_query = "
				UPDATE	cart.customers
				SET		active = 1
				WHERE	customer_id = '$customer_id'
				AND		company_id = '$company_id'
			";
			exec_query($update_customer_query);

			# Set Customer ID in Session Table
			update_session($customer_id);

			# Set Customer ID in Order Table
			if ($order_id) update_order($order_id,$customer_id,$shipper_id,$status_id);

			if ($target_url)
			{
				# Send to Requested Page
				header("location: $target_url");
			}
			elseif ($order_id)
			{
				# Send to shipping address page
				header("location: /_cart/address");
			}
			else
			{
				# Send to Welcome Page
				header("location: /_register/welcome");
			}
			debug_log("Customer $customer_id Registered.  Redirecting to $target_url");
			exit;
		}
		else
		{
			$result = "Sorry, the link you entered was invalid!";
		}
	}
	else
	{
		$result = "Sorry, the link you entered was invalid!";
	}
?>