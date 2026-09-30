<script language="Javascript">
	function initImageSelectWizard() {
<?php if (!empty($repository) && !empty($repository->id)) { ?>
		var imageSelectUrl = "/_media/image_select?repository_code=<?= rawurlencode($repository->code) ?>&path=/spectros_product_image";
		var childWindow = open(imageSelectUrl, "imageselect", 'resizable=no,width=500,height=500,location=no,menubar=no,scrollbars=yes,status=no,toolbar=no');
		if (childWindow && childWindow.opener == null) childWindow.opener = self;
<?php } else { ?>
		alert('Repository not found. Configure the website_images repository under Site Configurations.');
<?php } ?>
	}

	function endImageSelectWizard(code) {
		if (!code) return;
		showPendingImage(code);
	}

	function showPendingImage(code) {
		document.getElementById('new_image_code').value = code;
		var preview = document.getElementById('pendingImagePreview');
		var row = document.getElementById('pendingImageRow');
		if (!preview || !row) return;
		preview.src = '/api/media/downloadMediaImage?code=' + encodeURIComponent(code) + '&height=120&width=120';
		row.classList.remove('hidden');
	}

	function clearPendingImage() {
		document.getElementById('new_image_code').value = '';
		var row = document.getElementById('pendingImageRow');
		if (row) row.classList.add('hidden');
	}

	function dropImage(code) {
		document.getElementById('deleteImage').value = code;
		document.getElementById('ItemImageDiv_' + code).style.display = "none";
	}

	function removeTagById(id) {
		document.getElementById('removeTagId').value = id;
		document.getElementById('productEdit').submit();
	}

	function submitProductUpdate() {
		var form = document.getElementById('productEdit');
		if (form && form.requestSubmit) {
			form.requestSubmit(document.getElementById('updateSubmit'));
		} else if (form) {
			form.submit();
		}
	}
</script>

<script language="JavaScript">
	document.addEventListener('DOMContentLoaded', function() {
		var pendingCode = document.getElementById('new_image_code').value;
		if (pendingCode) showPendingImage(pendingCode);

		var form = document.getElementById('productEdit');
		if (!form) return;

		form.addEventListener('keydown', function(e) {
			if (e.key !== 'Enter' || e.target.tagName === 'TEXTAREA') return;
			if (e.target.type === 'button' || e.target.type === 'submit') return;
			if (!document.getElementById('new_image_code').value) return;
			e.preventDefault();
			submitProductUpdate();
		});
	});
</script>

<?= $page->showAdminPageInfo() ?>

