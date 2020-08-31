<script language="Javascript">
	function updateMeta(idx) {
		document.forms[1].key.value = document.getElementById('key_'+idx).value;
		document.forms[1].value.value = document.getElementById('value_'+idx).value;
		document.forms[1].type.value = document.getElementById('type_'+idx).value;
		document.forms[1].todo.value = 'update';
		document.forms[1].submit();
	}
	function dropMeta(idx) {
		document.forms[1].key.value = document.getElementById('key_'+idx).value;
		document.forms[1].todo.value = 'drop';
		document.forms[1].submit();
	}
	function addMeta() {
		document.forms[1].key.value = document.getElementById('key_').value;
		document.forms[1].value.value = document.getElementById('value_').value;
		document.forms[1].type.value = document.getElementById('type_').value;
		document.forms[1].todo.value = 'add';
		document.forms[1].submit();
	}
</script>
<h2>Dashboard Details</h2>
<?php	if ($page->errorCount() > 0) { ?>
<div class="form_error"><?=$page->errorString()?></div>
<?php	} ?>
<form method="post" action="/_monitor/admin_dashboard">
<input type="hidden" name="id" value="<?=$dashboard->id?>" />
<div class="container">
	<span class="label">Name</span>
	<input type="text" name="name" class="value input" value="<?=$dashboard->name?>" />
</div>
<div class="container">
	<span class="label">Template</span>
	<input type="text" name="template" class="value input" value="<?=$dashboard->template?>" />
</div>
<div class="container">
	<span class="label">Status</span>
	<select name="status" class="value input">
		<option value="NEW"<? if ($dashboard->status == 'NEW') print " selected"; ?>>New</option>
		<option value="HIDDEN"<? if ($dashboard->status == 'HIDDEN') print " selected"; ?>>Hidden</option>
		<option value="TEST"<? if ($dashboard->status == 'TEST') print " selected"; ?>>Test</option>
		<option value="PUBLISHED"<? if ($dashboard->status == 'PUBLISHED') print " selected"; ?>>Published</option>
	</select>
</div>
<div class="form_footer">
	<input type="submit" name="btn_submit" class="button" value="Update Dashboard" />
</div>
</form>
<form method="post" action="/_monitor/admin_dashboard">
<input type="hidden" name="id" value="<?=$dashboard->id?>" />
<input type="hidden" name="key" value="" />
<input type="hidden" name="value" value="" />
<input type="hidden" name="type" value="" />
<input type="hidden" name="todo" value="" />
<div class="subheading">Metadata</div>
<div class="table">
	<div class="tableRowHeader">
		<div class="tableCell">Key</div>
		<div class="tableCell">Value</div>
		<div class="tableCell">Type</div>
		<div class="tableCell">Actions</div>
	</div>
<?php	$idx = 0;
	foreach ($metadata as $record) { 
?>
	<div class="tableRow">
		<div class="tableCell"><?=$record->key?><input id="key_<?=$idx?>" type="hidden" name="key_<?=$idx?>" value="<?=$record->key?>" /></div>
		<div class="tableCell"><input id="value_<?=$idx?>" type="text" name="value_<?=$idx?>" value='<?=$record->value?>' /></div>
		<div class="tableCell">
			<select name="type_<?=$idx?>" id="type_<?=$idx?>" class="value input">
				<option value="SCALAR"<? if ($record->type == 'SCALAR') print ' selected';?>>Scalar</option>
				<option value="OBJECT"<? if ($record->type == 'OBJECT') print ' selected';?>>Object</option>
			</select>
		</div>
		<div class="tableCell">
			<input type="button" name="update_<?=$idx?>" value="Update" class="button" onclick="updateMeta('<?=$idx?>');" />
			<input type="button" name="drop_<?=$idx?>" value="Drop" class="button" onclick="dropMeta('<?=$idx?>');" />
		</div>
	</div>
<?php	
		$idx ++;
	}
?>
	<div class="tableRow">
		<div class="tableCell"><input type="text" id="key_" name="_key" class="value input" /></div>
		<div class="tableCell"><input type="text" id="value_" name="_value" class="value input" /></div>
		<div class="tableCell">
			<select name="type_" id="type_" class="value input">
				<option value="SCALAR"<? if ($record->type == 'SCALAR') print ' selected';?>>Scalar</option>
				<option value="OBJECT"<? if ($record->type == 'OBJECT') print ' selected';?>>Object</option>
			</select>
		</div>
		<div class="tableCell"><input type="button" name="add" value="Add" class="button" onclick="addMeta();" /></div>
	</div>
</div>
</form>
