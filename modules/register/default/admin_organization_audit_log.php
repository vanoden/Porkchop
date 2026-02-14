<!-- Page Header -->
<?=$page->showAdminPageInfo()?>
<!-- End Page Header -->

<?php $activeTab = 'audit'; ?>
<?php
    // Show organization info container similar to product container
    $title = htmlspecialchars($organization->name ?: $organization->code);
?>
<div class="product-container">
    <div class="product-title"><?=$title?></div>
</div>
<?php
?>
<div class="tabs">
    <a href="/_register/admin_organization/<?= $organization->code ?>" class="tab <?= $activeTab==='details'?'active':'' ?>">Details</a>
    <a href="/_register/admin_organization_users/<?= $organization->code ?>" class="tab <?= $activeTab==='users'?'active':'' ?>">Users</a>
    <a href="/_register/admin_organization_tags/<?= $organization->code ?>" class="tab <?= $activeTab==='tags'?'active':'' ?>">Tags</a>
    <a href="/_register/admin_organization_locations/<?= $organization->code ?>" class="tab <?= $activeTab==='locations'?'active':'' ?>">Locations</a>
    <a href="/_register/admin_organization_audit_log/<?= $organization->code ?>" class="tab <?= $activeTab==='audit'?'active':'' ?>">Audit Log</a>
</div>

<div class="form_instruction">View audit log records for this organization.</div>

<div class="tableBody">
	<div class="tableRowHeader">
		<div class="tableCell width-10per">Event Date</div>
		<div class="tableCell width-10per">Acted By</div>
		<div class="tableCell width-10per">Action</div>
		<div class="tableCell width-60per">Notes</div>
	</div>
<?php	if (!empty($records)) {
		foreach ($records as $record) {
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
<?php		}
	} else { ?>
	<div class="tableRow">
		<div class="tableCell" colspan="4">No audit log records found for this organization.</div>
	</div>
<?php	} ?>
</div>
