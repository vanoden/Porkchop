<?
	$page = new \Site\Page();

	$page->addError('SQL Error in Test Page');

	if ($page->errorCount() > 0) {
?>
<div class="form_error"><?=$page->errorString()?></div>
<?	} ?>
This is just a test page.  You should see an error, but no SQL above
