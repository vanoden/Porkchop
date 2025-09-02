<?=$page->showAdminPageInfo()?>

<div class="tableBody">
	<div class="tableRowHeader">
		<div class="tableCell width-10per">Event Date</div>
		
		<div class="tableCell width-10per">Acted By</div>
		<div class="tableCell width-10per">Action</div>
		<div class="tableCell width-60per">Notes</div>
	</div>
<?php	foreach ($records as $record) {
			if (isset($users[$record->admin_id])) $user = $users[$record->admin_id];
			else {
				$user = $record->user();
				$users[$record->admin_id] = $user;
			}
?>
	<div class="tableRow">
		<div class="tableCell"><?=shortDate($record->event_date)?></div>
		<div class="tableCell"><?=$user->code?></div>
		
		<div class="tableCell"><?=$record->event_class?></div>
		<div class="tableCell"><?=$record->event_notes?></div>
	</div>
<?php	} ?>
</div>