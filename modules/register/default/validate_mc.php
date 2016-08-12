<?
        # Initiate Image Object
        $_user = new Customer();

        # Add Event
        $_user->verify_email($_REQUEST['login'],$_REQUEST['validation_key']);

        # Error Handling
        if ($_user->error)
		{
			print "Sorry, would could not process your request!";
		}
        else
		{
			print "Thank you, please go to <a href=\"/_register/login\">http://<?=$GLOBALS['_location']->hostname?>/_register/login</a> to login to the site.\n";
        }
?>
