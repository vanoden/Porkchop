<?php
	print "<?xml version=\"1.0\" encoding=\"ISO-8859-1\"?>\n";
	print "<?xml-stylesheet type=\"text/xsl\" href=\"/monitor.monitors.xsl\"?>\n";
	print "<monitor>\n";
	print $xml->getSerializedData();
	print "</monitor>";
