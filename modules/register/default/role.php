<?	if ($page->errorCount()) { ?>
<div class="form_error"><?=$page->errorString()?></div>
<?	};
	if ($page->success) { ?>
<div class="form_success"><?=$page->success?></div>
<?	} ?>
<form method="post" action="/_register/role">
<input type="hidden" name="name" value="<?=$role->name?>" />
<div class="title">Role <?=$role->name?></div>
<span class="label">Name</span><span class="value"><?=$role->name?></span>
<span class="label">Description</span><input type="text" name="description" class="value input" value="<?=$role->description?>" />
<input type="submit" name="btn_submit" class="button" value="Submit" />
<div id="rolePrivilegesContainer">
<span class="label">Privileges</span>
<?php	foreach ($privileges as $privilege) { ?>
	<div class="rolePrivilegeContainer">
		<span class="value"><?=$privilege->name?></span>
	</div>
<?	} ?>
</div>
