<?= $page->showAdminPageInfo() ?>

<div class="site-header-edit-layout">
<form method="post" action="/_site/header" class="site-header-edit-form">
	<input type="hidden" name="csrfToken" value="<?= $GLOBALS['_SESSION_']->getCSRFToken() ?>" />
	<input type="hidden" name="id" value="<?= (int)($header->id ?? 0) ?>" />

	<section class="site-header-edit-section">
		<h3 class="site-header-edit-section-title">Header</h3>
		<div class="site-header-edit-grid">
			<div class="site-header-edit-field">
				<label for="header-name" class="site-header-edit-label">HTTP header name</label>
				<input type="text" id="header-name" name="name" class="site-header-edit-input" value="<?= htmlspecialchars($header->name() ?? '') ?>" required />
			</div>
			<div class="site-header-edit-field site-header-edit-field-full">
				<label for="header-value" class="site-header-edit-label">Contents</label>
				<textarea id="header-value" name="value" class="site-header-edit-input site-header-edit-textarea" rows="6" required><?= htmlspecialchars($header->value() ?? '') ?></textarea>
			</div>
		</div>
	</section>

	<div class="site-header-edit-actions">
		<button type="submit" name="btn_submit" class="button">Submit</button>
		<button type="button" class="button" onclick="window.location.href='/_site/headers';">Back to headers</button>
	</div>
</form>
</div>
