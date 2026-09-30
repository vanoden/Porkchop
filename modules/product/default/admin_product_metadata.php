<link rel="stylesheet" type="text/css" href="/html.src/css/admin.css">
<?= $page->showAdminPageInfo() ?>

<?php $activeTab = 'metadata'; ?>
<?php require __DIR__ . '/admin_product_identity.php'; ?>
<?php require __DIR__ . '/admin_product_tabs.php'; ?>

<form id="metadataForm" name="metadataForm" method="post" action="/_product/admin_product_metadata/<?= htmlspecialchars($item->code) ?>">
	<input type="hidden" name="csrfToken" value="<?= $GLOBALS['_SESSION_']->getCSRFToken() ?>">

	<h3>Product Metadata</h3>
	<p>Manage arbitrary key/value metadata for this product. Spectros-specific fields (default dashboard, manuals, etc.) are edited under <a href="/_spectros/admin_product_metadata/<?= htmlspecialchars($item->code) ?>">Spectros Product Metadata</a>.</p>

	<div class="metadata-section">
		<h4>Existing Metadata</h4>
		<?php if (empty($metadataKeys)) { ?>
			<p>No metadata keys defined yet.</p>
		<?php } else {
			foreach ($metadataKeys as $key) {
				$label = ucwords(str_replace("_", " ", $key));
		?>
			<div class="input-horiz" id="item<?= htmlspecialchars($key) ?>">
				<span class="label"><?= htmlspecialchars($label) ?></span>
				<input type="text" class="value input width-300px" name="<?= htmlspecialchars($key) ?>" id="<?= htmlspecialchars($key) ?>" value="<?= htmlspecialchars($item->getMetadata($key)) ?>" />
				<button type="button" class="button delete-metadata-btn" data-key="<?= htmlspecialchars($key) ?>" title="Delete this metadata field">&times;</button>
			</div>
		<?php }
		} ?>
	</div>

	<div class="metadata-section new-metadata-section">
		<h4>Add New Metadata</h4>
		<p>Add a new key/value pair to this product's metadata.</p>
		<div class="input-horiz">
			<span class="label">Key</span>
			<input type="text" class="value input width-300px" name="new_metadata_key" id="new_metadata_key" placeholder="e.g., technical_specs, features, etc." />
		</div>
		<div class="input-horiz">
			<span class="label">Value</span>
			<input type="text" class="value input width-500px" name="new_metadata_value" id="new_metadata_value" placeholder="Enter the value for this metadata key" />
		</div>
	</div>

	<div class="form-actions filter-bar">
		<div class="button-group filter-bar__actions">
			<button type="submit" class="button" name="updateMetadata" id="updateMetadata" value="Update Metadata">Update Metadata</button>
		</div>
	</div>
</form>

<form id="deleteMetadataForm" method="post" action="/_product/admin_product_metadata/<?= htmlspecialchars($item->code) ?>" style="display: none;">
	<input type="hidden" name="csrfToken" value="<?= $GLOBALS['_SESSION_']->getCSRFToken() ?>">
	<input type="hidden" name="deleteMetadata" value="1">
	<input type="hidden" name="delete_metadata_key" id="delete_metadata_key" value="">
</form>

<script>
document.addEventListener('DOMContentLoaded', function() {
	document.querySelectorAll('.delete-metadata-btn').forEach(function(button) {
		button.addEventListener('click', function(e) {
			e.preventDefault();
			var key = this.getAttribute('data-key');
			var label = this.closest('.input-horiz').querySelector('.label').textContent;
			if (confirm('Delete metadata field "' + label + '" (' + key + ')? This cannot be undone.')) {
				document.getElementById('delete_metadata_key').value = key;
				document.getElementById('deleteMetadataForm').submit();
			}
		});
	});
});
</script>
