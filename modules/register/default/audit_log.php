<?=$page->showAdminPageInfo()?>

<div class="tableBody">
	<div class="tableRowHeader">
		<div class="tableCell width-10per">Event Date</div>
		<div class="tableCell width-10per">Acted On</div>
		<div class="tableCell width-10per">Acted By</div>
		<div class="tableCell width-10per">Action</div>
		<div class="tableCell width-60per">Notes</div>
	</div>
<?php if (!empty($records)) {
	$targets = [];
	$actors = [];
	foreach ($records as $record) {
		$target = null;
		if (!empty($record->instance_id)) {
			if (!isset($targets[$record->instance_id])) {
				$targets[$record->instance_id] = new \Register\Customer($record->instance_id);
			}
			$target = $targets[$record->instance_id];
		}
		$actor = null;
		if (!empty($record->user_id)) {
			if (!isset($actors[$record->user_id])) {
				$actors[$record->user_id] = new \Register\Customer($record->user_id);
			}
			$actor = $actors[$record->user_id];
		}
?>
	<div class="tableRow">
		<div class="tableCell"><?=shortDate($record->event_date)?></div>
		<div class="tableCell"><?= $target ? $target->code : 'N/A' ?></div>
		<div class="tableCell"><?= $actor ? $actor->code : 'System' ?></div>
		<div class="tableCell"><?= htmlspecialchars($record->class_method ?? '') ?></div>
		<div class="tableCell"><?= htmlspecialchars($record->description ?? '') ?></div>
	</div>
<?php }
} else { ?>
	<div class="tableRow">
		<div class="tableCell" colspan="5">No audit log records found.</div>
	</div>
<?php } ?>
</div>
