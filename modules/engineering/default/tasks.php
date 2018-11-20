<script language="Javascript">
	function newTask() {
		document.forms[0].action = "/_engineering/task";
		document.forms[0].submit();
		return true;
	}
</script>
<?	if ($page->error) { ?>
    <div class="form_error"><?=$page->error?></div>
<?	} ?>
<div class="breadcrumbs">
    <a class="breadcrumb" href="/_engineering/home">Engineering</a> > Tasks
</div>
<?php include(MODULES.'/engineering/partials/search_bar.php'); ?>
<?php include(MODULES.'/engineering/partials/tasks_list.php'); ?>
