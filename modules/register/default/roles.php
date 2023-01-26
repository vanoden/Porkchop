<div class="title">Roles</div>
<style>
	ul {
		list-style-type: none;
	}
</style>

<!-- Page Header -->
<?=$page->showBreadcrumbs()?>
<?=$page->showTitle()?>
<?=$page->showMessages()?>
<!-- End Page Header -->

<p><a href="/_register/role">Create Role</a></p>

<div class="tableBody">
	<div class="tableRowHeader">
		<div class="tableCell">Name</div>
		<div class="tableCell">Description</div>
		<div class="tableCell">Remove?</div>		
	</div>
<?php	foreach ($roles as $role) { ?>
	<div class="tableRow">
		<div class="tableCell"><a href="/_register/role/<?=$role->name?>"><?=$role->name?></a></div>
		<div class="tableCell"><?=$role->description?></div>
		<div class="tableCell"><a href="/_register/roles?remove_id=<?=$role->id?>">&#x1F5D1;</a></div>
	</div>
<?php	} ?>
</div>