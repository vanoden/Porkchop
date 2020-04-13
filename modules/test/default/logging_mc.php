<?php
	$page = new \Site\Page();

	if (isset($_REQUEST['log_type'])) $log_type = $_REQUEST['log_type'];
	else $log_type = APPLICATION_LOG_TYPE;

	if (isset($_REQUEST['log_level'])) $log_level = $_REQUEST['log_level'];
	else $log_level = APPLICATION_LOG_LEVEL;

	$log_path = APPLICATION_LOG;

	if (isset($_REQUEST['message'])) $message = $_REQUEST['message'];
	else $message = 'This is a test message';
	if (isset($_REQUEST['level'])) $level = $_REQUEST['level'];
	else $level = 'error';

	if ($_REQUEST['btn_submit']) {
		print "Writing message to $log_type<br>\n";

		$log = \Site\Logger::get_instance(array('type' => $log_type,'path' => $log_path,'level' => $log_level));
		print "Connecting to log<br>\n";
		if ($log->connect()) {
			print "Writing Log<br>\n";
			$log->writeln($message,$level);
			print "Written, check your log\n";
		}
		else {
			print "Failed to connect\n";
			print $log->error();
		}
		return;
	}
	else {
?>
<form method="post" action="/_test/logging">
Log Type<br>
<select name="log_type">
	<option value="syslog">syslog</option>
	<option value="file">file</option>
	<option value="errorlog">errorlog</option>
	<option value="screen">screen</option>
</select>
<br>
Log Level<br>
<select name="log_level">
	<option value="emergency">emergency</option>
	<option value="alert">alert</option>
	<option value="critical">critical</option>
	<option value="error">error</option>
	<option value="notice">notice</option>
	<option value="warning">warning</option>
	<option value="info">info</option>
	<option value="debug">debug</option>
	<option value="trace">trace</option>
</select>
<br>
Message<br>
<input type="text" name="message"/>
<br>
Level<br>
<select name="level">
	<option value="emergency">emergency</option>
	<option value="alert">alert</option>
	<option value="critical">critical</option>
	<option value="error">error</option>
	<option value="notice">notice</option>
	<option value="warning">warning</option>
	<option value="info">info</option>
	<option value="debug">debug</option>
	<option value="trace">trace</option>
</select>
<input type="submit" name="btn_submit" value="Write to Log" />
</form>
<?php	} ?>
