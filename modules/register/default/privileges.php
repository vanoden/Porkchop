<span class="title">Privileges</span>
<?php if ($page->errorCount() > 0) { ?>
<div class="form_error"><?=$page->errorString()?></div>
<?php } elseif ($page->success) { ?>
<div class="form_success"><?=$page->success?></div>
<?php } ?>
<?php
    foreach ($privileges as $privilege) { ?>
<form name="privilege_delete" method="post">
<span class="label" style="display: inline-block; width: 250px;"><?=$privilege->name?></span>
<input type="hidden" name="delete_id" value="<?=$privilege->id?>">
<input type="submit" name="btn_delete" value="Delete" class="button">
</form>
<?php  } ?>
<form name="privilege_add">
<input type="text" name="newPrivilege" class="input" style="display: inline-block">
<input type="submit" name="btn_add" value="Add" class="button">
</form>
