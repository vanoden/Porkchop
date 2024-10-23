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

	<div class="body">
		<div class="input-horiz" id="itemCode">
			<span class="label">Code</span>
			<input type="text" name="code" value="<?= htmlspecialchars($item->code) ?>" class="value input" />
		</div>
		<div class="input-horiz" id="itemType">
			<span class="label">Type</span>
			<select class="input value" name="type">
				<option value="">Select</option>
				<?php foreach ($item_types as $item_type) { ?>
					<option value="<?= $item_type ?>" <?php if ($item_type == $item->type) print " selected"; ?>><?= $item_type ?></option>
				<?php } ?>
			</select>
		</div>
		<div class="input-horiz" id="itemName">
			<span class="label">Name</span>
			<input type="text" class="value input wide_lg" name="name" id="name" value="<?= htmlspecialchars($item->metadata()->getValue('name')) ?>" />
		</div>
		<div class="input-horiz" id="itemStatus">
			<span class="label">Status</span>
			<select name="status" class="value input wide_sm">
				<option value="">Select</option>
				<option value="ACTIVE" <?php if ($item->status == 'ACTIVE') print " selected"; ?>>ACTIVE</option>
				<option value="HIDDEN" <?php if ($item->status == 'HIDDEN') print " selected"; ?>>HIDDEN</option>
				<option value="DELETED" <?php if ($item->status == 'DELETED') print " selected"; ?>>DELETED</option>
			</select>
		</div>
		<div class="input-horiz" id="itemShortDescription">
			<span class="label align-top">Short Description</span>
			<textarea class="value input wide_lg" name="short_description" id="short_description"><?= htmlspecialchars($item->metadata()->getValue('short_description')) ?></textarea>
		</div>
		<div class="input-horiz" id="itemDescription">
			<span class="label align-top">Description</span>
			<textarea class="value input wide_lg" name="description" id="description"><?= htmlspecialchars($item->metadata()->getValue('description')) ?></textarea>
		</div>
		<div class="input-horiz" id="itemModel">
			<span class="label">Model</span>
			<input type="text" class="value input wide_md" name="model" id="model" value="<?= htmlspecialchars($item->metadata()->getValue('model')) ?>" />
		</div>
		<div class="input-horiz" id="itemEmpericalFormula">
			<span class="label">Empirical Formula</span>
			<input type="text" class="value input wide_md" name="empirical_formula" id="empirical_formula" value="<?= htmlspecialchars($item->metadata()->getValue('empirical_formula')) ?>" />
		</div>
		<div class="input-horiz">
			<span class="label">Sensitivity</span>
			<input type="text" class="value input wide_md" name="sensitivity" id="sensitivity" value="<?= htmlspecialchars($item->metadata()->getValue('sensitivity')) ?>" />
		</div>
		<div class="input-horiz">
			<span class="label">Measure Range</span>
			<input type="text" class="value input wide_md" name="measure_range" id="measure_range" value="<?= htmlspecialchars($item->metadata()->getValue('measure_range')) ?>" />
		</div>
		<div class="input-horiz">
			<span class="label">Datalogger</span>
			<input type="text" class="value input wide_md" name="datalogger" id="datalogger" value="<?= htmlspecialchars($item->metadata()->getValue('datalogger')) ?>" />
		</div>
		<div class="input-horiz">
			<span class="label">Accuracy</span>
			<input type="text" class="value input wide_md" name="accuracy" id="accuracy" value="<?= htmlspecialchars($item->metadata()->getValue('accuracy')) ?>" />
		</div>
		<div class="input-horiz">
			<span class="label">Default Dashboard</span>
			<select class="value input wide_md" name="default_dashboard_id" id="default_dashboard_id">
				<?php $default_dashboard = $item->getMetadata('default_dashboard_id');
				foreach ($dashboards as $dashboard) { ?>
		        	<option value="<?=$dashboard->id?>"<?php if ($default_dashboard->value == $dashboard->id) { print " selected"; } ?>><?=$dashboard->name?></option>
				<?php } ?>
			</select>
		</div>
		<div class="input-horiz">
			<span class="label">Manual</span>
			<select class="value input wide_md" name="manual_id" id="manual_id">
				<?php foreach ($manuals as $manual) { ?>
			        <option value="<?=$manual->id?>"<?php if ($item->manual_id == $manual->id) { print " selected"; } ?>><?=$manual->name?></option>
				<?php } ?>
			</select>
		</div>
		<div class="input-horiz">
			<span class="label">Spec Table</span>
			<select class="value input wide_md" name="spec_table_image" id="spec_table_image">
				<option value="Select"></option>
				<?php foreach ($tables as $table) { ?>
			        <option value="<?=$table->id?>"<?php if ($item->spec_table_image == $table->id) { print " selected"; } ?>><?=$table->name?></option>
				<?php } ?>
			</select>
		</div>
		<div class="input-horiz" id="itemImages">
			<span class="label align-top">Images</span>
			<?php foreach ($images as $image) { ?>
				<div class="editItemImage" id="ItemImageDiv_<?= $image->code ?>">
					<input type="button" name="btn_drop" class="editItemThumbnail" onclick="dropImage('<?= $image->code ?>')" value="X" />
					<img class="editItemThumbnail" src="/_media/api?method=downloadMediaFile&code=<?= $image->files[0]->code ?>">
				</div>
			<?php } ?>
			<div class="editItemImage" id="newImageBox">
				<input type="button" name="addImageButton" value="" class="add-image" onclick="initImageSelectWizard()" />
				<input type="hidden" name="new_image_code" id="new_image_code" />
			</div>
		</div>
		<div class="input-horiz" id="itemPrices">
			<span class="label align-top">Add Price</span>
			<table class="body">
				<tr>
					<th>Date Active</th>
					<th>Status</th>
					<th>Amount</th>
				</tr>
				<tr>
					<td><input type="text" name="new_price_date" value="now" /></td>
					<td>
						<select name="new_price_status">
							<option value="ACTIVE">ACTIVE</option>
							<option value="INACTIVE">INACTIVE</option>
						</select>
					</td>
					<td><input type="text" name="new_price_amount" value="0.00" /></td>
				</tr>
			</table>

			<h3>Prices</h3>
			<table class="body">
				<tr>
					<th>Date Active</th>
					<th>Status</th>
					<th>Amount</th>
				</tr>
				<?php foreach ($prices as $price) { ?>
					<tr>
						<td class="value"><?= $price->date_active ?></td>
						<td class="value"><?= $price->status ?></td>
						<td class="value"><?= $price->amount ?></td>
					</tr>
				<?php } ?>
			</table>

			<h3>Product Tags</h3>
			<div class="tableBody min-tablet">
				<div class="tableRowHeader">
					<div class="tableCell" style="width: 35%;">Tag</div>
				</div>
				<?php
				foreach ($productTags as $tag) {
				?>
					<div class="tableRow">
						<div class="tableCell">
							<input type="button" onclick="removeTagById('<?= $tag->id ?>')" name="removeTag" value="Remove" class="button" /> <strong><?= $tag->name ?></strong>
						</div>
					</div>
				<?php
				}
				?>
				<div class="tableRow">
					<div class="tableCell"><label>New Tag:</label><input type="text" class="" name="newTag" value="" /></div>
				</div>
				<div><input type="submit" name="addTag" value="Add Tag" class="button" /></div>
			</div>

			<h3 style="display:inline;">Product Search Tags</h3>
			<h4 style="display:inline;">(customer support knowledge center)</h4>
			<div class="tableBody min-tablet">
				<div class="tableRowHeader">
					<div class="tableCell" style="width: 33%;">&nbsp;</div>
					<div class="tableCell" style="width: 33%;">Category</div>
					<div class="tableCell" style="width: 33%;">Search Tag</div>
				</div>
				<?php
				foreach ($productSearchTags as $searchTag) {
				?>
					<div class="tableRow">
						<div class="tableCell">
							<input type="button" onclick="removeSearchTagById('<?= $searchTag->id ?>')" name="removeSearchTag" value="Remove" class="button" />
						</div>
						<div class="tableCell">
							<?= $searchTag->category ?>
						</div>
						<div class="tableCell">
							<?= $searchTag->value ?>
						</div>

					</div>

				<?php
				}
				?>
				<br />
				<div class="tableRow">
					<div class="tableCell">
						<label>Category:</label>
						<input type="text" class="autocomplete" name="newSearchTagCategory" id="newSearchTagCategory" value="" placeholder="gas" />
						<ul id="categoryAutocomplete" class="autocomplete-list"></ul>
					</div>
					<div class="tableCell">
						<label>New Search Tag:</label>
						<input type="text" class="autocomplete" name="newSearchTag" id="newSearchTag" value="" placeholder="sulfuryl fluoride" />
						<ul id="tagAutocomplete" class="autocomplete-list"></ul>
					</div>
				</div>
				<div><input type="submit" name="addSearchTag" value="Add Search Tag" class="button" /></div>
			</div>

			<h3>Price Audit Info</h3>
			<table class="body">
				<tr>
					<th>User</th>
					<th>Date</th>
					<th>Note</th>
				</tr>
				<?php foreach ($auditedPrices as $priceAudit) { ?>
					<tr>
						<td class="value">
							<?php $customer = new Register\Customer($priceAudit->user_id); ?>
							<?= $customer->first_name ?> <?= $customer->last_name ?>
						</td>
						<td class="value"><?= $priceAudit->date_updated ?></td>
						<td class="value"><?= $priceAudit->note ?></td>
					</tr>
				<?php } ?>
			</table>
			<div class="editSubmit button-bar floating">
				<input type="submit" class="button" value="Update" name="updateSubmit" id="updateSubmit" />
			</div>
		</div>
</form>
