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
<h1>Edit Site Content Block</h1>

<?php if ($page->errorCount() > 0) { ?>
<section id="form-message">
	<ul class="connectBorder errorText">
		<li><?=$page->errorString()?></li>
	</ul>
</section>

<?php	} else if ($page->success) { ?>
<section id="form-message">
	<ul class="connectBorder progressText">
		<li><?=$page->success?></li>
	</ul>
</section>
<?php	} ?>

<section class="table-group">
  <form method="post" action="/_site/content_block">
    <input type="hidden" name="id" value="<?=$message->id?>"/>
    <input type="hidden" name="csrfToken" value="<?=$GLOBALS['_SESSION_']->getCSRFToken()?>">
    <ul class="form-grid three-col">
			<li><label for="name">Name</label><span class="value"><input class="value" type="text" name="name" id="name" value="<?=$message->name?>"/></span></li>
			<li><label for="name">Target</label><span class="value"><input class="value input lefty" type="text" name="target" id="target" value="<?=$message->target?>"/></span></li>
		</ul>
    <div class="columns">
    <?php	if ($show_add_page) { ?>
      <span>Add As A Page? </span><input type="checkbox" name="addPage" value="1" />
    <?php	} else { ?>
      <a class="button" href="/<?=$message->target?>">Go To Page</a>
      <a class="button" href="/_site/page?module=content&view=index&index=<?=$message->target?>">Edit Page Metadata</a>
    <?php	} ?>
    </div>
    <div id="contentArea">
      <textarea id="content" name="content"><?=$message->content?></textarea>
      <input type="submit" name="Submit" value="Submit"/>
    </div>
  </form>
</section>
