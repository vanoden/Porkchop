<?=$page->showAdminPageInfo()?>
<form method="post" action="/_register/admin_organization_plans/<?=$organization->id?>">
<input type="hidden" name="csrf_token" value="<?=$GLOBALS['_SESSION_']->csrf_token?>">
<input type="hidden" name="organization_id" value="<?=$organization->id?>">

<div class="tabs">
    <a href="/_register/admin_organization/<?= $organization->code ?>" class="tab <?= $activeTab==='details'?'active':'' ?>">Details</a>
    <a href="/_register/admin_organization_users/<?= $organization->code ?>" class="tab <?= $activeTab==='users'?'active':'' ?>">Users</a>
    <a href="/_register/admin_organization_tags/<?= $organization->code ?>" class="tab <?= $activeTab==='tags'?'active':'' ?>">Tags</a>
    <a href="/_register/admin_organization_locations/<?= $organization->code ?>" class="tab <?= $activeTab==='locations'?'active':'' ?>">Locations</a>
    <a href="/_register/admin_organization_audit_log/<?= $organization->code ?>" class="tab <?= $activeTab==='audit'?'active':'' ?>">Audit Log</a>
	<a href="/_register/admin_organization_plans/<?= $organization->code ?>" class="tab <?= $activeTab==='plans'?'active':'' ?>">Plans</a>
</div>

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