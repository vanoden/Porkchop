<script language="Javascript">
	function goTo(target) {
		window.location.href = target;
		return true;
	}
</script>
<?=$page->showAdminPageInfo()?>

<?php	foreach ($menus as $menu) { ?>
<form name="menuForm" action="/_navigation/menus" method="post">
<input type="hidden" name="id" value="<?=$menu->id?>" />
<input type="hidden" name="csrfToken" value="<?=$GLOBALS['_SESSION_']->getCSRFToken()?>">
<div class="container">
	<div class="container">
		<span class="label">Code</span>
		<input type="text" name="code" value="<?=$menu->code?>" />
	</div>
	<div class="container">
		<span class="label">Title</span>
		<input type="text" name="title" value="<?=$menu->title?>" />
	</div>
	<div class="container">
		<span class="label">Required Authentication</span>
		<input type="checkbox" name="authentication_required" value="1" <?=($menu->authentication_required ? 'checked' : '')?> />
	</div>
	<div class="form_footer">
		<input type="submit" name="btn_submit" value="Update" class="button" />
		<input type="button" name="btn_menu" value="Items" class="button" onclick="goTo('/_navigation/items/<?=$menu->code?>')" />
	</div>
</div>
</form>
<?php  } ?>
<form name="menuForm" action="/_navigation/menus" method="post">
<input type="hidden" name="id" value="0" />
<input type="hidden" name="csrfToken" value="<?=$GLOBALS['_SESSION_']->getCSRFToken()?>">
<div class="container">
	<div class="container">
		<span class="label">Code</span>
		<input type="text" name="code" value="" />
	</div>
	<div class="container">
		<span class="label">Title</span>
		<input type="text" name="title" value="" />
	</div>
	<div class="container">
		<span class="label">Show Close Button</span>
		<input type="checkbox" name="show_close_button" value="1" />
	</div>
	<div class="form_footer">
		<input type="submit" name="btn_submit" value="Add" class="button" />
	</div>
</div>
</form>
