<?= $page->showAdminPageInfo() ?>

<?php
	foreach ($weeks as $week) {
?>
<span><?=$week->number() ?></span>
<?php
		$days = $week->days();
		foreach ($days as $day) {
?>
		<span><?=$day->date('j') ?></span>
<?php
		}
	}
?>