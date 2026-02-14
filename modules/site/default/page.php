<script language="Javascript">
	function updateMeta(idx) {
		document.forms[0].key.value = document.getElementById('key_'+idx).value;
		document.forms[0].value.value = document.getElementById('value_'+idx).value;
		document.forms[0].todo.value = 'update';
		document.forms[0].submit();
	}
	function dropMeta(idx) {
		document.forms[0].key.value = document.getElementById('key_'+idx).value;
		document.forms[0].todo.value = 'drop';
		document.forms[0].submit();
	}
	function addMeta() {
		document.forms[0].key.value = document.getElementById('key_').value;
		document.forms[0].value.value = document.getElementById('value_').value;
		document.forms[0].todo.value = 'add';
		document.forms[0].submit();
	}
</script>

<?= $page->showAdminPageInfo() ?>

<div class="site-page-edit-layout">
	<section class="site-page-edit-section">
		<h3 class="site-page-edit-section-title">Page</h3>
		<div class="site-page-edit-info">
			<div class="site-page-edit-info-item">
				<span class="site-page-edit-label">Module</span>
				<span class="site-page-edit-value"><?= htmlspecialchars($module ?? '') ?></span>
			</div>
			<div class="site-page-edit-info-item">
				<span class="site-page-edit-label">View</span>
				<span class="site-page-edit-value"><?= htmlspecialchars($view ?? '') ?></span>
			</div>
			<div class="site-page-edit-info-item">
				<span class="site-page-edit-label">Index</span>
				<span class="site-page-edit-value"><?= htmlspecialchars($index ?? '') ?></span>
			</div>
		</div>
	</section>

	<form method="post" action="/_site/page" class="site-page-edit-form">
		<input type="hidden" name="csrfToken" value="<?= $GLOBALS['_SESSION_']->getCSRFToken() ?>" />
		<input type="hidden" name="module" value="<?= htmlspecialchars($module ?? '') ?>" />
		<input type="hidden" name="view" value="<?= htmlspecialchars($view ?? '') ?>" />
		<input type="hidden" name="index" value="<?= htmlspecialchars($index ?? '') ?>" />
		<input type="hidden" name="key" value="" />
		<input type="hidden" name="value" value="" />
		<input type="hidden" name="todo" value="" />

		<section class="site-page-edit-section">
			<h3 class="site-page-edit-section-title">Metadata</h3>
			<div class="tableBody bandedRows site-page-edit-metadata-table">
				<div class="tableRowHeader">
					<div class="tableCell">Key</div>
					<div class="tableCell">Value</div>
					<div class="tableCell site-page-edit-actions-cell">Actions</div>
				</div>
				<?php $idx = 0; foreach ($metadata as $key => $value) { ?>
				<div class="tableRow">
					<div class="tableCell">
						<?= htmlspecialchars($key) ?>
						<input id="key_<?= $idx ?>" type="hidden" name="key_<?= $idx ?>" value="<?= htmlspecialchars($key) ?>" />
					</div>
					<div class="tableCell">
						<input id="value_<?= $idx ?>" type="text" name="value_<?= $idx ?>" class="site-page-edit-input" value="<?= htmlspecialchars($value) ?>" />
					</div>
					<div class="tableCell site-page-edit-actions-cell">
						<button type="button" name="update_<?= $idx ?>" class="button" onclick="updateMeta('<?= $idx ?>');">Update</button>
						<button type="button" name="drop_<?= $idx ?>" class="button" onclick="dropMeta('<?= $idx ?>');">Drop</button>
					</div>
				</div>
				<?php $idx++; } ?>
				<div class="tableRow">
					<div class="tableCell">
						<input type="text" id="key_" name="_key" class="site-page-edit-input" placeholder="New key" />
					</div>
					<div class="tableCell">
						<input type="text" id="value_" name="_value" class="site-page-edit-input" placeholder="New value" />
					</div>
					<div class="tableCell site-page-edit-actions-cell">
						<button type="button" name="add" class="button" onclick="addMeta();">Add</button>
					</div>
				</div>
			</div>
		</section>
	</form>
</div>