<form id="productEdit" name="productEdit" method="post" action="/_product/edit/<?= $item->code ?>">

	<input type="hidden" name="id" id="id" value="<?= $item->id ?>" />
	<input type="hidden" name="deleteImage" id="deleteImage" value="" />
	<input type="hidden" name="csrfToken" value="<?= $GLOBALS['_SESSION_']->getCSRFToken() ?>">
	<input type="hidden" id="removeTagId" name="removeTagId" value="" />
	<input type="hidden" id="removeSearchTagId" name="removeSearchTagId" value="" />

	<h3>Basic Information</h3>
	<section>
		<div class="tableBody min-tablet">
			<div class="tableRowHeader">
				<div class="tableCell width-25per">Product Code</div>
				<div class="tableCell width-25per">Type</div>
				<div class="tableCell width-25per">Status</div>
			</div>
			<div class="tableRow">
				<div class="tableCell">
					<input type="text" name="code" id="code" value="<?= htmlspecialchars($item->code) ?>" class="value input width-100per" />
				</div>
				<div class="tableCell">
					<select name="type" id="type" class="value input width-100per">
						<option value="">Select Type</option>
						<?php foreach ($item_types as $item_type) { ?>
							<option value="<?= $item_type ?>" <?php if ($item_type == $item->type) print " selected"; ?>><?= ucfirst($item_type) ?></option>
						<?php } ?>
					</select>
				</div>
				<div class="tableCell">
					<select name="status" id="status" class="value input width-100per">
						<option value="">Select Status</option>
						<option value="ACTIVE" <?php if ($item->status == 'ACTIVE') print " selected"; ?>>Active</option>
						<option value="HIDDEN" <?php if ($item->status == 'HIDDEN') print " selected"; ?>>Hidden</option>
						<option value="DELETED" <?php if ($item->status == 'DELETED') print " selected"; ?>>Deleted</option>
					</select>
				</div>
			</div>
		</div>
	</section>

	<h3>Product Details</h3>
	<section>
		<div class="tableBody min-tablet">
			<div class="tableRowHeader">
				<div class="tableCell">Product Name</div>
			</div>
			<div class="tableRow">
				<div class="tableCell">
					<input type="text" name="name" id="name" value="<?= htmlspecialchars($item->getMetadata('name')) ?>" class="value input width-100per" />
				</div>
			</div>
			<div class="tableRowHeader">
				<div class="tableCell">Short Description</div>
			</div>
			<div class="tableRow">
				<div class="tableCell">
					<textarea name="short_description" id="short_description" class="value input width-100per" rows="3"><?= htmlspecialchars($item->getMetadata('short_description')) ?></textarea>
				</div>
			</div>
			<div class="tableRowHeader">
				<div class="tableCell">Full Description</div>
			</div>
			<div class="tableRow">
				<div class="tableCell">
					<textarea name="description" id="description" class="value input width-100per" rows="5"><?= htmlspecialchars($item->getMetadata('description')) ?></textarea>
				</div>
			</div>
		</div>
	</section>

	<h3>Technical Specifications</h3>
	<section>
		<div class="tableBody min-tablet">
			<div class="tableRowHeader">
				<div class="tableCell width-25per">Model</div>
				<div class="tableCell width-25per">Empirical Formula</div>
				<div class="tableCell width-25per">Sensitivity</div>
				<div class="tableCell width-25per">Measure Range</div>
			</div>
			<div class="tableRow">
				<div class="tableCell">
					<input type="text" name="model" id="model" value="<?= htmlspecialchars($item->getMetadata('model')) ?>" class="value input width-100per" />
				</div>
				<div class="tableCell">
					<input type="text" name="empirical_formula" id="empirical_formula" value="<?= htmlspecialchars($item->getMetadata('empirical_formula')) ?>" class="value input width-100per" />
				</div>
				<div class="tableCell">
					<input type="text" name="sensitivity" id="sensitivity" value="<?= htmlspecialchars($item->getMetadata('sensitivity')) ?>" class="value input width-100per" />
				</div>
				<div class="tableCell">
					<input type="text" name="measure_range" id="measure_range" value="<?= htmlspecialchars($item->getMetadata('measure_range')) ?>" class="value input width-100per" />
				</div>
			</div>
			<div class="tableRowHeader">
				<div class="tableCell width-25per">Datalogger</div>
				<div class="tableCell width-25per">Accuracy</div>
			</div>
			<div class="tableRow">
				<div class="tableCell">
					<input type="text" name="datalogger" id="datalogger" value="<?= htmlspecialchars($item->getMetadata('datalogger')) ?>" class="value input width-100per" />
				</div>
				<div class="tableCell">
					<input type="text" name="accuracy" id="accuracy" value="<?= htmlspecialchars($item->getMetadata('accuracy')) ?>" class="value input width-100per" />
				</div>
			</div>
		</div>
	</section>

	<h3>Configuration</h3>
	<section>
		<?php
		$default_dashboard_id = $item->getMetadata('default_dashboard_id');
		$selected_manual_id = $item->manual_id ?: $item->getMetadata('manual_id');
		$selected_spec_table_id = $item->spec_table_image ?: $item->getMetadata('spec_table_image');
		?>
		<div class="tableBody min-tablet">
			<div class="tableRowHeader">
				<div class="tableCell width-33per">Default Dashboard</div>
				<div class="tableCell width-33per">Manual</div>
				<div class="tableCell width-33per">Spec Table</div>
			</div>
			<div class="tableRow">
				<div class="tableCell">
					<select name="default_dashboard_id" id="default_dashboard_id" class="value input width-100per">
						<option value="">Select Dashboard</option>
						<?php foreach ($dashboards as $dashboard) { ?>
							<option value="<?= (int)$dashboard->id ?>"<?php if ((string)$default_dashboard_id === (string)$dashboard->id) print " selected"; ?>><?= htmlspecialchars($dashboard->name ?? '') ?></option>
						<?php } ?>
					</select>
				</div>
				<div class="tableCell">
					<select name="manual_id" id="manual_id" class="value input width-100per">
						<option value="">Select Manual</option>
						<?php foreach ($manuals as $manual) {
							$manualLabel = $manual->display_name ?: $manual->name;
						?>
							<option value="<?= (int)$manual->id ?>"<?php if ((string)$selected_manual_id === (string)$manual->id) print " selected"; ?>><?= htmlspecialchars($manualLabel ?? '') ?></option>
						<?php } ?>
					</select>
				</div>
				<div class="tableCell">
					<select name="spec_table_image" id="spec_table_image" class="value input width-100per">
						<option value="">Select Spec Table</option>
						<?php foreach ($tables as $table) {
							$tableLabel = $table->display_name ?: $table->name;
						?>
							<option value="<?= (int)$table->id ?>"<?php if ((string)$selected_spec_table_id === (string)$table->id) print " selected"; ?>><?= htmlspecialchars($tableLabel ?? '') ?></option>
						<?php } ?>
					</select>
				</div>
			</div>
		</div>
	</section>

	<h3>Product Images</h3>
	<p class="product-admin-images__hint">Use <strong>Choose Image from Library</strong> to pick an image. It will appear below as <strong>Pending</strong> until you click <strong>Update Product</strong> at the bottom of the page.</p>
	<section>
		<div class="tableBody min-tablet">
			<div class="tableRowHeader">
				<div class="tableCell width-20per">Preview</div>
				<div class="tableCell width-25per">Status</div>
				<div class="tableCell">Actions</div>
			</div>
			<?php foreach ($images as $image) { ?>
			<div class="tableRow" id="ItemImageDiv_<?= $image->code ?>">
				<div class="tableCell">
					<img src="/api/media/downloadMediaImage?code=<?= rawurlencode($image->code) ?>&height=120&width=120" alt="Product Image" class="register-image-preview" />
				</div>
				<div class="tableCell">
					<span class="value">Attached</span>
				</div>
				<div class="tableCell">
					<input type="button" class="button btn-secondary" onclick="dropImage('<?= htmlspecialchars($image->code, ENT_QUOTES) ?>')" value="Remove" />
				</div>
			</div>
			<?php } ?>
			<div class="tableRow hidden" id="pendingImageRow">
				<div class="tableCell">
					<img id="pendingImagePreview" src="" alt="Pending product image" class="register-image-preview" />
				</div>
				<div class="tableCell">
					<span class="product-admin-images__badge">Pending</span>
					<div class="value">Not saved yet — click <strong>Update Product</strong> to apply.</div>
				</div>
				<div class="tableCell">
					<input type="button" class="button btn-secondary" onclick="clearPendingImage()" value="Cancel" />
				</div>
			</div>
			<div class="tableRow" id="addImageRow">
				<div class="tableCell">
					<span class="value">—</span>
				</div>
				<div class="tableCell">
					<span class="value">Add image</span>
				</div>
				<div class="tableCell">
					<input type="hidden" name="new_image_code" id="new_image_code" value="<?= htmlspecialchars($pendingImageCode ?? '', ENT_QUOTES) ?>" />
					<input type="button" class="button" name="addImageButton" value="Choose Image from Library" onclick="initImageSelectWizard()" />
				</div>
			</div>
		</div>
	</section>

	<h3>Pricing</h3>
	<section>
		<h4>Add New Price</h4>
		<div class="tableBody min-tablet">
			<div class="tableRowHeader">
				<div class="tableCell width-33per">Date Active</div>
				<div class="tableCell width-33per">Status</div>
				<div class="tableCell width-33per">Amount</div>
			</div>
			<div class="tableRow">
				<div class="tableCell">
					<input type="text" name="new_price_date" id="new_price_date" value="now" class="value input width-100per" />
				</div>
				<div class="tableCell">
					<select name="new_price_status" id="new_price_status" class="value input width-100per">
						<option value="ACTIVE">Active</option>
						<option value="INACTIVE">Inactive</option>
					</select>
				</div>
				<div class="tableCell">
					<input type="text" name="new_price_amount" id="new_price_amount" value="0.00" class="value input width-100per" />
				</div>
			</div>
		</div>

		<h4>Price History</h4>
		<div class="tableBody min-tablet">
			<div class="tableRowHeader">
				<div class="tableCell width-33per">Date Active</div>
				<div class="tableCell width-33per">Status</div>
				<div class="tableCell width-33per">Amount</div>
			</div>
			<?php foreach ($prices as $price) { ?>
			<div class="tableRow">
				<div class="tableCell"><?= htmlspecialchars($price->date_active) ?></div>
				<div class="tableCell"><?= htmlspecialchars($price->status) ?></div>
				<div class="tableCell">$<?= number_format($price->amount, 2) ?></div>
			</div>
			<?php } ?>
		</div>
	</section>

	<h3>Product Tags</h3>
	<section>
		<div class="tableBody min-tablet">
			<div class="tableRowHeader">
				<div class="tableCell width-33per">&nbsp;</div>
				<div class="tableCell">Tag</div>
			</div>
			<?php if (!empty($productTags)) {
				foreach ($productTags as $tag) { ?>
			<div class="tableRow">
				<div class="tableCell">
					<input type="button" onclick="removeTagById('<?= $tag->id ?>')" name="removeTag" value="Remove" class="button" />
				</div>
				<div class="tableCell"><strong><?= htmlspecialchars($tag->name) ?></strong></div>
			</div>
			<?php }
			} else { ?>
			<div class="tableRow">
				<div class="tableCell">No tags assigned to this product.</div>
			</div>
			<?php } ?>
			<div class="tableRow">
				<div class="tableCell">
					<label for="newTag">New Tag:</label>
					<input type="text" name="newTag" id="newTag" class="value input" placeholder="Enter tag name" />
				</div>
				<div class="tableCell">
					<input type="submit" name="addTag" value="Add Tag" class="button" />
				</div>
			</div>
		</div>
	</section>

