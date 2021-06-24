<?php
	$page = new \Site\Page();
	if (role('administrator')) {
		# You are amongst friends
	} else {
        print "<p><strong>This area is for administrators only!</string></p>";
        print "<p>Return to <a href=\"/\">".$GLOBALS['_SESSION_']->company->name." Web Site</a></p>";
        exit;
    }
