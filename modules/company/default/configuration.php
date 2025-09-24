<?=$page->showAdminPageInfo()?>

<form method="post">
<input type="hidden" name="id" value="<?=$company->id?>" />
<p>	<span>Name</span>
	<input name="name" value="<?=$company->name?>" />
</p>
<input type="submit" name="btn_submit" value="Update" />
</form>
