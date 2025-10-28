<?php
	$page = new \Site\Page();

    // Initiate Image Object
    $user = new \Register\Customer();

	if ($user->validLogin($_REQUEST['login'])) {
		if ($user->get($_REQUEST['login'])) {
			// Add Event
			$site = new \Site();
			if ($user->verify_email($_REQUEST['validation_key'])) {
				$page->success = "Thank you, please go to <a href=\"/_register/login\">".$site->url()."/_register/login</a> to login to the site.\n";
			}
			elseif ($user->error) {
				$page->addError("Sorry, would could not process your request!");
			}
			else {
				$page->addError("Invalid key, please try again");
	    	}
		}
		else {
			$page->addError("Invalid key, please try again");
		}
	}
