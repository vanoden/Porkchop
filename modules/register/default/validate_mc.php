<?php
		$page = new \Site\Page();

        // Initiate Image Object
        $user = new Customer();

		if ($user->get($_REQUEST['login'])) {
		
			// Add Event
			if ($user->verify_email($_REQUEST['validation_key'])) {
				print "Thank you, please go to <a href=\"/_register/login\">http://<?=$GLOBALS['_location']->hostname?>/_register/login</a> to login to the site.\n";
			} elseif ($user->error) {
				$page->addError("Sorry, would could not process your request!");
			} else {
				$page->addError("Invalid key, please try again");
        	}
        	
		} else {
			$page->addError("Invalid key, please try again");
		}
