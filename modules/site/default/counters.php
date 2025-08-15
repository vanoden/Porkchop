<?=$page->showAdminPageInfo()?>

<div class="tableBody min-tablet">
    <div class="tableRowHeader">
        <div class="tableCell tableCell-width-25">Key</div>
        <div class="tableCell tableCell-width-25">Value</div>
        <div class="tableCell tableCell-width-50">Notes</div>
    </div>
<?php	foreach ($counters as $counter) { ?>
	<div class="tableRow">
		<div class="tableCell tableCell-width-25" id="<?=$counter->code()?>_label"><?=$counter->code()?></div>
		<div class="tableCell tableCell-width-25" id="<?=$counter->code()?>_value"><?=$counter->value()?></div>
		<div class="tableCell tableCell-width-50">&nbsp;</div>
	</div>
<?php	} ?>
</div>
