<?php
	$page = new \Site\Page();

	$code = "test";
	$counter = new \Site\Counter($code);
	print "Code: ".$counter->code()."<br>\n";
	if ($counter->error()) print "Error: ".$counter->error()."<br>\n";
	else print "Value: ".$counter->increment();

	$counterList = new \Site\CounterList();
	print_r($counterList->find(),false);
