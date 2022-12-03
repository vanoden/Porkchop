<script language="Javascript">
	function goTo(target) {
		window.location.href = target;
		return true;
	}
</script>
<?php	foreach ($menus as $menu) { ?>
<form name="menuForm" action="/_navigation/menus" method="post">
<input type="hidden" name="id" value="<?=$menu->id?>" />
<div class="container">
	<div class="container">
		<span class="label">Code</span>
		<input type="text" name="code" value="<?=$menu->code?>" />
	</div>
	<div class="container">
		<span class="label">Title</span>
		<input type="text" name="title" value="<?=$menu->title?>" />
	</div>
	<div class="form_footer">
		<input type="submit" name="btn_submit" value="Update" class="button" />
		<input type="button" name="btn_menu" value="Items" class="button" onclick="goTo('/_navigation/items/<?=$menu->code?>')" />
	</div>
</div>
</form>
<?php  } ?>
