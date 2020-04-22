<?php
	$page = new \Site\Page();
	$page->requireAuth();

	# No Spoofing!
	$unsub_customer_id = '';

	# Find Matching Record for Key
	if ($r7_session["email_id"]) {
		# Get Customer Record
		$get_customer_query = "
			SELECT	h.contact_id,
					c.first_name
			FROM	mail_dist.requests r,
					mail_dist.events e,
					mail_dist.history h,
					cart.customers c
			WHERE	r.company_id = '$company_id'
			AND		r.request_id = e.request_id
			AND		e.event_id = h.event_id
			AND		h.eeid = '".$r7_session["email_id"]."'
			AND		c.customer_id = h.contact_id
			";
		# Execute Query
		list($unsub_customer_id,$unsubscribe_name) = exec_query_row($get_customer_query);
	}
	else {
		$page->addError("Invalid key Returned!  We apologize for the problem. Please login to the web site to continue.");
		mail("tony@rootseven.com","Invalid Key sent to UnSubscribe!","Invalid key sent to unsubscribe form, key='".$r7_session["email_id"]."', company $company_id, session $session_id. Invalid Key Format");
	}

	if (! $unsub_customer_id) {
		$page->addError("Invalid key Returned!  We apologize for the problem. Please login to the web site to continue.");
		mail("tony@rootseven.com","Invalid Key sent to UnSubscribe!","Invalid key sent to unsubscribe form, company $company_id, session $session_id.  No Key Match");
	}
	# Handle Unsubscribe Request
	elseif ($todo) {
		# Get Email Address Being Refused
		$get_email_query = "
			SELECT	email_address
			FROM	contact.contacts
			WHERE	company_id = '$company_id'
			AND		customer_id = '$unsub_customer_id'
		";
		list($unsub_email) = exec_query_row($get_email_query);

		# Get Customers with Associated Email Address
		$get_contacts_query = "
			SELECT	customer_id
			FROM	contact.contacts
			WHERE	company_id = '$company_id'
			AND		email_address = '$unsub_email'
		";
		$contacts = exec_query_handle($get_contacts_query);	
		while (list($unsub_customer_id) = mysql_fetch_row($contacts))
		{
			# Update the record in the database
			$update_customer_query = "
				UPDATE	cart.customers
				SET		opt_in = '$opt_in'
				WHERE	customer_id = '$unsub_customer_id'
			";

			# Execute Query
			exec_query($update_customer_query);
		}
		
		# Send Customer To UnSub Complete Page
		if ($opt_in) header("Location: /");
		else header("Location: /_register/unsubscribed");
		exit;
	}
