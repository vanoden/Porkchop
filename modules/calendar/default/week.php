<!-- Week Header -->
<?php
	$days = $week->days();

	foreach ($days as $day) {
?>
<!-- Day Header -->
<?php
		$events = $day->allDayEvents();
		foreach ($events as $event) {
?>
<!-- All Day Event -->
<?php	}

		$hours = $day->hours();

		foreach ($hours as $hour) {
			$events = $hour->events();
?>
<!-- Event -->
<?php	} ?>
<!-- Day Footer -->
<?php } ?>
<!-- Week Footer -->
