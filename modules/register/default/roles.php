<!-- Page Header -->
<?=$page->showAdminPageInfo()?>
<!-- End Page Header -->

<form action="/_register/role" method="get" class="register-roles-create-form">
	<button type="submit" class="button">Create Role</button>
</form>

<div class="tableBody">
	<div class="tableRowHeader">
		<div class="tableCell">Name</div>
		<div class="tableCell">Description</div>
		<div class="tableCell register-roles-remove-cell">Remove?</div>		
	</div>
<?php	foreach ($roles as $role) { ?>
	<div class="tableRow">
		<div class="tableCell"><a href="/_register/role/<?=$role->name?>"><?=$role->name?></a></div>
		<div class="tableCell"><?=strip_tags($role->description)?></div>
		<div class="tableCell register-roles-remove-cell"><a href="/_register/roles?remove_id=<?=$role->id?>"><img src="/img/icons/icon_tools_trash_active.svg" class="width-30px-small register-roles-delete-icon" alt="delete role"></a></div>
	</div>
<?php	} ?>
</div>