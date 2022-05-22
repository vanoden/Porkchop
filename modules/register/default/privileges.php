<span class="title">Privileges</span>
<?php if ($page->errorCount() > 0) { ?>
<div class="form_error"><?=$page->errorString()?></div>
<?php } elseif ($page->success) { ?>
<div class="form_success"><?=$page->success?></div>
<?php } ?>
<?php
    foreach ($privileges as $privilege) { ?>
<form name="privilege_delete" action="/_register/privileges" method="post">
<input type="text" name="name[<?=$privilege->id?>]" class="value input" style="display: inline-block; width: 250px;" value="<?=$privilege->name?>"/>
<input type="text" name="module[<?=$privilege->id?>]" class="value input" style="display: inline-block; width: 150px;" value="<?=$privilege->module?>"/>
<input type="hidden" name="privilege_id" value="<?=$privilege->id?>">
<input type="submit" name="btn_update" value="Update" class="button">
<input type="submit" name="btn_delete" value="Delete" class="button">
</form>
<?php  } ?>
<form name="privilege_add" action="/_register/privileges" method="post">
<input type="text" name="newPrivilege" class="input" style="display: inline-block">
<input type="submit" name="btn_add" value="Add" class="button">
</form>
