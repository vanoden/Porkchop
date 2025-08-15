<?=$page->showAdminPageInfo()?>

<div class="tableBody">
	<div class="tableRowHeader">
		<div class="tableCell register-audit-log-event-date-cell">Event Date</div>
		<div class="tableCell register-audit-log-acted-on-cell">Acted On</div>
		<div class="tableCell register-audit-log-acted-by-cell">Acted By</div>
		<div class="tableCell register-audit-log-action-cell">Action</div>
		<div class="tableCell register-audit-log-notes-cell">Notes</div>
	</div>
<?php	foreach ($records as $record) { 
			if (isset($users[$record->user_id])) $user = $users[$record->user_id];
			else {
				$user = $record->user();
				$users[$record->user_id] = $user;
			}
			if (isset($users[$record->admin_id])) $admin = $users[$record->admin_id];
			else {
				$admin = $record->admin();
				$users[$record->admin_id] = $admin;
			}
?>
	<div class="tableRow">
		<div class="tableCell"><?=shortDate($record->event_date)?></div>
		<div class="tableCell"><?=$user->code?></div>
		<div class="tableCell"><?=$admin->code?></div>
		<div class="tableCell"><?=$record->event_class?></div>
		<div class="tableCell"><?=$record->event_notes?></div>
	</div>
<?php	} ?>
</div>
