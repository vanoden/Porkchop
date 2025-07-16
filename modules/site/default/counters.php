<style>
    input[type=button] {
        cursor: pointer;
    }
</style>
<?=$page->showAdminPageInfo()?>

<div class="tableBody min-tablet">
    <div class="tableRowHeader">
        <div class="tableCell" style="width: 25%;">Key</div>
        <div class="tableCell" style="width: 25%;">Value</div>
        <div class="tableCell" style="width: 50%;">Notes</div>
    </div>
<?php	foreach ($counters as $counter) { ?>
	<div class="tableRow">
		<div class="tableCell" id="<?=$counter->code()?>_label" style="width: 25%;"><?=$counter->code()?></div>
		<div class="tableCell" id="<?=$counter->code()?>_value" style="width: 25%;"><?=$counter->value()?></div>
		<div class="tableCell" style="width: 50%;">&nbsp;</div>
	</div>
<?php	} ?>
</div>
