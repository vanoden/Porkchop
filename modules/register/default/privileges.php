<!-- Page Header -->
<?=$page->showBreadcrumbs()?>
<?=$page->showTitle()?>
<?=$page->showMessages()?>
<!-- End Page Header -->

<div class="form_instruction">
	<strong>Privilege Management:</strong> Manage system privileges and their associated modules. Privileges control access to specific functionality within the application.
</div>

<h3>Add New Privilege</h3>
<form name="privilege_add" action="/_register/privileges" method="post">
	<input type="hidden" name="csrfToken" value="<?=$GLOBALS['_SESSION_']->getCSRFToken()?>">
	<section class="tableBody clean min-tablet">
		<div class="tableRowHeader">
			<div class="tableCell width-20per">Privilege Name</div>
			<div class="tableCell width-20per">Module</div>
			<div class="tableCell width-60per">Actions</div>
		</div>
		<div class="tableRow">
			<div class="tableCell">
				<input type="text" name="newPrivilege" class="value input width-100per" placeholder="Enter privilege name" required />
			</div>
			<div class="tableCell">
				<input type="text" name="newModule" class="value input width-100per" placeholder="Enter module name" />
			</div>
			<div class="tableCell">
				<div class="button-group">
					<input type="submit" name="btn_add" value="Add" class="button">
				</div>
			</div>
		</div>
	</section>
</form>

<h3>Existing Privileges</h3>
<?php if (isset($privileges) && count($privileges) > 0) { ?>
<form name="privileges_form" action="/_register/privileges" method="post">
	<input type="hidden" name="csrfToken" value="<?=$GLOBALS['_SESSION_']->getCSRFToken()?>">
	<section class="tableBody clean min-tablet">
		<div class="tableRowHeader">
			<div class="tableCell width-20per">Privilege Name</div>
			<div class="tableCell width-20per">Module</div>
			<div class="tableCell width-60per">Actions</div>
		</div>
<?php foreach ($privileges as $privilege) { ?>
		<div class="tableRow">
			<div class="tableCell">
				<input type="text" name="name[<?=$privilege->id?>]" class="value input width-100per" value="<?=htmlspecialchars($privilege->name)?>" placeholder="Privilege name" required />
			</div>
			<div class="tableCell">
				<input type="text" name="module[<?=$privilege->id?>]" class="value input width-100per" value="<?=htmlspecialchars($privilege->module ?? '')?>" placeholder="Module name" />
			</div>
			<div class="tableCell">
				<div class="button-group">
					<input type="submit" name="btn_update" value="Update" class="button" onclick="document.querySelector('input[name=privilege_id]').value='<?=$privilege->id?>';">
					<input type="submit" name="btn_delete" value="Delete" class="button secondary" onclick="document.querySelector('input[name=privilege_id]').value='<?=$privilege->id?>'; return confirm('Are you sure you want to delete this privilege?');">
				</div>
			</div>
		</div>
<?php } ?>
	</section>
	<input type="hidden" name="privilege_id" value="">
</form>
<?php } else { ?>
<div class="marginTop_20">
	<div class="value" style="text-align: center; color: #666; padding: 20px;">
		No privileges found. Add a new privilege above to get started.
	</div>
</div>
<?php } ?>
