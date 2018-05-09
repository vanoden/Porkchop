<?  if (! $GLOBALS['_SESSION_']->customer->has_role('monitor admin'))
    {
        print "<span class=\"form_error\">You are not authorized for this view!</span>";
        return;
    }
?>
<style>
	.dateValue {
		width: 150px;
		padding-left: 3px;
		margin-left: 1px;
	}
	.orgValue {
		width: 250px;
	}
	table.body {
		width: 1200px;
	}
</style>
<?	if ($page->error) { ?>
<div class="form_error"><?=$page->error?></div>
<?	} ?>
<table class="body">
<form name="eventFilter" method="get" action="/_spectros/cal_report">
<tr><td class="title" colspan="5">Event Filter</td></tr>
<tr><td class="label">Start Date</td>
	<td class="label">End Date</td>
	<td class="label">Organization</td>
	<td class="label">Asset</td>
	<td class="label">Product</td>
</tr>
<tr><td class="value"><input type="text" name="date_start" class="value input" value="<?=$date_start?>" /></td>
	<td class="value"><input type="text" name="date_end" class="value input" value="<?=$date_end?>" /></td>
	<td class="value"><select name="organization_id" class="value input">
			<option value="">All</option>
<?	foreach ($organizations as $organization) { ?>
			<option value="<?=$organization->id?>"<? if (isset($_REQUEST['organization_id']) && $organization->id == $_REQUEST['organization_id']) print " selected"; ?>><?=$organization->name?></option>
<?	} ?>
		</select>
	</td>
	<td class="value"><input type="text" name="asset_code" class="value input" value="<?=$asset_code?>" /></td>
	<td class="value"><select name="product_id" class="value input">
			<option value="">All</option>
<?	foreach ($products as $product) { ?>
			<option value="<?=$product->id?>"<? if ($product->id == $_REQUEST['product_id']) print " selected"; ?>><?=$product->code?></option>
<?	} ?>
		</select>
	</td>
</tr>
<tr><td class="form_footer" colspan="5" style="text-align: center"><input type="submit" name="btn_submit" class="button" value="Search" /></td></tr>
</form>
</table>
<table class="body">
<tr><td class="title" colspan="5">Calibration Events [<?=count($verifications)?>]</td></tr>
<tr><td class="label dateValue">Date Submitted</td>
	<td class="label dateValue">Date Written</td>
	<td class="label orgValue">Organization</td>
	<td class="label">Asset</td>
	<td class="label">Product</td>
	<td class="label">Manufacturer</td>
	<td class="label">Cylinder</td>
	<td class="label">Concent.</td>
	<td class="label">Reading</td>
	<td class="label">Voltage</td>
</tr>
<?	foreach ($verifications as $verification) {
    $greenbar = '';
?>
<tr><td class="value dateValue<?=$greenbar?>"><?=$verification->date_request?></td>
	<td class="value dateValue<?=$greenbar?>"><?=$verification->date_confirm?></td>
	<td class="value dateValue<?=$greenbar?>"><?=$verification->customer->organization->name?></td>
	<td class="value<?=$greenbar?>"><?=$verification->asset->code?></td>
	<td class="value<?=$greenbar?>"><?=$verification->asset->product->code?></td>
	<td class="value<?=$greenbar?>"><?=$verification->getMetadata('standard_manufacturer')?></td>
	<td class="value<?=$greenbar?>"><?=$verification->getMetadata('cylinder_number')?></td>
	<td class="value<?=$greenbar?>"><?=$verification->getMetadata('standard_concentration')?></td>
	<td class="value<?=$greenbar?>"><?=$verification->getMetadata('monitor_reading')?></td>
	<td class="value<?=$greenbar?>"><?=$verification->getMetadata('detector_voltage')?></td>
</tr>
<?		if (isset($greenbar) && $greenbar)
			$greenbar = '';
		else
			$greenbar = ' greenbar';
	} ?>
</table>
