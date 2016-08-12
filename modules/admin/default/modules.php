<table class="body">
<tr><td class="title" colspan="2">Modules</td></tr>
<?	if ($GLOBALS['_page']->error) { ?>
<tr><td class="error" colspan="2"><?=$GLOBALS['_page']->error?></td></tr>
<?	} ?>
<tr><td class="label">Title</td>
	<td class="label">Description</td>
</tr>
<?	foreach ($modules as $module) { ?>
<tr><td class="value"><?=$module->name?></td>
	<td class="value"><?=$module->description?></td>
</tr>
<?	} ?>
</table>
