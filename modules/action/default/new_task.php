<style>
	.label {
		width: 100%;
	}
	.value {
		width: 100%;
	}
	.question {
		width: 200px;
		float: left;
	}
	#taskDescription {
		width: 100%;
	}
	.form_footer {
		width: 600px;
		height: 50px;
		text-align: center;
	}
</style>
<script language="JavaScript">
	var form_complete = <?=$form_complete?>;
	function onLoad() {
		if (form_complete == 1) {
			window.opener.location.reload(false);
			window.close();
		}
    }
</script>
<div class="title">New Task</div>
<?php	if ($GLOBALS['_page']->error) { ?>
<div class="form_error"><?=$GLOBALS['_page']->error?></div>
<?php	} ?>
<form name="newTaskForm" action="/_action/new_task" method="post">
<input type="hidden" name="asset_id" value="<?=$asset->id?>" />
<div class="question">
	<div class="label">Type</div>
	<div class="value">
		<select name="type_id" class="value input">
			<option value="">Select</option>
	<?php	foreach ($types as $type) { ?>
			<option value="<?=$type->id?>"><?=$type->code?></option>
	<?php	} ?>
		</select>
	</div>
</div>
<div class="question">
	<div class="label">Requestor</div>
	<div class="value">
		<select name="customer_id" class="value input">
			<option value="">Select</option>
	<?php	foreach ($customers as $customer) { ?>
			<option value="<?=$customer->id?>"><?=$customer->first_name?> <?=$customer->last_name?></option>
	<?php	} ?>
		</select>
	</div>
</div>
<div class="question">
	<div class="label">Assignee</div>
	<div class="value">
		<select name="user_assigned" class="value input">
			<option value="">Unassigned</option>
	<?php	foreach ($techs as $tech) {
			$realtech = new RegisterCustomer($tech->id);
			if ($realtech->has_role('action user')) { ?>
			<option value="<?=$tech->id?>"><?= $tech->first_name?> <?=$tech->last_name?></option>
	<?php		}
		} ?>
		</select>
	</div>
</div>
<div class="question" id="taskDescription">
	<div class="label">Description</div>
	<div class="value">
		<textarea name="description" class="value input" rows="4"></textarea>
	</div>
</div>
<div class="form_footer">
	<input type="submit" name="btn_submit" class="button" />
</div>
</form>
