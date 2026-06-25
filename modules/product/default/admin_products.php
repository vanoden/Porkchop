<?=$page->showAdminPageInfo()?>
<script language="JavaScript">
	var metadata = new Array();
	function showMeta(id) {
		alert(metadata[id]);
	}
	function resetPaginationAndSearch() {
		document.getElementById('pagination_start_id').value = '0';
	}
</script>

<form id="productSearch" method="get" class="monitor-admin-list product-admin-products">
	<input type="hidden" id="pagination_start_id" name="pagination_start_id" value="<?=htmlspecialchars((string)$pagination->startId(), ENT_QUOTES, 'UTF-8')?>">

	<div class="filter-bar">
		<div class="filter-bar__controls">
			<div class="form-field filter-bar__search">
				<label for="search">Search</label>
				<input type="text" name="search" id="search" placeholder="search" value="<?=htmlspecialchars($_REQUEST['search'] ?? '', ENT_QUOTES, 'UTF-8')?>">
			</div>
			<div class="form-field">
				<label for="product_type">Type</label>
				<select name="product_type" id="product_type">
					<option value="">All</option>
					<option value="inventory"<?php if (($_REQUEST['product_type'] ?? '') == 'inventory') print ' selected'; ?>>Inventory</option>
					<option value="unique"<?php if (($_REQUEST['product_type'] ?? '') == 'unique') print ' selected'; ?>>Unique</option>
					<option value="group"<?php if (($_REQUEST['product_type'] ?? '') == 'group') print ' selected'; ?>>Group</option>
					<option value="kit"<?php if (($_REQUEST['product_type'] ?? '') == 'kit') print ' selected'; ?>>Kit</option>
					<option value="note"<?php if (($_REQUEST['product_type'] ?? '') == 'note') print ' selected'; ?>>Note</option>
				</select>
			</div>
			<div class="form-field form-field--checks">
				<span class="form-field__group-label">Status</span>
				<label class="check-field">
					<input type="checkbox" name="status_active" value="1" <?php if (!empty($_REQUEST['status_active'])) print 'checked'; ?>>
					Active
				</label>
				<label class="check-field">
					<input type="checkbox" name="status_hidden" value="1" <?php if (!empty($_REQUEST['status_hidden'])) print 'checked'; ?>>
					Hidden
				</label>
				<label class="check-field">
					<input type="checkbox" name="status_deleted" value="1" <?php if (!empty($_REQUEST['status_deleted'])) print 'checked'; ?>>
					Deleted
				</label>
			</div>
		</div>
		<div class="button-group filter-bar__actions">
			<button type="submit" name="btn_search" value="Search" onclick="resetPaginationAndSearch()">Search</button>
			<a class="button btn-secondary" href="/_product/admin_product">New Product</a>
		</div>
	</div>

	<h2>Products [<?=$totalRecords?>]</h2>
	<table class="responsive-table responsive-table--banded">
		<thead>
			<tr>
				<th scope="col" class="col-w-15">Code</th>
				<th scope="col" class="col-w-10">Type</th>
				<th scope="col" class="col-w-10">Status</th>
				<th scope="col" class="col-w-25">Name</th>
				<th scope="col">Description</th>
				<th scope="col" class="col-w-10">Object</th>
			</tr>
		</thead>
		<tbody>
<?php if (is_array($products) && count($products)) {
	foreach ($products as $product) { ?>
			<tr>
				<td data-label="Code"><a href="/_product/admin_product/<?=htmlspecialchars($product->code, ENT_QUOTES, 'UTF-8')?>"><?=htmlspecialchars($product->code)?></a></td>
				<td data-label="Type"><?=htmlspecialchars($product->type ?? '')?></td>
				<td data-label="Status"><?=htmlspecialchars($product->status ?? '')?></td>
				<td data-label="Name"><?=htmlspecialchars((string)$product->getMetadata('name'))?></td>
				<td data-label="Description"><?=htmlspecialchars((string)$product->getMetadata('short_description'))?></td>
				<td data-label="Object"><button type="button" name="btn_show_<?=$product->id?>" onclick="showMeta(<?=$product->id?>)" value="Show">Show</button></td>
			</tr>
			<script language="JavaScript">
				metadata[<?=$product->id?>] = "<?php
				foreach (get_object_vars($product) as $key => $value) {
					if (preg_match('/^_/', $key)) continue;
					if (!is_scalar($value)) print "$key=".print_r($value, true)."\\n";
					else print "$key=$value\\n";
				} ?>";
			</script>
<?php }
} else { ?>
			<tr>
				<td colspan="6">No products found</td>
			</tr>
<?php } ?>
		</tbody>
	</table>

	<?=$pagination->renderBar()?>
</form>
