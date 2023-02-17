<?php
	$dir = $argv[1];
	if (! is_dir($dir)) {
		print "Directory not found or is not directory\n";
		exit;
	}
	$index = "$dir/index.html";
	if (! file_exists($dir."/index.html")) {
		print "Index file not found at $index\n";
		exit;
	}

	$el = "\r\n";

	# Load File
	$index_data = array();
	$index_raw = file_get_contents($index);
	$index_recs = preg_split('/\r?\n/',$index_raw);
	foreach ($index_recs as $record) {
		if (! preg_match('/^\<tr\>\<td\>/',$record)) continue;
		$rec_parts = preg_split('/\<\/td\>/',$record);
		$index_name = preg_replace('/\<tr\>\<td\>/','',$rec_parts[0]);
		$index_purpose = preg_replace('/\<td\>/','',$rec_parts[3]);
		//print $index_name." = ".$index_purpose."\n";
		$index_data[$index_name] = $index_purpose;
	}

	$icons = scandir($dir);

	if (!$fh = fopen($index,'w')) {
		print "Cannot open file $index for writing\n";
		exit;
	}

	// Table Header
	fwrite($fh,"<html>$el<table>$el<tr>$el");
	fwrite($fh,"\t<th>Name</th><th>URL</th><th>Default</th><th>Purpose</th>$el");

	// Table Body - Icons
	foreach ($icons as $icon) {
		if (preg_match('/^(.+)\.(svg|png|jpg)$/',$icon,$matches)) {
			$icon_name = $matches[1];
			$icon_file = $matches[0];
			$icon_ext = $matches[2];
			if (! isset($index_data[$icon_name])) $index_data[$icon_name] = "Unset";
			fwrite($fh,"<tr>$el");
			fwrite($fh,"\t<td>$icon_name</td>$el");
			fwrite($fh,"\t<td>$icon_file</td>$el");
			fwrite($fh,"\t<td><img src=\"/img/icons/".$icon_file."\" height=\"20px\" alt=\"$icon_name\"/></td>$el");
			fwrite($fh,"\t<td>".$index_data[$icon_name]."</td>$el");
			fwrite($fh,"</tr>$el");
		}
	}

	// Table Footer
	fwrite($fh,"</table>\r\n</html>$el");
	fclose($fh);

	print "Index file $index generated\n";
