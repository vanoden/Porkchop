<?=$page->showAdminPageInfo()?>

<form name="menuForm" action="/_navigation/item" method="post">
<input type="hidden" name="csrfToken" value="<?=$GLOBALS['_SESSION_']->getCSRFToken()?>">
<input type="hidden" name="id" value="<?=isset($item) ? $item->id : ''?>" />
<input type="hidden" name="menu_id" value="<?=isset($menu) ? $menu->id : ''?>" />
<input type="hidden" name="parent_id" value="<?=isset($parent) ? $parent->id : 0?>" />

<table class="body clear-both">
<tr><th>Title</th>
	<th>Target</th>
	<th>Alt</th>
	<th>Requires Auth</th>
	<th>Required Role</th>
	<th>View Order</th>
</tr>
<tr><td><input type="text" name="title" class="value input" value="<?=isset($item) ? $item->title : ''?>" /></td>
	<td><input type="text" name="target" class="value input input-width-300" value="<?=isset($item) ? $item->target : ''?>" /></td>
	<td><input type="text" name="alt" class="value input" value="<?=isset($item) ? $item->alt : ''?>" /></td>
	<td><input type="checkbox" name="authentication_required" value="1" <?=isset($item) && $item->authentication_required ? 'checked' : ''?> /></td>
	<td><select name="required_role_id" class="value input">
			<option value="">None</option>
<?php	foreach ($roles as $role) { ?>
			<option value="<?=isset($role) ? $role->id : ''?>"<?php if (isset($role) && isset($item) && $role->id == $item->required_role_id) print " selected";?>><?=isset($role) ? $role->name : ''?></option>
<?php	} ?>
		</select>
	</td>
	<td><input type="text" name="view_order" class="value input input-width-80 input-text-right" value="<?=isset($item) ? $item->view_order : ''?>" /></td>
</tr>
<tr><th colspan="6">Description</th></tr>
<tr><td colspan="6"><textarea name="description" class="value input textarea-width-100"><?=isset($item) ? strip_tags($item->description) : ''?></textarea></td></tr>
<tr><td colspan="6"><input type="submit" class="button" name="btn_delete" value="Delete"/>
					<input type="submit" class="button" name="btn_submit" value="Submit" />
</table>
</form>
