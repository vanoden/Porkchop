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
<div class="title">Engineering Tasks [<?=count($tasks)?>]</div>
<form>
<input type="button" name="btn_new_task" value="Add Task" class="button" onclick="newTask();" />
<table class="body">
<tr><td class="label">Code</td>
	<td class="label" style="width: 200px">Title</td>
	<td class="label" style="width: 160px">Added</td>
	<td class="label" style="width: 160px">Requested By</td>
	<td class="label" style="width: 160px">Assigned To</td>
	<td class="label" style="width: 120px">Status</td>
</tr>
<?php
	foreach ($tasks as $task) {
		$requestor = $task->requestedBy();
		$worker = $task->assignedTo();
?>
<tr><td class="value"><a href="/_engineering/task/<?=$task->code?>"><?=$task->code?></a></td>
	<td class="value"><?=$task->title?></td>
	<td class="value"><?=$task->date_added?></td>
	<td class="value"><?=$requestor->login?></td>
	<td class="value"><?=$worker->login?></td>
	<td class="value"><?=$task->status?></td>
<?php	} ?>
</table>
</form>