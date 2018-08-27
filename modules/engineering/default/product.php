<div style="width: 756px;">
<form name="product_form" action="/_engineering/product" method="post">
<input type="hidden" name="product_id" value="<?=$product->id?>" />
<div class="title">Engineering Release</div>
<?	if ($page->error) { ?>
<div class="form_error"><?=$page->error?></div>
<?	}
	if ($page->success) { ?>
<div class="form_success"><?=$page->success?></div>
<?	} ?>
<div class="container_narrow">
	<div class="label">Code</div>
	<input type="text" name="code" class="value input" value="<?=$form['code']?>" />
</div>
<div class="container_narrow">
	<div class="label">Title</div>
	<input type="text" name="title" class="value input" style="width: 240px" value="<?=$form['title']?>" />
</div>
<div class="container">
	<div class="label">Description</div>
	<textarea name="description" style="width: 700px; height: 300px;"><?=$form['description']?></textarea>
</div>
<div class="container">
	<input type="submit" name="btn_submit" class="button" value="Submit">
</div>
</form>
</div>