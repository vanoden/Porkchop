<style>
    input[type=button] {
        cursor: pointer;
    }
</style>
<span class="title">Site Counters</span>

<?php if ($page->errorCount() > 0) { ?>
<section id="form-message">
	<ul class="connectBorder errorText">
		<li><?=$page->errorString()?></li>
	</ul>
</section>

<?php	} else if ($page->success) { ?>
<section id="form-message">
	<ul class="connectBorder progressText">
		<li><?=$page->success?></li>
	</ul>
</section>
<?php	} ?>

<div class="tableBody min-tablet">
    <div class="tableRowHeader">
        <div class="tableCell" style="width: 25%;">Key</div>
        <div class="tableCell" style="width: 25%;">Value</div>
        <div class="tableCell" style="width: 50%;">Notes</div>
    </div>
<?php	foreach ($counters as $counter) { ?>
	<div class="tableRow">
		<div class="tableCell" style="width: 25%;"><?=$counter->code()?></div>
		<div class="tableCell" style="width: 25%;"><?=$counter->value()?></div>
		<div class="tableCell" style="width: 50%;">&nbsp;</div>
	</div>
<?php	} ?>
</div>