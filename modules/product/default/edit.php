<script language="Javascript">
	function initImageSelectWizard() {
		childWindow = open("http://<?=$_SERVER['HTTP_HOST']?>/_media/image_select", "imageselect", 'resizable=no,width=500,height=500');
		if (childWindow.opener == null) childWindow.opener = self;
	}
	function endImageSelectWizard(code) {
		document.getElementById('new_image_code').value = code;
		document.getElementById('newImageBox').style.backgroundImage = '/_media/api?method=downloadImageFile&code='+code;
	}
	function dropImage(code) {
		document.getElementById('deleteImage').value = code;
		document.getElementById('ItemImageDiv_'+code).style.display = "none";
	}
</script>

<!-- Page Header -->
<?=$page->showBreadcrumbs()?>
<?=$page->showTitle()?>
<?=$page->showMessages()?>
<!-- End Page Header -->

<form name="productEdit" method="post" action="/_product/edit">
    <input type="hidden" name="id" id="id" value="<?=$item->id?>" />
    <input type="hidden" name="deleteImage" id="deleteImage" value="" />
    <input type="hidden" name="csrfToken" value="<?=$GLOBALS['_SESSION_']->getCSRFToken()?>">
    <div class="body">
	    <div class="input-horiz" id="itemCode">
		    <span class="label">Code</span>
		    <input type="text" name="code" value="<?=htmlspecialchars($item->code)?>" class="value input" />
	    </div>
	    <div class="input-horiz" id="itemType">
		    <span class="label">Type</span>
		    <select class="input value" name="type">
			    <option value="">Select</option>
                <?php foreach ($item_types as $item_type) { ?>
			        <option value="<?=$item_type?>"<?php if ($item_type == $item->type) print " selected";?>><?=$item_type?></option>
                <?php } ?>
		    </select>
	    </div>
	    <div class="input-horiz" id="itemName">
		    <span class="label">Name</span>
		    <input type="text" class="value input wide_lg" name="name" id="name" value="<?=htmlspecialchars($item->metadata['name'])?>" />
	    </div>
	    <div class="input-horiz" id="itemStatus">
		    <span class="label">Status</span>
		    <select name="status" class="value input wide_sm">
			    <option value="">Select</option>
			    <option value="ACTIVE"<?php if ($item->status == 'ACTIVE') print " selected"; ?>>ACTIVE</option>
			    <option value="HIDDEN"<?php if ($item->status == 'HIDDEN') print " selected"; ?>>HIDDEN</option>
			    <option value="DELETED"<?php if ($item->status == 'DELETED') print " selected"; ?>>DELETED</option>
		    </select>
	    </div>
	    <div class="input-horiz" id="itemShortDescription">
		    <span class="label align-top">Short Description</span>
		    <textarea class="value input wide_lg" name="short_description" id="short_description"><?=htmlspecialchars($item->metadata['short_description'])?></textarea>
	    </div>
	    <div class="input-horiz" id="itemDescription">
		    <span class="label align-top">Description</span>
		    <textarea class="value input wide_lg" name="description" id="description"><?=htmlspecialchars($item->metadata['description'])?></textarea>
	    </div>
	    <div class="input-horiz">
		    <span class="label">Model</span>
		    <input type="text" class="value input wide_md" name="model" id="model" value="<?=htmlspecialchars($item->metadata['model'])?>" />
	    </div>
	    <div class="input-horiz">
		    <span class="label">Empirical Formula</span>
		    <input type="text" class="value input wide_md" name="empirical_formula" id="empirical_formula" value="<?=htmlspecialchars($item->metadata['empirical_formula'])?>" />
	    </div>
	    <div class="input-horiz">
		    <span class="label">Sensitivity</span>
		    <input type="text" class="value input wide_md" name="sensitivity" id="sensitivity" value="<?=htmlspecialchars($item->metadata['sensitivity'])?>" />
	    </div>
	    <div class="input-horiz">
		    <span class="label">Measure Range</span>
		    <input type="text" class="value input wide_md" name="measure_range" id="measure_range" value="<?=htmlspecialchars($item->metadata['measure_range'])?>" />
	    </div>
	    <div class="input-horiz">
		    <span class="label">Accuracy</span>
		    <input type="text" class="value input wide_md" name="accuracy" id="accuracy" value="<?=htmlspecialchars($item->metadata['accuracy'])?>" />
	    </div>
	    <div class="input-horiz">
		    <span class="label">Default Dashboard</span>
		    <select class="value input wide_md" name="default_dashboard_id" id="default_dashboard_id">
            <?php $default_dashboard = $item->getMetadata('default_dashboard_id');
            	foreach($dashboards as $dashboard) { ?>
		        	<option value="<?=$dashboard->id?>"<?php if ($default_dashboard->value == $dashboard->id) { print " selected"; } ?>><?=$dashboard->name?></option>
            <?php } ?>
		    </select>
	    </div>
	    <div class="input-horiz">
		    <span class="label">Manual</span>
		    <select class="value input wide_md" name="manual_id" id="manual_id">
                <?php foreach($manuals as $manual) { ?>
			        <option value="<?=$manual->id?>"<?php if ($item->manual_id == $manual->id) { print " selected"; } ?>><?=$manual->name?></option>
                <?php } ?>
		    </select>
	    </div>
	    <div class="input-horiz">
		    <span class="label">Spec Table</span>
		    <select class="value input wide_md" name="spec_table_image" id="spec_table_image">
			    <option value="Select"></option>
                <?php foreach($tables as $table) { ?>
			        <option value="<?=$table->id?>"<?php if ($item->spec_table_image == $table->id) { print " selected"; } ?>><?=$table->name?></option>
                <?php } ?>
		    </select>
	    </div>
	    <div class="input-horiz" id="itemImages">
		    <span class="label align-top">Images</span>
            <?php foreach($images as $image) { ?>
		        <div class="editItemImage" id="ItemImageDiv_<?=$image->code?>">
			        <input type="button" name="btn_drop" class="editItemThumbnail" onclick="dropImage('<?=$image->code?>')" value="X" />
			        <img class="editItemThumbnail" src="/_media/api?method=downloadMediaFile&code=<?=$image->files[0]->code?>">
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
			<tr><th>Date Active</th>
				<th>Status</th>
				<th>Amount</th>
			</tr>
			<tr><td><input type="text" name="new_price_date" value="now" /></td>
				<td><select name="new_price_status">
						<option value="ACTIVE">ACTIVE</option>
						<option value="INACTIVE">INACTIVE</option>
				</select></td>
				<td><input type="text" name="new_price_amount" value="0.00" /></td>
			</tr>
			</table>
			<span class="label align-top">Prices</span>
			<table class="body">
			<tr><th>Date Active</th>
				<th>Status</th>
				<th>Amount</th>
			</tr>
			<?php foreach ($prices as $price) { ?>
			<tr>
				<td class="value"><?=$price->date_active?></td>
				<td class="value"><?=$price->status?></td>
				<td class="value"><?=$price->amount?></td>
			</tr>
			<?php } ?>
			</table>
	    <div class="editSubmit button-bar floating">
		    <input type="submit" class="button" value="Update" name="submit" id="submit"/>
	    </div>
    </div>
</form>
