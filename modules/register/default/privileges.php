<span class="title">Privileges</span>
<?php
    foreach ($privileges as $privilege) { ?>
<form name="privilege_delete">
<span class="label"><?=$privilege->name?></span>
<input type="hidden" name="delete_id" value="<?=$privilege->id?>">
<input type="submit" name="btn_delete" value="Delete" class="button">
</form>
<?php  } ?>
<form name="privilege_add">
<input type="text" name="newPrivilege" class="input" style="display: inline-block">
<input type="submit" name="btn_add" value="Add" class="button">
</form>