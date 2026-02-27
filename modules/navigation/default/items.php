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
	function edit(item_id,menu_id,parent_id) {
		window.location.href = "/_navigation/item?menu_id="+menu_id+"&parent_id="+parent_id+"&id="+item_id;
	}
	function follow(target) {
		window.location.href = target;
	}
	function addItem(parent) {
	window.location.href = "/_navigation/item?menu_id=<?=isset($menu) ? $menu->id : ''?>&parent_id="+parent;
}
</script>
<?=$page->showAdminPageInfo()?>

<form name="menuForm" action="/_navigation/items" method="post">
<input type="hidden" name="csrfToken" value="<?=$GLOBALS['_SESSION_']->getCSRFToken()?>">
<input type="hidden" name="id" value="<?=isset($menu) ? $menu->id : ''?>" />
<input type="hidden" name="parent_id" value="<?=isset($parent) ? $parent->id : 0?>" />
<input type="hidden" name="delete" value="" />
<input type="button" name="add" value="Add Item" onclick="addItem(<?=isset($parent) ? $parent->id : 0?>);" />

<table class="body clear-both">
<tr><th>Title</th>
	<th>Target</th>
	<th>Alt</th>
	<th>Required Role</th>
	<th>Required Product</th>
	<th>View Order</th>
	<th>Actions</th>
</tr>
<?php	foreach ($items as $item) { ?>
<tr><td><?=$item->title?></td>
	<td><?=$item->target?></td>
	<td><?=$item->alt?></td>
	<td><?=$item->required_role() ? $item->required_role()->name : ''?></td>
	<td><?=$item->required_product() ? $item->required_product()->code : ''?></td>
	<td><?=$item->view_order?></td>
	<td>
		<input type="button" name="details[<?=$item->id?>]" class="button" value="Edit" onclick="edit(<?=$item->id?>,<?=isset($menu) ? $menu->id : ''?>,<?=isset($parent) ? $parent->id : 0?>);" />
		<input type="button" name="follow[<?=$item->id?>]" class="button" value="Follow" onclick="follow('<?=$item->target?>');"<?php if (empty($item->target)) print " disabled";?> />
		<input type="button" name="children[<?=$item->id?>]" class="button" value="Children" onclick="childLink(<?=$item->id?>);" />
		<input type="button" name="deleteit[<?=$item->id?>]" class="button" value="Drop" onclick="drop(<?=$item->id?>);" />
	</td>
</tr>
<?php	} ?>
</table>
</form>
