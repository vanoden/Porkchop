

<div class="title">Edit Media Item</div>
<?php	if ($GLOBALS['_page']->error) { ?>
<div class="form_error"><?=$GLOBALS['_page']->error?></div>
<?php	} ?>
<form name="mediaEdit" method="post" action="/_media/edit">
<input type="hidden" name="code" id="code" value="<?=$item->code?>" />
<div class="body">
	<div class="editItem" id="itemCode">
		<span class="label">Code</span>
		<span class="value"><?=$item->code?></span>
	</div>
	<div class="editItem" id="itemType">
		<span class="label">Type</span>
		<span class="value"><?=$item->type?></span>
	</div>
	<div class="editItem" id="itemName">
		<span class="label">Name</span>
		<input type="text" class="value input" name="name" id="name" value="<?=$item->name?>" />
	</div>
	<div class="editItem" id="itemIcon">
		<span class="label">Icon</span>
		<input type="text" class="value input" name="icon" id="icon" value="<?=$item->icon?>" />
	</div>
	<div class="editItem" id="itemDescription">
		<span class="label">Description</span>
		<textarea class="value input textarea-height-75 textarea-width-300" name="description" id="description"><?=strip_tags($item->description)?></textarea>
	</div>
	<div class="editSubmit">
		<input type="submit" class="button" value="Submit" name="submit" id="submit"/>
	</div>
</div>
</form>
