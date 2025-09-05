<script language="Javascript">
	function initImageSelectWizard() {
		childWindow = open("http://<?= $_SERVER['HTTP_HOST'] ?>/_media/image_select", "imageselect", 'resizable=no,width=500,height=500');
		if (childWindow.opener == null) childWindow.opener = self;
	}

	function endImageSelectWizard(code) {
		document.getElementById('new_image_code').value = code;
		document.getElementById('newImageBox').style.backgroundImage = '/_media/api?method=downloadImageFile&code=' + code;
	}

	function dropImage(code) {
		document.getElementById('deleteImage').value = code;
		document.getElementById('ItemImageDiv_' + code).style.display = "none";
	}

	// remove an organization tag by id
	function removeTagById(id) {
		document.getElementById('removeTagId').value = id;
		document.getElementById('productEdit').submit();
	}

	function removeSearchTagById(id) {
		document.getElementById('removeSearchTagId').value = id;
		document.getElementById('productEdit').submit();
	}
</script>

<!-- Autocomplete CSS and JS -->
<link href="/css/autocomplete.css" type="text/css" rel="stylesheet">
<script language="JavaScript" src="/js/autocomplete.js"></script>
<script language="JavaScript">
	// define existing categories and tags for autocomplete
	var existingCategories = <?= $uniqueTagsData['categoriesJson'] ?>;
	var existingTags = <?= $uniqueTagsData['tagsJson'] ?>;
</script>

<!-- Page Header -->
<?= $page->showAdminPageInfo() ?>
<!-- End Page Header -->

