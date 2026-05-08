<?=$page->showAdminPageInfo()?>
<script language="JavaScript">
	var metadata = new Array();
	function showMeta(id) {
		alert(metadata[id]);
	}
</script>

<?=$totalRecords?> Products Found
<a href="/_product/admin_product">New Product</a>
<div id="search_container">
    <form method="get">
        <input type="text" name="search" id="search" placeholder="search" value="<?=$_REQUEST['search'] ?? ''?>" />
        <select name="product_type" id="product_type">
            <option value="">All</option>
            <option value="inventory"<?php if (($_REQUEST['product_type'] ?? '') == "inventory") { print " selected"; } ?>>Inventory</option>
            <option value="unique"<?php if (($_REQUEST['product_type'] ?? '') == "unique") { print " selected"; } ?>>Unique</option>
            <option value="group"<?php if (($_REQUEST['product_type'] ?? '') == "group") { print " selected"; } ?>>Group</option>
            <option value="kit"<?php if (($_REQUEST['product_type'] ?? '') == "kit") { print " selected"; } ?>>Kit</option>
            <option value="note"<?php if (($_REQUEST['product_type'] ?? '') == "note") { print " selected"; } ?>>Note</option>
        </select>
        <input type="checkbox" name="status_active" value="1" <?php if (($_REQUEST['status_active'] ?? 0) == 1) { print "checked"; } ?>/><label>Active</label>
        <input type="checkbox" name="status_hidden" value="1" <?php if (($_REQUEST['status_hidden'] ?? 0) == 1) { print "checked"; } ?>/><label>Hidden</label>
        <input type="checkbox" name="status_deleted" value="1" <?php if (($_REQUEST['status_deleted'] ?? 0) == 1) { print "checked"; } ?>/><label>Deleted</label>
        <input type="submit" name="btn_search" value="Search" />
    </form>
</div>
<div class="tableBody">
    <div class="tableRowHeader">
        <div class="tableCell width-21per">Code</div>
		<div class="tableCell width-7per">Type</div>
		<div class="tableCell width-7per">Status</div>
		<div class="tableCell width-35per">Name</div>
		<div class="tableCell width-30per">Description</div>
		<div class="tableCell width-7per">Object</div>
	</div>
<?php
foreach ($products as $product) { 
    if (isset($greenbar) && $greenbar) $greenbar = ''; else $greenbar = ' greenbar';
?>
	<div class="tableRow bandedRows">
        <div class="tableCell width-21per<?=$greenbar?>"><a href="/_product/admin_product/<?=$product->code?>"><?=$product->code?></a></div>
		<div class="tableCell width-7per<?=$greenbar?>"><?=isset($product->type) ? $product->type : ''?></div>
		<div class="tableCell width-7per<?=$greenbar?>"><?=isset($product->status) ? $product->status : ''?></div>
		<div class="tableCell width-35per<?=$greenbar?>"><?=$product->getMetadata('name')?></div>
		<div class="tableCell width-30per<?=$greenbar?>"><?=$product->getMetadata('short_description')?></div>
		<div class="tableCell width-7per<?=$greenbar?>"><input type="button" name="btn_show_<?=$product->id?>" class="margin-0px" onclick="showMeta(<?=$product->id?>)" value="Show" /></div>
		<script language="JavaScript">
			metadata[<?=$product->id?>] = "<?php
			foreach (get_object_vars($product) as $key => $value) {
				if (preg_match('/^_/',$key)) continue;
				if (!is_scalar($value)) print "$key=".print_r($value,true)."\\n";
				else print "$key=$value\\n";
			} ?>";
		</script>
	</div>
<?php	
	} 
	?>
</div>

<!-- Start pagination -->
<div class="pagination" id="pagination">
    <?=$pagination->renderPages()?>
</div>
<!-- End pagination -->