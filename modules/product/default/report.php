<?=$page->showAdminPageInfo()?>
<script language="JavaScript">
	var metadata = new Array();
	function showMeta(id) {
		alert(metadata[id]);
	}
</script>
<style>
	.codeValue { width: 21%; }
	.smallValue{ width: 7%; } /* for Type and Status */
	.nameValue { width: 35%; }
	.descValue { width: 30%; }
</style>
<?=$totalRecords?> Products Found
<a href="/_product/edit">New Product</a>
<div id="search_container">
    <form method="get">
        <input type="text" name="search" id="search" placeholder="search" value="<?=$_REQUEST['search']?>" />
        <select name="product_type" id="product_type">
            <option value="">All</option>
            <option value="inventory"<?php if ($_REQUEST['product_type'] == "inventory") { print " selected"; } ?>>Inventory</option>
            <option value="unique"<?php if ($_REQUEST['product_type'] == "unique") { print " selected"; } ?>>Unique</option>
            <option value="group"<?php if ($_REQUEST['product_type'] == "group") { print " selected"; } ?>>Group</option>
            <option value="kit"<?php if ($_REQUEST['product_type'] == "kit") { print " selected"; } ?>>Kit</option>
            <option value="note"<?php if ($_REQUEST['product_type'] == "note") { print " selected"; } ?>>Note</option>
        </select>
        <input type="checkbox" name="status_active" value="true" <?php if ($_REQUEST['status_active'] == 'true') { print "checked"; } ?>/><label>Active</label>
        <input type="checkbox" name="status_hidden" value="true" <?php if ($_REQUEST['status_hidden'] == 'true') { print "checked"; } ?>/><label>Hidden</label>
        <input type="checkbox" name="status_deleted" value="true" <?php if ($_REQUEST['status_deleted'] == 'true') { print "checked"; } ?>/><label>Deleted</label>
        <input type="submit" name="btn_search" value="Search" />
    </form>
</div>
<div class="tableBody">
    <div class="tableRowHeader">
        <div class="tableCell codeValue">Code</div>
		<div class="tableCell smallValue">Type</div>
		<div class="tableCell smallValue">Status</div>
		<div class="tableCell nameValue">Name</div>
		<div class="tableCell descValue">Description</div>
		<div class="tableCell smallValue">Object</div>
		<div class="tableCell smallValue">Sensor Profile</div>
	</div>
<?php
foreach ($products as $product) { 
    if (isset($greenbar) && $greenbar) $greenbar = ''; else $greenbar = ' greenbar';
?>
	<div class="tableRow">
        <div class="tableCell codeValue<?=$greenbar?>"><a href="/_product/edit/<?=$product->code?>"><?=$product->code?></a></div>
		<div class="tableCell smallValue<?=$greenbar?>"><?=isset($product->type) ? $product->type : ''?></div>
		<div class="tableCell smallValue<?=$greenbar?>"><?=isset($product->status) ? $product->status : ''?></div>
		<div class="tableCell nameValue<?=$greenbar?>"><?=$product->getMetadata('name')?></div>
		<div class="tableCell descValue<?=$greenbar?>"><?=$product->getMetadata('short_description')?></div>
		<div class="tableCell smallValue<?=$greenbar?>"><input type="button" name="btn_show_<?=$product->id?>" onclick="showMeta(<?=$product->id?>)" value="Show" /></div>
		<script language="JavaScript">
			metadata[<?=$product->id?>] = "<?php $metadata = $product->getAllMetadata(); foreach ($metadata as $key => $value) { print "$key=$value\\n"; } ?>";
		</script>
		<div class="tableCell descValue<?=$greenbar?>"><a href="/_spectros/admin_asset_sensors?product_id=<?=$product->id?>">View/Edit</a></div>
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