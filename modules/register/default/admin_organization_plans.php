<?=$page->showSubHeading()?>
<form method="post" action="/_register/admin_organization_plans/<?=$organization->id?>">
<input type="hidden" name="csrf_token" value="<?=$GLOBALS['_SESSION_']->csrf_token?>">
<input type="hidden" name="organization_id" value="<?=$organization->id?>">
<div class="tableBody">
	<div class="tableRowHeader">
		<div class="tableCell">Code</div>
		<div class="tableCell">Name</div>
		<div class="tableCell">Assigned</div>
	</div>
<?php
	if (!empty($organization)) {
		foreach ($product_codes as $product_code) {
			$product = new \Product\Item();
			if (! $product->get($product_code)) {
				$page->addError("Product not found: ".$product_code);
				continue;
			}
?>
	<div class="tableRow">
		<div class="tableCell"><?=$product_code?></div>
		<div class="tableCell"><?=$product_code?></div>
		<div class="tableCell"><input name="product_<?=$product_code?>" type="checkbox" <?=($organization->hasProductID($product->id)) ? "checked" : ""?>></div>
	</div>
<?php
		}
	}
	else {
		$page->addError("Organization not specified");
	}
?>
</div>
<input type="submit" value="Save">
</form>