<?=$page->showAdminPageInfo();?>

<?=$latest_version->title?>
<?=$latest_version->content?>
<p>To view the requested contact, you must accept the above Terms of Use.</p>
<form method="post">
<input type="hidden" name="module" value="<?=$target_page->module()?>" />
<input type="hidden" name="view" value="<?=$target_page->view()?>" />
<input type="hidden" name="index" value="<?=$target_page->index()?>" />
<input type="hidden" name="tou_id" value="<?=$tou->id?>" />
<input type="hidden" name="version_id" value="<?=$latest_version->id?>" />
<input type="submit" name="btn_submit" value="Accept" />
<input type="submit" name="btn_submit" value="Decline" />
</form>