<?php
	$searchTagsTitle = 'Product Search Tags';
	$searchTagRows = $productSearchTags ?? [];
	$searchTagsFormId = 'productEdit';
	$searchTagsCategoryPlaceholder = 'e.g., gas';
	$searchTagsValuePlaceholder = 'e.g., sulfuryl fluoride';
	$searchTagsSubmitInForm = true;
	require BASE . '/modules/site/default/search_tags_editor.php';
?>

	<h3>Price Audit History</h3>
	<section>
		<div class="tableBody min-tablet">
			<div class="tableRowHeader">
				<div class="tableCell width-25per">User</div>
				<div class="tableCell width-25per">Date</div>
				<div class="tableCell width-50per">Note</div>
			</div>
			<?php if (!empty($auditedPrices)) {
				foreach ($auditedPrices as $priceAudit) {
					$customer = new Register\Customer($priceAudit->user_id);
			?>
			<div class="tableRow">
				<div class="tableCell"><?= htmlspecialchars(trim($customer->first_name . ' ' . $customer->last_name)) ?></div>
				<div class="tableCell"><?= htmlspecialchars($priceAudit->date_updated) ?></div>
				<div class="tableCell"><?= htmlspecialchars(stripslashes($priceAudit->note)) ?></div>
			</div>
			<?php }
			} else { ?>
			<div class="tableRow">
				<div class="tableCell">No price audit history available.</div>
			</div>
			<?php } ?>
		</div>
	</section>

	<div class="form-actions filter-bar">
		<div class="button-group filter-bar__actions">
			<button type="submit" name="updateSubmit" id="updateSubmit" class="button" value="Update">Update Product</button>
		</div>
	</div>
</form>
