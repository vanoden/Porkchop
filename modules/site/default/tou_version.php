<?=$page->showBreadCrumbs()?>
<?=$page->showTitle()?>
<?=$page->showMessages()?>
<style>
	#contentArea {
		display: block;
		clear: both;
	}
</style>
<script src="https://cdn.tiny.cloud/1/owxjg74mr7ujxhw9soo7iquo7iul2mclregqovcp7ophazmn/tinymce/5/tinymce.min.js" referrerpolicy="origin"></script>
<script>
	tinymce.init({
		selector: '#content',
        plugins: 'code',
        toolbar: 'undo redo | styleselect | bold italic | alignleft aligncenter alignright alignjustify | outdent indent | code'
	});
</script>

<section class="table-group">
  <form method="post" action="/_site/tou_version">
    <input type="hidden" name="id" value="<?=$message->id?>"/>
	<input type="hidden" name="tou_id" value="<?=$tou->id?>"/>
    <input type="hidden" name="csrfToken" value="<?=$GLOBALS['_SESSION_']->getCSRFToken()?>">
    <ul class="form-grid three-col">
			<li><label for="name">Terms of Use Record</label><span class="value"><?=$tou->name?></span></li>
			<li><label for="name">Version Number</label><span class="value"><?=$version_number?></span></li>
			<li><label for="name">Version Status</label><span class="value"><?=$version->status?></span></li>
		</ul>
    <div id="contentArea">
      <textarea id="content" name="content"><?=$version->content?></textarea>
      <input type="submit" name="btn_submit" value="Submit"/>
    </div>
  </form>
</section>
