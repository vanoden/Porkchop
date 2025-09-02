<?= $page->showBreadCrumbs() ?>
<?= $page->showTitle() ?>
<?= $page->showMessages() ?>

<script src="https://cdn.tiny.cloud/1/owxjg74mr7ujxhw9soo7iquo7iul2mclregqovcp7ophazmn/tinymce/5/tinymce.min.js" referrerpolicy="origin"></script>
<?php if (isset($version) && $version->status == 'NEW') { ?>
	<script>
		tinymce.init({
			selector: '#content',
			plugins: 'code',
			toolbar: 'undo redo | styleselect | bold italic | alignleft aligncenter alignright alignjustify | outdent indent | code',
			setup: function(editor) {
				editor.on('change', function() {
					editor.save();
				});
			}
		});
	</script>
<?php	} else { ?>
	<script>
		tinymce.init({
			selector: '#content',
			plugins: 'code',
			toolbar: 'undo redo | styleselect | bold italic | alignleft aligncenter alignright alignjustify | outdent indent | code',
			readonly: 1
		});
	</script>
<?php	} ?>

<section class="table-group">
	<form method="post" action="/_site/tou_version">
		<input type="hidden" name="version_id" value="<?= isset($version_id) ? $version_id : '' ?>" />
		<input type="hidden" name="tou_id" value="<?= isset($tou_id) ? $tou_id : '' ?>" />
		<input type="hidden" name="csrfToken" value="<?= $GLOBALS['_SESSION_']->getCSRFToken() ?>">
		<ul class="form-grid three-col">
			<li><label for="name">Terms of Use Record</label><span class="value"><a href="/_site/terms_of_use?id=<?= isset($tou) ? $tou->id : '' ?>"><?= isset($tou) ? $tou->name : '' ?></a></span></li>
			<li><label for="name">Version Number</label><span class="value"><?= isset($version) ? $version->date_created() : '' ?></span></li>
			<li><label for="name">Version Status</label><span class="value"><?= isset($version) ? $version->status : '' ?></span></li>
		</ul>
		<div id="contentArea" class="site-tou-version-content-area">
			<textarea id="content" name="content"><?= isset($version) && isset($version->content) ? stripslashes($version->content) : '' ?></textarea>
			<?php if (isset($version) && $version->status == 'NEW') { ?>
				<input type="submit" name="btn_submit" value="Submit" />
			<?php	} ?>
		</div>
	</form>
</section>
