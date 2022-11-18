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
	function childLink(id) {
		document.forms[0].parent_id.value = id;
		document.forms[0].submit();
	}
</script>
<div class="title"><?=$menu->title?></div>
<?php	if ($page->errorCount()) { ?>
<div class="form_error"><?=$page->errorString()?></div>
<?php	} ?>
<form name="menuForm" action="/_navigation/items" method="post">
<input type="hidden" name="csrfToken" value="<?=$GLOBALS['_SESSION_']->getCSRFToken()?>">
<input type="hidden" name="id" value="<?=$menu->id?>" />
<input type="hidden" name="parent_id" value="<?=$parent->id?>" />
<input type="hidden" name="delete" value="" />

<table class="body" style="clear: both">
<tr><th>Title</th>
	<th>Target</th>
	<th>Alt</th>
	<th>View Order</th>
	<th>See Children</th>
	<th>Drop</th>
</tr>
<?php	foreach ($items as $item) { ?>
<tr><td><a href="/_navigation/item?menu_id=<?=$menu->id?>&parent_id=<?=$parent->id?>&id=<?=$item->id?>"><?=$item->title?></a></td>
	<td><a href="<?=$item->target?>" class="value"><?=$item->target?></a></td>
	<td><?=$item->alt?></td>
	<td><?=$item->view_order?></td>
	<td><input type="button" name="children[<?=$item->id?>]" class="button" value="Go" onclick="childLink(<?=$item->id?>);" /></td>
	<td><input type="button" name="deleteit[<?=$item->id?>]" class="button" value="X" onclick="drop(<?=$item->id?>);" /></td>
</tr>
<?php	} ?>
</table>
</form>
