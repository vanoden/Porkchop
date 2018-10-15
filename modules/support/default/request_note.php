<?	if ($page->errorCount()) { ?>
<div class="form_error"><?=$page->errorString()?></div>
<?	} ?>
<form name="noteForm" method="post">
<input type="hidden" name="request_id" value="<?=$request->id?>" />
<h1>Request Note for <?=$request->code?></h1>
<div class="label">Note</div>
<textarea name="note"></textarea>
<input type="submit" name="btn_submit" class="button" value="Add Note" />
</form>