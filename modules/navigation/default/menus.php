<script language="Javascript">
	function goTo(target) {
		window.location.href = target;
		return true;
	}
</script>
<?=$page->showAdminPageInfo()?>

<section class="navigation-menus-layout">
	<?php if (!empty($menus)) { ?>
	<h2 class="pageSect_full">Existing menus</h2>
	<div class="menus-list connectBorder">
		<table class="menus-table body clear-both">
			<thead>
				<tr>
					<th>Code</th>
					<th>Title</th>
					<th>Show close button</th>
					<th>Actions</th>
				</tr>
			</thead>
			<tbody>
			<?php foreach ($menus as $menu) { ?>
				<tr>
					<td colspan="4" class="menu-row-wrap">
						<form class="menu-row-form" action="/_navigation/menus" method="post">
							<input type="hidden" name="id" value="<?=$menu->id?>" />
							<input type="hidden" name="csrfToken" value="<?=$GLOBALS['_SESSION_']->getCSRFToken()?>">
							<table class="menu-inner"><tr>
								<td class="col-code"><input type="text" name="code" value="<?=htmlspecialchars($menu->code ?? '')?>" class="input" /></td>
								<td class="col-title"><input type="text" name="title" value="<?=htmlspecialchars($menu->title ?? '')?>" class="input" /></td>
								<td class="col-check"><label><input type="checkbox" name="show_close_button" value="1" <?=($menu->show_close_button ? 'checked' : '')?> /> Show close</label></td>
								<td class="col-actions">
									<button type="submit" name="btn_submit" value="Update" class="button">Update</button>
									<button type="button" class="button" onclick="goTo('/_navigation/items/<?=htmlspecialchars($menu->code ?? '')?>')">Items</button>
								</td>
							</tr></table>
						</form>
					</td>
				</tr>
			<?php } ?>
			</tbody>
		</table>
	</div>
	<?php } ?>

	<h2 class="pageSect_full"><?=!empty($menus) ? 'Add new menu' : 'Add menu'?></h2>
	<section class="connectBorder">
		<form class="menu-add-form menu-add-form-horizontal" action="/_navigation/menus" method="post">
			<input type="hidden" name="id" value="0" />
			<input type="hidden" name="csrfToken" value="<?=$GLOBALS['_SESSION_']->getCSRFToken()?>">
			<div class="menu-add-row">
				<label for="new_code">Code</label>
				<input id="new_code" type="text" name="code" value="" class="input" placeholder="Code" />
				<label for="new_title">Title</label>
				<input id="new_title" type="text" name="title" value="" class="input" placeholder="Title" />
				<label class="menu-add-check"><input type="checkbox" name="show_close_button" value="1" /> Show close</label>
				<button type="submit" name="btn_submit" value="Add" class="button">Add menu</button>
			</div>
		</form>
	</section>
</section>
