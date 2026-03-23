<?= $page->showSubHeading() ?>

<div class="tableBody bandedRows">
	<div class="tableRow header">
		<div class="tableCell">ID</div>
		<div class="tableCell">Address</div>
		<div class="tableCell">Size</div>
		<div class="tableCell">Type</div>
		<div class="tableCell">Risk Level</div>
		<div class="tableCell">Managed</div>
		<div class="tableCell">Last Seen</div>
	</div>
	<?php foreach ($subnets as $subnet): ?>
		<div class="tableRow">
			<div class="tableCell"><a href="/_network/admin_subnet/<?php print $subnet->id; ?>"><?php print $subnet->id; ?></a></div>
			<div class="tableCell"><?php print long2ip($subnet->address); ?></div>
			<div class="tableCell"><?php print $subnet->size; ?></div>
			<div class="tableCell"><?php print strtoupper($subnet->type); ?></div>
			<div class="tableCell"><?php print $subnet->risk_level; ?></div>
			<div class="tableCell"><?php print $subnet->managed; ?></div>
			<div class="tableCell"><?php print $subnet->date_last_seen; ?></div>
		</div>
	<?php endforeach; ?>
</div>