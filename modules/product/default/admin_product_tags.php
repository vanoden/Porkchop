<?= $page->showAdminPageInfo() ?>

<div id="page_top_nav" style="margin-bottom: 20px;">
	<a href="/_spectros/admin_product/<?= $item->code ?>" class="button">Details</a>
	<a href="/_product/admin_product_prices/<?= $item->code ?>" class="button">Prices</a>
	<a href="/_product/admin_product_vendors/<?= $item->code ?>" class="button">Vendors</a>
	<a href="/_product/admin_images/<?= $item->code ?>" class="button">Images</a>
	<a href="/_product/admin_product_tags/<?= $item->code ?>" class="button" disabled>Tags</a>
	<a href="/_product/admin_product_parts/<?= $item->code ?>" class="button">Parts</a>
</div>

<form method="post" action="/_product/admin_product_tags/<?= $item->code ?>">
<input type="hidden" name="csrfToken" value="<?= $GLOBALS['_SESSION_']->getCSRFToken() ?>">
<input type="hidden" name="id" value="<?= $item->id ?>" />
<input type="hidden" id="removeTagId" name="removeTagId" value="" />
<input type="hidden" id="removeSearchTagId" name="removeSearchTagId" value="" />

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
</form>