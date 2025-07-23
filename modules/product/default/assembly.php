<?=$page->showAdminPageInfo()?>

<div class="tableBody min-tablet marginTop_20">
	<div class="tableRowHeader">
		<div class="tableCell" style="width: 20%;">Part Code</div>
		<div class="tableCell" style="width: 20%;">Part Name</div>
		<div class="tableCell" style="width: 20%;">Quantity</div>
		<div class="tableCell" style="width: 20%;">Available</div>
		<div class="tableCell" style="width: 20%;">Actions</div>
	</div> <!-- end row header -->
	
<?php foreach ($parts as $part): ?>
	<form name="edit_part_<?=$part->id?>" action="/_product/assembly" method="post">
		<input type="hidden" name="part_id" value="<?=$part->id?>">
		<input type="hidden" name="product_id" value="<?=$item->id?>">
		<input type="hidden" name="csrfToken" value="<?=$GLOBALS['_SESSION_']->getCSRFToken()?>">
		<div class="tableRow">
			<div class="tableCell"><?=$part->code?></div>
			<div class="tableCell"><?=$part->name?></div>
			<div class="tableCell"><?=$part->quantity?></div>
			<div class="tableCell"><?=$part->available?></div>
			<div class="tableCell">
				<a href="/_product/edit_part?part_id=<?=$part->id?>">Edit</a> |
				<a href="/_product/delete_part?part_id=<?=$part->id?>" onclick="return confirm('Are you sure you want to delete this part?');">Delete</a>
			</div>
		</div>
	</form>
<?php endforeach; ?>
	<form name="add_part" action="/_product/assembly" method="post">
	<input type="hidden" name="product_id" value="<?=$item->id?>">
	<input type="hidden" name="csrfToken" value="<?=$GLOBALS['_SESSION_']->getCSRFToken()?>">
	<div class="tableRow">
		<div class="tableCell">
			<select name="new_part_id">
				<option value="">Select a part</option>
<?php foreach ($items as $item): ?>
				<option value="<?=$item->id?>"><?=$item->code?></option>
<?php endforeach; ?>
			</select>
		</div>
		<div class="tableCell">
			<input type="number" name="new_part_quantity" min="1" value="1" class="wide_100per">
		</div>
		<div class="tableCell">
			<button type="submit" name="add_part" class="btn btn-primary">Add Part</button>
		</div>
	</div>
	</form>
</div> <!-- end table body -->