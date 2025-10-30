<?php
	include("../includes/functions.php");

	$in_array = [false, false, false, false, false, false, false, false];

	enum Visibility: int {
		case Store = 1;
		case Support = 2;
		case Assembly = 3;
		case Internal = 4;
	}

	$byte = matrix2Byte($in_array);

	print "Set Support byte [".Visibility::Support->value."] to true\n";
	$byte = setMatrix($byte, Visibility::Support->value, true);

	print "Set Interal byte [".Visibility::Internal->value."] to true\n";
	$byte = setMatrix($byte, Visibility::Internal->value, true);

	print "Byte ";
	print_r($byte);
	print "\n";

	$out_array = byte2Matrix($byte);

	print_r($out_array);

	print "Store: ";
	if (inMatrix($byte, Visibility::Store->value)) {
		print "True\n";
	}
	else {
		print "False\n";
	}

	print "Support: ";	
	if (inMatrix($byte, Visibility::Support->value)) {
		print "True\n";
	}
	else {
		print "False\n";
	}

	print "Assembly: ";
	if (inMatrix($byte, Visibility::Assembly->value)) {
		print "True\n";
	}
	else {
		print "False\n";
	}

	print "Internal: ";
	if (inMatrix($byte, Visibility::Internal->value)) {
		print "True\n";
	}
	else {
		print "False\n";
	}

	$arr = matrix2Elements($byte);
	print_r($arr);

	print "Visibilities: \n";
	foreach ($arr as $elem) {
		$visibility = Visibility::from($elem);
		print $visibility->name."\n";
	}
