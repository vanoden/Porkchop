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
<a class="breadcrumb" href="/_engineering/home">Engineering</a>
</div>
<div class="title">Engineering Tasks [<?=count($tasks)?>]</div>
<form>
<input type="button" name="btn_new_task" value="Add Task" class="button" onclick="newTask();" />
<div class="filter_container">
	<input type="checkbox" name="complete" value="1"<? if ($_REQUEST['complete']) print " checked"; ?> onchange="document.forms[0].submit(); " />Completed
	<input type="checkbox" name="cancelled" value="1"<? if ($_REQUEST['cancelled']) print " checked"; ?> onchange="document.forms[0].submit(); " />Cancelled
	<input type="checkbox" name="hold" value="1"<? if ($_REQUEST['hold']) print " checked"; ?> onchange="document.forms[0].submit(); " />Hold
</div>
<table class="body">
<tr> <td class="label" style="width: 250px">Title</td>
	<td class="label" style="width: 120px">Added</td>
	<td class="label" style="width: 120px">Assigned To</td>
	<td class="label" style="width: 120px">Status</td>
	<td class="label" style="width: 160px">Product</td>
	<td class="label" style="width: 120px">Priority</td>
</tr>
<?php
	foreach ($tasks as $task) {
		$product = $task->product();
		$worker = $task->assignedTo();
?>
<tr><td class="value"><a href="/_engineering/task/<?=$task->code?>"><?=$task->title?></a></td>
	<td class="value"><?=date('m/d/Y',$task->timestamp_added)?></td>
	<td class="value"><?=$worker->login?></td>
	<td class="value"><?=$task->status?></td>
	<td class="value"><?=$product->title?></td>
	<td class="value"><?=$task->priority?></td>
</tr>
<?php	} ?>
</table>
</form>
