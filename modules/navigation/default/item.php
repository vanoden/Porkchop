<!-- Page Header -->
<?=$page->showAdminPageInfo()?>
<!-- End Page Header -->

<div class="form_instruction">
	<?php if (isset($item) && $item->id) { ?>
		Edit navigation item: <strong><?= htmlspecialchars($item->title) ?></strong>
	<?php } else { ?>
		Create new navigation item for menu: <strong><?= isset($menu) ? htmlspecialchars($menu->title) : 'Unknown Menu' ?></strong>
	<?php } ?>
	<?php if (isset($parent) && $parent->id > 0) { ?>
		<br>Parent item: <strong><?= htmlspecialchars($parent->title) ?></strong>
	<?php } ?>
</div>

<form name="menuForm" action="/_navigation/item" method="post">
	<input type="hidden" name="csrfToken" value="<?=$GLOBALS['_SESSION_']->getCSRFToken()?>">
	<input type="hidden" name="id" value="<?=isset($item) ? $item->id : ''?>" />
	<input type="hidden" name="menu_id" value="<?=isset($menu) ? $menu->id : ''?>" />
	<input type="hidden" name="parent_id" value="<?=isset($parent) ? $parent->id : 0?>" />

	<!-- ============================================== -->
	<!-- NAVIGATION ITEM BASIC INFORMATION -->
	<!-- ============================================== -->
	<h3>Item Information</h3>
	<section class="tableBody clean min-tablet">
		<div class="tableRowHeader">
			<div class="tableCell width-20per">Field</div>
			<div class="tableCell width-80per">Value</div>
		</div>
		<div class="tableRow">
			<div class="tableCell">
				<span class="label">Title</span>
			</div>
			<div class="tableCell">
				<input type="text" name="title" class="value input width-100per" value="<?=isset($item) ? htmlspecialchars($item->title) : ''?>" placeholder="Enter navigation item title" required />
			</div>
		</div>
		<div class="tableRow">
			<div class="tableCell">
				<span class="label">Target URL</span>
			</div>
			<div class="tableCell">
				<input type="text" name="target" class="value input width-100per" value="<?=isset($item) ? htmlspecialchars($item->target) : ''?>" placeholder="Enter target URL (e.g., /page, https://example.com)" />
				<small class="help-text">Leave empty for menu headers or separators</small>
			</div>
		</div>
		<div class="tableRow">
			<div class="tableCell">
				<span class="label">Alt Text</span>
			</div>
			<div class="tableCell">
				<input type="text" name="alt" class="value input width-100per" value="<?=isset($item) ? htmlspecialchars($item->alt) : ''?>" placeholder="Enter alt text for accessibility" />
			</div>
		</div>
		<div class="tableRow">
			<div class="tableCell">
				<span class="label">Required Role</span>
			</div>
			<div class="tableCell">
				<select name="required_role_id" class="value input width-100per">
					<option value="">None - Public Access</option>
					<?php if (isset($roles) && is_array($roles)) {
						foreach ($roles as $role) { ?>
					<option value="<?=isset($role) ? $role->id : ''?>"<?php if (isset($role) && isset($item) && $role->id == $item->required_role_id) print " selected";?>><?=isset($role) ? htmlspecialchars($role->name) : ''?></option>
					<?php } 
					} ?>
				</select>
				<small class="help-text">Select a role to restrict access to this item</small>
			</div>
		</div>
		<div class="tableRow">
			<div class="tableCell">
				<span class="label">View Order</span>
			</div>
			<div class="tableCell">
				<input type="number" name="view_order" class="value input width-100per" value="<?=isset($item) ? $item->view_order : ''?>" placeholder="Enter display order (lower numbers appear first)" />
				<small class="help-text">Lower numbers appear first in the menu</small>
			</div>
		</div>
	</section>

	<!-- ============================================== -->
	<!-- ITEM DESCRIPTION -->
	<!-- ============================================== -->
	<h3>Description</h3>
	<section class="tableBody clean min-tablet">
		<div class="tableRowHeader">
			<div class="tableCell width-20per">Field</div>
			<div class="tableCell width-80per">Value</div>
		</div>
		<div class="tableRow">
			<div class="tableCell">
				<span class="label">Description</span>
			</div>
			<div class="tableCell">
				<textarea name="description" class="value input width-100per" rows="4" placeholder="Enter item description (optional)"><?=isset($item) ? htmlspecialchars(strip_tags($item->description)) : ''?></textarea>
				<small class="help-text">Optional description for this navigation item</small>
			</div>
		</div>
	</section>

	<!-- ============================================== -->
	<!-- FORM ACTIONS -->
	<!-- ============================================== -->
	<div class="form_footer marginTop_20">
		<?php if (isset($item) && $item->id) { ?>
		<input type="submit" class="button" name="btn_submit" value="Update Item" />
		<input type="submit" class="button secondary" name="btn_delete" value="Delete" onclick="return confirm('Are you sure you want to delete this navigation item? This action cannot be undone.');" />
		<?php } else { ?>
		<input type="submit" class="button" name="btn_submit" value="Submit" />
		<?php } ?>
		<input type="button" class="button secondary" value="Cancel" onclick="window.history.back();" />
	</div>
</form>
