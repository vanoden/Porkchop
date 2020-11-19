<?php  if (! $GLOBALS['_SESSION_']->customer->has_role('monitor admin'))
    {
        print "<span class=\"form_error\">You are not authorized for this view!</span>";
        return;
    }
?>
<style>
	.dateValue { width: 150px; padding-left: 3px; margin-left: 1px;	}
	.orgValue { width: 250px; }
	table.body { width: 1200px;	}
</style>
<?php	if ($page->error) { ?>
<div class="form_error"><?=$page->error?></div>
<?php	} ?>
<h2>Event Filter</h2>
<table class="body">
<form name="eventFilter" method="get" action="/_spectros/cal_report">
<tr>
	<th class="label">Start Date</th>
	<th class="label">End Date</th>
	<th class="label">Organization</th>
	<th class="label">Asset</th>
	<th class="label">Product</th>
</tr>
<tr><td class="value"><input type="text" name="date_start" class="value input" value="<?=$date_start?>" /></td>
	<td class="value"><input type="text" name="date_end" class="value input" value="<?=$date_end?>" /></td>
	<td class="value"><select name="organization_id" class="value input">
			<option value="">All</option>
<?php	foreach ($organizations as $organization) { ?>
			<option value="<?=$organization->id?>"<?php if (isset($_REQUEST['organization_id']) && $organization->id == $_REQUEST['organization_id']) print " selected"; ?>><?=$organization->name?></option>
<?php	} ?>
		</select>
	</td>
	<td class="value"><input type="text" name="asset_code" class="value input" value="<?=$asset_code?>" /></td>
	<td class="value"><select name="product_id" class="value input">
			<option value="">All</option>
<?php	foreach ($products as $product) { ?>
			<option value="<?=$product->id?>"<?php if ($product->id == $_REQUEST['product_id']) print " selected"; ?>><?=$product->code?></option>
<?php	} ?>
		</select>
	</td>
</tr>
<tr><td class="form_footer" colspan="5" style="text-align: center"><input type="submit" name="btn_submit" class="button" value="Search" /></td></tr>
</form>
</table>
<h2>Calibration Events [<?=count($verifications)?>]</h2>
<table class="body">
<tr>
	<th class="label dateValue">Date Submitted</th>
	<th class="label dateValue">Date Written</th>
	<th class="label orgValue">Organization</th>
	<th class="label">Asset</th>
	<th class="label">Product</th>
	<th class="label">Manufacturer</th>
	<th class="label">Cylinder</th>
	<th class="label">Concent.</th>
	<th class="label">Reading</th>
	<th class="label">Voltage</th>
</tr>
<?php	foreach ($verifications as $verification) {
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
<?php		if (isset($greenbar) && $greenbar)
			$greenbar = '';
		else
			$greenbar = ' greenbar';
	} ?>
</table>
