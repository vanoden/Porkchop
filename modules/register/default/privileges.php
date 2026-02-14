<!-- Page Header -->
<?=$page->showAdminPageInfo()?>
<!-- End Page Header -->

<div class="tableBody bandedRows">
	<div class="tableRowHeader">
		<div class="tableCell">Name</div>
		<div class="tableCell">Module</div>
		<div class="tableCell">Actions</div>
	</div>
<?php foreach ($privileges as $privilege) { ?>
	<form name="privilege_<?=$privilege->id?>" action="/_register/privileges" method="post" class="tableRow register-privileges-form-row">
		<div class="tableCell">
			<label for="name_<?=$privilege->id?>" class="sr-only">Name</label>
			<input type="text" id="name_<?=$privilege->id?>" name="name[<?=$privilege->id?>]" class="value input register-privileges-name-input" value="<?=htmlspecialchars($privilege->name)?>"/>
		</div>
		<div class="tableCell">
			<label for="module_<?=$privilege->id?>" class="sr-only">Module</label>
			<input type="text" id="module_<?=$privilege->id?>" name="module[<?=$privilege->id?>]" class="value input register-privileges-module-input" value="<?=htmlspecialchars($privilege->module)?>"/>
		</div>
		<div class="tableCell">
			<input type="hidden" name="privilege_id" value="<?=$privilege->id?>">
			<input type="hidden" name="csrfToken" value="<?=$GLOBALS['_SESSION_']->getCSRFToken()?>">
			<input type="submit" name="btn_update" value="Update" class="button">
			<input type="submit" name="btn_delete" value="Delete" class="button">
		</div>
	</form>
<?php } ?>
</div>

<form name="privilege_add" action="/_register/privileges" method="post" class="register-privileges-add-form">
	<label for="newPrivilege" class="sr-only">New privilege name</label>
	<input type="text" id="newPrivilege" name="newPrivilege" class="input register-privileges-new-input" placeholder="New privilege name">
	<input type="hidden" name="csrfToken" value="<?=$GLOBALS['_SESSION_']->getCSRFToken()?>">
	<input type="submit" name="btn_add" value="Add" class="button">
</form>
