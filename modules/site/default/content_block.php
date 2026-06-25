<?php /* TinyMCE: If you see "invalid-origin" or read-only editors, add this site's domain (e.g. https://yoursite.com) in Tiny Cloud dashboard under Domain registration. Use HTTPS in production. */ ?>
<script src="https://cdn.tiny.cloud/1/owxjg74mr7ujxhw9soo7iquo7iul2mclregqovcp7ophazmn/tinymce/5/tinymce.min.js" referrerpolicy="origin"></script>
<script>
	tinymce.init({
		selector: '#content',
        plugins: 'code',
        toolbar: 'undo redo | styleselect | bold italic | alignleft aligncenter alignright alignjustify | outdent indent | code'
	});
</script>

<!-- Page Header -->
<?= $page->showAdminPageInfo() ?>
<!-- End Page Header -->

<form id="contentBlockEdit" name="contentBlockEdit" method="post" action="/_site/content_block/<?= $message->target ?>">

	<input type="hidden" name="id" id="id" value="<?= $message->id ?>" />
	<input type="hidden" name="csrfToken" value="<?= $GLOBALS['_SESSION_']->getCSRFToken() ?>">
	<input type="hidden" id="removeSearchTagId" name="removeSearchTagId" value="" />

	<div class="body">
		<div class="input-horiz" id="itemName">
			<span class="label">Name</span>
			<input type="text" class="value input width-250px" name="name" id="name" value="<?= htmlspecialchars($message->name) ?>" />
		</div>
		<div class="input-horiz" id="itemTarget">
			<span class="label">Target</span>
			<input type="text" class="value input width-250px" name="target" id="target" value="<?= htmlspecialchars($message->target) ?>" />
		</div>
		<div class="input-horiz" id="itemContent">
			<span class="label align-top">Content</span>
			<textarea class="value input width-250px" name="content" id="content"><?= htmlspecialchars($message->content) ?></textarea>
		</div>

<?php
		$searchTagsTitle = 'Content Message Search Tags';
		$searchTagRows = $registerCustomerSearchTags ?? [];
		$searchTagsFormId = 'contentBlockEdit';
		$searchTagsCategoryPlaceholder = 'content';
		$searchTagsValuePlaceholder = 'about us';
		$searchTagsSubmitInForm = true;
		require __DIR__ . '/search_tags_editor.php';
?>

		<div class="form-actions filter-bar">
			<div class="button-group filter-bar__actions">
				<button type="submit" class="button" name="updateSubmit" id="updateSubmit" value="Update">Update</button>
			</div>
		</div>
	</div>
</form>
