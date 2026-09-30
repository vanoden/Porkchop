<?=$page->showSubHeading()?>

<?php
$activeTab = 'privileges';
require __DIR__ . '/admin_account_tabs.php';
?>

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
	</div><!-- .register-admin-account__content -->
</div><!-- .register-admin-account -->
