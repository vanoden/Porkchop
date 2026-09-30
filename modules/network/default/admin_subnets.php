<?= $page->showSubHeading() ?>
<h3>Found <?= $subnet_list->count() ?> subnets</h3>
<div class="tableBody bandedRows">
	<div class="tableRow header">
		<div class="tableCell">ID</div>
		<div class="tableCell">Address</div>
		<div class="tableCell">Size</div>
		<div class="tableCell">Type</div>
		<div class="tableCell">Risk Level</div>
		<div class="tableCell">Managed</div>
		<div class="tableCell">Last Seen</div>
		<div class="tableCell">URI</div>
	</div>
	<?php foreach ($subnets as $subnet) {
		$session = $subnet->session();
	?>
		<div class="tableRow">
			<div class="tableCell"><a href="/_network/admin_subnet/<?php print $subnet->id; ?>"><?php print $subnet->id; ?></a></div>
			<div class="tableCell"><?= $subnet->realAddress() ?></div>
			<div class="tableCell"><?= $subnet->size ?></div>
			<div class="tableCell"><?= strtoupper($subnet->type); ?></div>
			<div class="tableCell"><?= $subnet->risk_level; ?></div>
			<div class="tableCell"><?= $subnet->managed; ?></div>
			<div class="tableCell"><?= $subnet->date_last_seen; ?></div>
			<div class="tableCell"><?= $subnet->uri_last_seen; ?></div>
		</div>
	<?php } ?>
</div>