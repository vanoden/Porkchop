<script language="Javascript">
	function drop(id) {
		if (confirm('Are you sure you want to delete this navigation item? This action cannot be undone.')) {
			document.forms[0].delete.value = id;
			document.forms[0].submit();
		}
	}
	function childLink(id) {
		document.forms[0].parent_id.value = id;
		document.forms[0].submit();
	}
	function edit(item_id,menu_id,parent_id) {
		window.location.href = "/_navigation/item?menu_id="+menu_id+"&parent_id="+parent_id+"&id="+item_id;
	}
	function follow(target) {
		if (target && target.trim() !== '') {
			window.location.href = target;
		} else {
			alert('No target URL specified for this item.');
		}
	}
	function addItem(parent) {
		window.location.href = "/_navigation/item?menu_id=<?=isset($menu) ? $menu->id : ''?>&parent_id="+parent;
	}
</script>

<!-- Page Header -->
<?=$page->showAdminPageInfo()?>
<!-- End Page Header -->

<div class="form_instruction">
	Manage navigation menu items. 
	<?php if (isset($parent) && $parent->id > 0) { ?>
		Currently viewing sub-items of: <strong><?= htmlspecialchars($parent->title) ?></strong>
	<?php } else { ?>
		Showing top-level menu items for: <strong><?= isset($menu) ? htmlspecialchars($menu->title) : 'Unknown Menu' ?></strong>
	<?php } ?>
</div>

<!-- ============================================== -->
<!-- NAVIGATION ITEMS MANAGEMENT -->
<!-- ============================================== -->
<h3>Navigation Items</h3>
<form name="menuForm" action="/_navigation/items" method="post">
	<input type="hidden" name="csrfToken" value="<?=$GLOBALS['_SESSION_']->getCSRFToken()?>">
	<input type="hidden" name="id" value="<?=isset($menu) ? $menu->id : ''?>" />
	<input type="hidden" name="parent_id" value="<?=isset($parent) ? $parent->id : 0?>" />
	<input type="hidden" name="delete" value="" />
	
	<div class="marginBottom_20">
		<input type="button" name="add" value="Add New Item" class="button" onclick="addItem(<?=isset($parent) ? $parent->id : 0?>);" />
		<?php if (isset($parent) && $parent->id > 0) { ?>
		<input type="button" name="back" value="Back to Parent" class="button secondary" onclick="window.location.href='/_navigation/items/<?= isset($menu) ? $menu->code : '' ?>';" />
		<?php } ?>
	</div>

	<?php if (isset($items) && count($items) > 0) { ?>
	<section class="tableBody clean min-tablet">
		<div class="tableRowHeader">
			<div class="tableCell width-20per">Title</div>
			<div class="tableCell width-25per">Target URL</div>
			<div class="tableCell width-15per">Alt Text</div>
			<div class="tableCell width-15per">Required Role</div>
			<div class="tableCell width-10per">Order</div>
			<div class="tableCell width-15per">Actions</div>
		</div>
		<?php foreach ($items as $item) { ?>
		<div class="tableRow">
			<div class="tableCell">
				<div class="value"><?= htmlspecialchars($item->title) ?></div>
				<?php if (!empty($item->description)) { ?>
				<div class="label marginTop_5" style="font-size: 0.8em; color: #666;">
					<?= htmlspecialchars(strip_tags($item->description)) ?>
				</div>
				<?php } ?>
			</div>
			<div class="tableCell">
				<?php if (!empty($item->target)) { ?>
					<div class="value" style="word-break: break-all;"><?= htmlspecialchars($item->target) ?></div>
				<?php } else { ?>
					<span class="value" style="color: #999;">No target</span>
				<?php } ?>
			</div>
			<div class="tableCell">
				<div class="value"><?= htmlspecialchars($item->alt) ?></div>
			</div>
			<div class="tableCell">
				<div class="value"><?= $item->required_role() ? htmlspecialchars($item->required_role()->name) : 'None' ?></div>
			</div>
			<div class="tableCell">
				<div class="value text-align-center"><?= $item->view_order ?></div>
			</div>
			<div class="tableCell">
				<div class="button-group">
					<input type="button" name="details[<?=$item->id?>]" class="button" value="Edit" onclick="edit(<?=$item->id?>,<?=isset($menu) ? $menu->id : ''?>,<?=isset($parent) ? $parent->id : 0?>);" />
					<input type="button" name="follow[<?=$item->id?>]" class="button secondary" value="Follow" onclick="follow('<?= htmlspecialchars($item->target) ?>');"<?php if (empty($item->target)) print " disabled";?> />
					<input type="button" name="children[<?=$item->id?>]" class="button secondary" value="Children" onclick="childLink(<?=$item->id?>);" />
					<input type="button" name="deleteit[<?=$item->id?>]" class="button secondary" value="Delete" onclick="drop(<?=$item->id?>);" />
				</div>
			</div>
		</div>
		<?php } ?>
	</section>
	<?php } else { ?>
	<section class="tableBody clean min-tablet">
		<div class="tableRow">
			<div class="tableCell width-100per text-align-center">
				<div class="value">No navigation items found.</div>
				<div class="label marginTop_10">Add your first navigation item using the "Add New Item" button above.</div>
			</div>
		</div>
	</section>
	<?php } ?>
</form>