<form id="productEdit" name="productEdit" method="post" action="/_product/edit/<?= $item->code ?>">

	<input type="hidden" name="id" id="id" value="<?= $item->id ?>" />
	<input type="hidden" name="deleteImage" id="deleteImage" value="" />
	<input type="hidden" name="csrfToken" value="<?= $GLOBALS['_SESSION_']->getCSRFToken() ?>">
	<input type="hidden" id="removeTagId" name="removeTagId" value="" />
	<input type="hidden" id="removeSearchTagId" name="removeSearchTagId" value="" />

	<div class="product-edit-container">
		<!-- Basic Information Section -->
		<div class="form-section">
			<h3 class="section-title">Basic Information</h3>
			<div class="form-grid">
				<div class="form-group">
					<label for="code" class="form-label">Product Code</label>
					<input type="text" name="code" id="code" value="<?= htmlspecialchars($item->code) ?>" class="form-input" />
				</div>
				<div class="form-group">
					<label for="type" class="form-label">Type</label>
					<select name="type" id="type" class="form-select">
						<option value="">Select Type</option>
						<?php foreach ($item_types as $item_type) { ?>
							<option value="<?= $item_type ?>" <?php if ($item_type == $item->type) print " selected"; ?>><?= ucfirst($item_type) ?></option>
						<?php } ?>
					</select>
				</div>
				<div class="form-group">
					<label for="status" class="form-label">Status</label>
					<select name="status" id="status" class="form-select">
						<option value="">Select Status</option>
						<option value="ACTIVE" <?php if ($item->status == 'ACTIVE') print " selected"; ?>>Active</option>
						<option value="HIDDEN" <?php if ($item->status == 'HIDDEN') print " selected"; ?>>Hidden</option>
						<option value="DELETED" <?php if ($item->status == 'DELETED') print " selected"; ?>>Deleted</option>
					</select>
				</div>
			</div>
		</div>

		<!-- Product Details Section -->
		<div class="form-section">
			<h3 class="section-title">Product Details</h3>
			<div class="form-grid">
				<div class="form-group full-width">
					<label for="name" class="form-label">Product Name</label>
					<input type="text" name="name" id="name" value="<?= htmlspecialchars($item->getMetadata('name')) ?>" class="form-input" />
				</div>
				<div class="form-group full-width">
					<label for="short_description" class="form-label">Short Description</label>
					<textarea name="short_description" id="short_description" class="form-textarea" rows="3"><?= htmlspecialchars($item->getMetadata('short_description')) ?></textarea>
				</div>
				<div class="form-group full-width">
					<label for="description" class="form-label">Full Description</label>
					<textarea name="description" id="description" class="form-textarea" rows="5"><?= htmlspecialchars($item->getMetadata('description')) ?></textarea>
				</div>
			</div>
		</div>

		<!-- Technical Specifications Section -->
		<div class="form-section">
			<h3 class="section-title">Technical Specifications</h3>
			<div class="form-grid">
				<div class="form-group">
					<label for="model" class="form-label">Model</label>
					<input type="text" name="model" id="model" value="<?= htmlspecialchars($item->getMetadata('model')) ?>" class="form-input" />
				</div>
				<div class="form-group">
					<label for="empirical_formula" class="form-label">Empirical Formula</label>
					<input type="text" name="empirical_formula" id="empirical_formula" value="<?= htmlspecialchars($item->getMetadata('empirical_formula')) ?>" class="form-input" />
				</div>
				<div class="form-group">
					<label for="sensitivity" class="form-label">Sensitivity</label>
					<input type="text" name="sensitivity" id="sensitivity" value="<?= htmlspecialchars($item->getMetadata('sensitivity')) ?>" class="form-input" />
				</div>
				<div class="form-group">
					<label for="measure_range" class="form-label">Measure Range</label>
					<input type="text" name="measure_range" id="measure_range" value="<?= htmlspecialchars($item->getMetadata('measure_range')) ?>" class="form-input" />
				</div>
				<div class="form-group">
					<label for="datalogger" class="form-label">Datalogger</label>
					<input type="text" name="datalogger" id="datalogger" value="<?= htmlspecialchars($item->getMetadata('datalogger')) ?>" class="form-input" />
				</div>
				<div class="form-group">
					<label for="accuracy" class="form-label">Accuracy</label>
					<input type="text" name="accuracy" id="accuracy" value="<?= htmlspecialchars($item->getMetadata('accuracy')) ?>" class="form-input" />
				</div>
			</div>
		</div>

		<!-- Configuration Section -->
		<div class="form-section">
			<h3 class="section-title">Configuration</h3>
			<div class="form-grid">
				<div class="form-group">
					<label for="default_dashboard_id" class="form-label">Default Dashboard</label>
					<select name="default_dashboard_id" id="default_dashboard_id" class="form-select">
						<option value="">Select Dashboard</option>
						<?php $default_dashboard = $item->getMetadata('default_dashboard_id');
						foreach ($dashboards as $dashboard) { ?>
				        	<option value="<?=$dashboard->id?>"<?php if ($default_dashboard == $dashboard->id) { print " selected"; } ?>><?=$dashboard->name?></option>
						<?php } ?>
					</select>
				</div>
				<div class="form-group">
					<label for="manual_id" class="form-label">Manual</label>
					<select name="manual_id" id="manual_id" class="form-select">
						<option value="">Select Manual</option>
						<?php foreach ($manuals as $manual) { ?>
					        <option value="<?=$manual->id?>"<?php if ($item->manual_id == $manual->id) { print " selected"; } ?>><?=$manual->name?></option>
						<?php } ?>
					</select>
				</div>
				<div class="form-group">
					<label for="spec_table_image" class="form-label">Spec Table</label>
					<select name="spec_table_image" id="spec_table_image" class="form-select">
						<option value="">Select Spec Table</option>
						<?php foreach ($tables as $table) { ?>
					        <option value="<?=$table->id?>"<?php if ($item->spec_table_image == $table->id) { print " selected"; } ?>><?=$table->name?></option>
						<?php } ?>
					</select>
				</div>
			</div>
		</div>
		<!-- Images Section -->
		<div class="form-section">
			<h3 class="section-title">Product Images</h3>
			<div class="images-container">
				<div class="images-grid">
					<?php foreach ($images as $image) { ?>
						<div class="image-item" id="ItemImageDiv_<?= $image->code ?>">
							<div class="image-wrapper">
								<img src="/_media/api?method=downloadMediaFile&code=<?= $image->code ?>" alt="Product Image" class="product-image" />
								<button type="button" class="remove-image-btn" onclick="dropImage('<?= $image->code ?>')" title="Remove Image">
									<span>&times;</span>
								</button>
							</div>
						</div>
					<?php } ?>
					<div class="image-item add-image-item" id="newImageBox">
						<div class="image-wrapper add-image-wrapper" onclick="initImageSelectWizard()">
							<div class="add-image-content">
								<span class="add-image-icon">+</span>
								<span class="add-image-text">Add Image</span>
							</div>
						</div>
						<input type="hidden" name="new_image_code" id="new_image_code" />
					</div>
				</div>
			</div>
		</div>
		<!-- Pricing Section -->
		<div class="form-section">
			<h3 class="section-title">Pricing</h3>
			
			<!-- Add New Price -->
			<div class="price-add-section">
				<h4 class="subsection-title">Add New Price</h4>
				<div class="form-grid">
					<div class="form-group">
						<label for="new_price_date" class="form-label">Date Active</label>
						<input type="text" name="new_price_date" id="new_price_date" value="now" class="form-input" />
					</div>
					<div class="form-group">
						<label for="new_price_status" class="form-label">Status</label>
						<select name="new_price_status" id="new_price_status" class="form-select">
							<option value="ACTIVE">Active</option>
							<option value="INACTIVE">Inactive</option>
						</select>
					</div>
					<div class="form-group">
						<label for="new_price_amount" class="form-label">Amount</label>
						<input type="text" name="new_price_amount" id="new_price_amount" value="0.00" class="form-input" />
					</div>
				</div>
			</div>

			<!-- Current Prices -->
			<div class="price-history-section">
				<h4 class="subsection-title">Price History</h4>
				<div class="table-container">
					<table class="data-table">
						<thead>
							<tr>
								<th>Date Active</th>
								<th>Status</th>
								<th>Amount</th>
							</tr>
						</thead>
						<tbody>
							<?php foreach ($prices as $price) { ?>
								<tr>
									<td><?= $price->date_active ?></td>
									<td><span class="status-badge status-<?= strtolower($price->status) ?>"><?= $price->status ?></span></td>
									<td class="price-amount">$<?= number_format($price->amount, 2) ?></td>
								</tr>
							<?php } ?>
						</tbody>
					</table>
				</div>
			</div>
		</div>

		<!-- Tags Section -->
		<div class="form-section">
			<h3 class="section-title">Product Tags</h3>
			
			<!-- Current Tags -->
			<div class="tags-container">
				<div class="current-tags">
					<?php if (!empty($productTags)) { ?>
						<div class="tags-list">
							<?php foreach ($productTags as $tag) { ?>
								<div class="tag-item">
									<span class="tag-name"><?= htmlspecialchars($tag->name) ?></span>
									<button type="button" onclick="removeTagById('<?= $tag->id ?>')" class="remove-tag-btn" title="Remove Tag">
										<span>&times;</span>
									</button>
								</div>
							<?php } ?>
						</div>
					<?php } else { ?>
						<p class="no-tags">No tags assigned to this product.</p>
					<?php } ?>
				</div>
				
				<!-- Add New Tag -->
				<div class="add-tag-section">
					<div class="form-group">
						<label for="newTag" class="form-label">Add New Tag</label>
						<div class="input-group">
							<input type="text" name="newTag" id="newTag" class="form-input" placeholder="Enter tag name" />
							<button type="submit" name="addTag" class="btn btn-primary">Add Tag</button>
						</div>
					</div>
				</div>
			</div>
		</div>

		<!-- Search Tags Section -->
		<div class="form-section">
			<h3 class="section-title">Search Tags</h3>
			<p class="section-description">Tags for customer support knowledge center</p>
			
			<!-- Current Search Tags -->
			<div class="search-tags-container">
				<?php if (!empty($productSearchTags)) { ?>
					<div class="table-container">
						<table class="data-table">
							<thead>
								<tr>
									<th>Category</th>
									<th>Search Tag</th>
									<th>Actions</th>
								</tr>
							</thead>
							<tbody>
								<?php foreach ($productSearchTags as $searchTag) { ?>
									<tr>
										<td><?= htmlspecialchars($searchTag->category) ?></td>
										<td><?= htmlspecialchars($searchTag->value) ?></td>
										<td>
											<button type="button" onclick="removeSearchTagById('<?= $searchTag->id ?>')" class="btn btn-danger btn-sm">Remove</button>
										</td>
									</tr>
								<?php } ?>
							</tbody>
						</table>
					</div>
				<?php } else { ?>
					<p class="no-tags">No search tags assigned to this product.</p>
				<?php } ?>
			</div>
			
			<!-- Add New Search Tag -->
			<div class="add-search-tag-section">
				<h4 class="subsection-title">Add New Search Tag</h4>
				<div class="form-grid">
					<div class="form-group">
						<label for="newSearchTagCategory" class="form-label">Category</label>
						<input type="text" class="autocomplete form-input" name="newSearchTagCategory" id="newSearchTagCategory" value="" placeholder="e.g., gas" />
						<ul id="categoryAutocomplete" class="autocomplete-list"></ul>
					</div>
					<div class="form-group">
						<label for="newSearchTag" class="form-label">Search Tag</label>
						<input type="text" class="autocomplete form-input" name="newSearchTag" id="newSearchTag" value="" placeholder="e.g., sulfuryl fluoride" />
						<ul id="tagAutocomplete" class="autocomplete-list"></ul>
					</div>
					<div class="form-group">
						<label class="form-label">&nbsp;</label>
						<button type="submit" name="addSearchTag" class="btn btn-primary">Add Search Tag</button>
					</div>
				</div>
			</div>
		</div>

		<!-- Price Audit Section -->
		<div class="form-section">
			<h3 class="section-title">Price Audit History</h3>
			<?php if (!empty($auditedPrices)) { ?>
				<div class="table-container">
					<table class="data-table">
						<thead>
							<tr>
								<th>User</th>
								<th>Date</th>
								<th>Note</th>
							</tr>
						</thead>
						<tbody>
							<?php foreach ($auditedPrices as $priceAudit) { ?>
								<tr>
									<td>
										<?php $customer = new Register\Customer($priceAudit->user_id); ?>
										<?= htmlspecialchars($customer->first_name . ' ' . $customer->last_name) ?>
									</td>
									<td><?= $priceAudit->date_updated ?></td>
									<td><?= htmlspecialchars(stripslashes($priceAudit->note)) ?></td>
								</tr>
							<?php } ?>
						</tbody>
					</table>
				</div>
			<?php } else { ?>
				<p class="no-data">No price audit history available.</p>
			<?php } ?>
		</div>

		<!-- Form Actions -->
		<div class="form-actions">
			<button type="submit" name="updateSubmit" id="updateSubmit" class="btn btn-primary btn-large">
				<span class="btn-icon">✓</span>
				Update Product
			</button>
		</div>
	</div>
</form>
