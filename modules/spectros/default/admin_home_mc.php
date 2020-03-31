<?php	
	if (role('register manager') || role('monitor admin') || role('action user')) {
		# You are amongst friends
	}
	else {
        print "<p><strong>This area is for administrators only!</string></p>";
        print "<p>Return to <a href=\"/\">Spectros Instruments Web Site</a></p>";
        exit;
    }
