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
<h2>Engineering Tasks [<?=count($tasks)?>]</h2>
<form>
	

<!--	START First Table -->
	<div class="tableBody min-tablet">
	<div class="tableRowHeader">
		<div class="tableCell" style="width: 25%;">Assigned To</div>
		<div class="tableCell" style="width: 25%;">Project</div>
		<div class="tableCell" style="width: 50%;"></div>
	</div>
	<div class="tableRow">
		<div class="tableCell">
			<select name="assigned_id" class="value input" onchange="document.forms[0].submit();">
				<option value="">Any</option>
				<?	foreach ($assigners as $assigner) { ?>
				<option value="<?=$assigner->id?>"<? if ($assigner->id == $_REQUEST['assigned_id']) print " selected"; ?>><?=$assigner->login?></option>
				<?	} ?>
			</select>
		</div>
		<div class="tableCell">
			<select name="project_id" class="value input" onchange="document.forms[0].submit();">
				<option value="">Any</option>
				<?	foreach ($projects as $project) { ?>
				<option value="<?=$project->id?>"<? if ($project->id == $_REQUEST['project_id']) print " selected"; ?>><?=$project->title?></option>
				<?	} ?>
			</select>
		</div>
		<div class="tableCell">
			<input type="checkbox" name="complete" value="1"<? if ($_REQUEST['complete']) print " checked"; ?> onchange="document.forms[0].submit(); " />Completed
			<input type="checkbox" name="cancelled" value="1"<? if ($_REQUEST['cancelled']) print " checked"; ?> onchange="document.forms[0].submit(); " />Cancelled
			<input type="checkbox" name="hold" value="1"<? if ($_REQUEST['hold']) print " checked"; ?> onchange="document.forms[0].submit(); " />Hold
		</div>
	</div>
</div>
<!--	END First Table -->		

	
<!--	START First Table -->
	<div class="tableBody min-tablet">
	<div class="tableRowHeader">
		<div class="tableCell" style="width: 35%;">Title</div>
		<div class="tableCell" style="width: 15%;">Added</div>
		<div class="tableCell" style="width: 15%;">Assigned To</div>
		<div class="tableCell" style="width: 10%;">Status</div>
		<div class="tableCell" style="width: 15%;">Product</div>
		<div class="tableCell" style="width: 10%;">Priority</div>
	</div>
<?php
	foreach ($tasks as $task) {
		$product = $task->product();
		$worker = $task->assignedTo();
?>
	<div class="tableRow">
		<div class="tableCell">
			<a href="/_engineering/task/<?=$task->code?>"><?=$task->title?></a>
		</div>
		<div class="tableCell">
			<?=date('m/d/Y',$task->timestamp_added)?>
		</div>
		<div class="tableCell">
			<?=$worker->login?>
		</div>
		<div class="tableCell">
			<?=$task->status?>
		</div>
		<div class="tableCell">
			<?=$product->title?>
		</div>
		<div class="tableCell">
			<?=$task->priority?>
		</div>
	</div>
<?php	} ?>
</div>
<!--	END First Table -->			
	
<div class="button-bar">
	<input type="button" name="btn_new_task" value="Add Task" class="button" onclick="newTask();" />
</div>
</form>
