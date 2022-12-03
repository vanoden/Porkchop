<span class="title">Privileges</span>

<?php if ($page->errorCount() > 0) { ?>
<section id="form-message">
	<ul class="connectBorder errorText">
		<li><?=$page->errorString()?></li>
	</ul>
</section>

<?php	} else if ($page->success) { ?>
<section id="form-message">
	<ul class="connectBorder progressText">
		<li><?=$page->success?></li>
	</ul>
</section>
<?php	} ?>

<?php
    foreach ($privileges as $privilege) { 
?>
    <form name="privilege_delete" action="/_register/privileges" method="post">
        <input type="text" name="name[<?=$privilege->id?>]" class="value input" style="display: inline-block; width: 250px;" value="<?=$privilege->name?>"/>
        <input type="text" name="module[<?=$privilege->id?>]" class="value input" style="display: inline-block; width: 150px;" value="<?=$privilege->module?>"/>
        <input type="hidden" name="privilege_id" value="<?=$privilege->id?>">
        <input type="hidden" name="csrfToken" value="<?=$GLOBALS['_SESSION_']->getCSRFToken()?>">
        <input type="submit" name="btn_update" value="Update" class="button">
        <input type="submit" name="btn_delete" value="Delete" class="button">
    </form>
<?php  } ?>
<form name="privilege_add" action="/_register/privileges" method="post">
    <input type="text" name="newPrivilege" class="input" style="display: inline-block">
    <input type="hidden" name="csrfToken" value="<?=$GLOBALS['_SESSION_']->getCSRFToken()?>">
    <input type="submit" name="btn_add" value="Add" class="button">
</form>
