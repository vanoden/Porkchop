<?=$page->showAdminPageInfo()?>

<div class="tableBody">
	<div class="tableRowHeader">
		<div class="tableCell" style="width: 10%;">Event Date</div>
		
		<div class="tableCell" style="width: 10%;">Acted By</div>
		<div class="tableCell" style="width: 10%;">Action</div>
		<div class="tableCell" style="width: 60%;">Notes</div>
	</div>
<?php	foreach ($records as $record) { 
			if (isset($users[$record->user_id])) $user = $users[$record->user_id];
			else {
				$user = $record->user();
				$users[$record->user_id] = $user;
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