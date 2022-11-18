<script language="Javascript">
	function newTask() {
	    var tasksListForm = document.getElementById('tasksListForm');
		tasksListForm.action = "/_engineering/task";
		tasksListForm.submit();
		return true;
	}
</script>
<?php	if ($page->error) { ?>
    <div class="form_error"><?=$page->error?></div>
<?php	} ?>
<div class="breadcrumbs">
    <a class="breadcrumb" href="/_engineering/home">Engineering</a> > Tasks
</div>
<?php include(MODULES.'/engineering/default/partials/search_bar.php'); ?>
<?php include(MODULES.'/engineering/default/partials/tasks_list.php'); ?>
