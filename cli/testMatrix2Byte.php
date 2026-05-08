<?php
	include("../includes/functions.php");

	// Initialize Array with All No's
	$in_array = [false, false, false, false, false, false, false, false];

	// Create Enums tieing Levels to array positions
	enum Visibility: int {
		case Store = 1;
		case Support = 2;
		case Assembly = 3;
		case Internal = 4;
		case High = 7;
	}

	// Conver Starting Array to Byte
	$byte = matrix2Byte($in_array);

	// Enable High Level
	print "Set High byte [".Visibility::High->value."] to true\n";
	$byte = setMatrix($byte, Visibility::High->value, true);

	// Enable Support Level
	#print "Set Support byte [".Visibility::Support->value."] to true\n";
	#$byte = setMatrix($byte, Visibility::Support->value, true);

	// Enable Internal Level
	#print "Set Internal byte [".Visibility::Internal->value."] to true\n";
	#$byte = setMatrix($byte, Visibility::Internal->value, true);

	// Print the Current Numeric Value of Byte (Not very useful)
	print "Byte ";
	print_r($byte);
	print "\n";

	// Convert Byte back to Array
	$out_array = byte2Matrix($byte);

	// Show Current Values (Support and Internal should now be true)
	print_r($out_array);

	// Show Store Level Value
	print "Store: ";
	if (inMatrix($byte, Visibility::Store->value)) {
		print "True\n";
	}
	else {
		print "False\n";
	}

	// Show Support Level Value
	print "Support: ";	
	if (inMatrix($byte, Visibility::Support->value)) {
		print "True\n";
	}
	else {
		print "False\n";
	}

	// Show Assembly Level Value
	print "Assembly: ";
	if (inMatrix($byte, Visibility::Assembly->value)) {
		print "True\n";
	}
	else {
		print "False\n";
	}

	// Show Internal Level Value
	print "Internal: ";
	if (inMatrix($byte, Visibility::Internal->value)) {
		print "True\n";
	}
	else {
		print "False\n";
	}

	// Show High Level Value
	print "High: ";
	if (inMatrix($byte, Visibility::High->value)) {
		print "True\n";
	}
	else {
		print "False\n";
	}

	// Get Active Levels as Array
	$arr = matrix2Elements($byte);
	print_r($arr);

	// Show Active Levels
	print "Visibilities: \n";
	foreach ($arr as $elem) {
		$visibility = Visibility::from($elem);
		print $visibility->name."\n";
	}
