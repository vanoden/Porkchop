<?=$page->showSubHeading()?>

<?php $activeTab = 'privileges'; ?>

<div class="tabs">
    <a href="/_register/admin_account?customer_id=<?= $customer_id ?>" class="tab <?= $activeTab==='login'?'active':'' ?>">Login / Registration</a>
    <a href="/_register/admin_account_contacts?customer_id=<?= $customer_id ?>" class="tab <?= $activeTab==='contacts'?'active':'' ?>">Methods of Contact</a>
    <a href="/_register/admin_account_password?customer_id=<?= $customer_id ?>" class="tab <?= $activeTab==='password'?'active':'' ?>">Change Password</a>
    <a href="/_register/admin_account_roles?customer_id=<?= $customer_id ?>" class="tab <?= $activeTab==='roles'?'active':'' ?>">Assigned Roles</a>
    <a href="/_register/admin_account_auth_failures?customer_id=<?= $customer_id ?>" class="tab <?= $activeTab==='auth_failures'?'active':'' ?>">Recent Auth Failures</a>
    <a href="/_register/admin_account_terms?customer_id=<?= $customer_id ?>" class="tab <?= $activeTab==='terms'?'active':'' ?>">Terms of Use History</a>
    <a href="/_register/admin_account_locations?customer_id=<?= $customer_id ?>" class="tab <?= $activeTab==='locations'?'active':'' ?>">Locations</a>
    <a href="/_register/admin_account_images?customer_id=<?= $customer_id ?>" class="tab <?= $activeTab==='images'?'active':'' ?>">User Images</a>
    <a href="/_register/admin_account_backup_codes?customer_id=<?= $customer_id ?>" class="tab <?= $activeTab==='backup_codes'?'active':'' ?>">Backup Codes</a>
    <a href="/_register/admin_account_search_tags?customer_id=<?= $customer_id ?>" class="tab <?= $activeTab==='search_tags'?'active':'' ?>">Search Tags</a>
    <a href="/_register/admin_account_audit_log?customer_id=<?= $customer_id ?>" class="tab <?= $activeTab==='audit'?'active':'' ?>">Audit Log</a>
    <a href="/_register/admin_account_register_audit?customer_id=<?= $customer_id ?>" class="tab <?= $activeTab==='register_audit'?'active':'' ?>">Failed Logins</a>
	<a href="/_register/admin_account_privileges?customer_id=<?= $customer_id ?>" class="tab <?= $activeTab==='privileges'?'active':'' ?>">Assigned Privileges</a>
</div>
<h3><?="Privileges assigned to " . $customer->first_name . " " . $customer->last_name?></h3>
<div class="tableBody">
	<div class="tableRowHeader">
		<div class="tableCell">Role</div>
		<div class="tableCell">Level</div>
		<div class="tableCell">Privilege</div>
	</div>
	<?php
	foreach ($privileges as $privilege) {	?>
	<div class="tableRow">
		<div class="tableCell"><?=$privilege['role']?></div>
		<div class="tableCell"><?=$privilege['level']?></div>
		<div class="tableCell"><?=$privilege['privilege']?></div>
	</div>
	<?php
	}
	if (!is_array($privileges) || !count($privileges)) {
	?>
	<div class="tableRow">
		<div class="tableCell"><p>No Privileges Assigned</p></div>
	</div>
	<?php
	}
	?>
</div>