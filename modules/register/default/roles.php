<!-- Page Header -->
<?=$page->showTitle()?>
<?=$page->showBreadcrumbs()?>
<?=$page->showMessages()?>
<!-- End Page Header -->

<a class="button" href="/_register/role">Create Role</a>

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
		<div class="tableCell"><a href="/_register/roles?remove_id=<?=$role->id?>"><img src="/img/icons/icon_tools_trash_active.svg" style="cursor: pointer; width: 20px; border: none;" alt="delete role"></a></div>
	</div>
<?php	} ?>
</div>