<style>
	th {
		text-align: left;
	}
</style>
<script language="Javascript">
	function drop(id) {
		document.forms[0].delete.value = id;
		document.forms[0].submit();
	}
</script>
<?php	if ($page->errorCount()) { ?>
<div class="form_error"><?=$page->errorString()?></div>
<?php	} ?>
<form name="menuForm" action="/_navigation/menu" method="post">
<input type="hidden" name="csrfToken" value="<?=$GLOBALS['_SESSION_']->getCSRFToken()?>">
<input type="hidden" name="id" value="<?=$menu->id?>" />
<input type="hidden" name="delete" value="" />
<div class="container">
	<span class="label">Code</span>
	<input type="text" name="code" class="value input" value="<?=$menu->code?>" />
</div>
<div class="container">
	<span class="label">Title</span>
	<input type="text" name="menu_title" class="value input" value="<?=$menu->title?>" />
</div>
<div class="container">
	<span class="label">Items</span>
</div>
<?php	foreach ($items as $item) { ?>
<table class="body" style="clear: both">
<tr><th>Title</th>
	<th>Target</th>
	<th>Alt</th>
	<th>View Order</th>
	<th>Drop</th>
</tr>
<tr><td><input type="text" name="title[<?=$item->id?>]" class="value input" value="<?=$item->title?>" /></td>
	<td><input type="text" name="target[<?=$item->id?>]" class="value input" style="width: 300px" value="<?=$item->target?>" /></td>
	<td><input type="text" name="alt[<?=$item->id?>]" class="value input" value="<?=$item->alt?>" /></td>
	<td><input type="text" name="view_order[<?=$item->id?>]" class="value input" style="width: 80px; text-align: right" value="<?=$item->view_order?>" /></td>
	<td><input type="button" name="deleteit[<?=$item->id?>]" class="button" value="X" onclick="drop(<?=$item->id?>);" /></td>
</tr>
<tr><th colspan="4">Description</th></tr>
<tr><td colspan="4"><textarea name="description[<?=$item->id?>]" class="value input" style="width: 100%"><?=$item->description?></textarea></td></tr>
</table>
<?php	} ?>
<table class="body" style="clear: both">
<tr><th>Title</th>
	<th>Target</th>
	<th>Alt</th>
	<th>View Order</th>
</tr>
<tr><td><input type="text" name="title[0]" class="value input" value="" /></td>
	<td><input type="text" name="target[0]" class="value input" style="width: 300px" value="" /></td>
	<td><input type="text" name="alt[0]" class="value input" value="" /></td>
	<td><input type="text" name="view_order[0]" class="value input" style="width: 80px; text-align: right" value="" /></td>
</tr>
<tr><th colspan="4">Description</th></tr>
<tr><td colspan="4"><textarea name="description[0]" class="value input" style="width: 100%"></textarea></td></tr>
</table>
<div class="form_footer">
	<input type="submit" name="btn_submit" class="button" value="Submit" />
</div>
</form>
