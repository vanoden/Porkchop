<?=$page->showBreadcrumbs();?>
<?=$page->showTitle();?>
<?=$page->showMessages();?>

<form method="post">
<div class="tableBody">
	<div class="tableRowHeader">
		<div class="tableCell">Name</div>
		<div class="tableCell">Symbol</div>
	</div>
<?php	foreach ($currencies as $currency) { ?>
	<div class="tableRow">
		<div class="tableCell"><input type="text" name="currency_name[<?=$currency->id?>]" value="<?=$currency->name?>" /></div>
		<div class="tableCell"><input type="text" name="currency_symbol[<?=$currency->id?>]" value="<?=$currency->symbol?>" /></div>
	</div>
<?php	} ?>
	<div class="tableRow">
		<div class="tableCell"><input type="text" name="new_currency_name" placeholder="New Currency Name" /></div>
		<div class="tableCell"><input type="text" name="new_currency_symbol" placeholder="New Currency Symbol" /></div>
	</div>
	<div class="width-800px">
		<input type="submit" name="btn_submit" value="Submit" />
	</div>
</div>
</form>