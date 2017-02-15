<script language="Javascript">
	function goCalibrate(code)
	{
		window.location = '/_spectros/calibrate/'+code;
		return false;
	}
</script>
<style>
	zoneLabel {
		width: 150px;
	}
	zoneValue {
		margin-right: 15px;
		margin-left: 15px;
	}
</style>
<form name="assetForm" method="post" action="/_monitor/asset">
<input type="hidden" name="id" value="<?=$asset->id?>" />
<? if ($GLOBALS['_page']->error) { ?>
<div class="form_error"><?=$GLOBALS['_page']->error?></div>
<?	} else if ($GLOBALS['_page']->success) { ?>
<div class="form_success"><?=$GLOBALS['_page']->success?></div>
<?	} ?>
<table class="body" cellpadding="0" cellspacing="0">
<tr><td colspan="4" class="title">Asset</td></tr>
<tr><td class="label" style="width: 150px;">Serial Number</td>
	<td class="value" style="width: 190px;"><?=$asset->code?></td>
	<td class="label" style="width: 150px;">Name</td>
	<td class="value" style="width: 240px;"><input type="text" name="name" class="value input" style="width: 250px" value="<?=$asset->name?>" /></td>
</tr>
<tr><td class="label">Model</td>
	<td class="value"><?=$asset->product->name?></td>
	<td class="label">Calibrated</td>
	<td class="value"></td>
</tr>
<tr><td class="form_footer" colspan="4"><input type="submit" name="btn_submit" class="button" /></td></tr>
</table>
</form>
<br/>
<div class="title">Last Communication</div>
<table class="body" cellpadding="0" cellspacing="0" width="900px">
<tr><td class="label">Date Hit [EST]</td>
	<td class="label">IP Address</td>
	<td class="label">URI</td>
	<td class="label">Method</td>
	<td class="label">Agent</td>
	<td class="label">Status</td>
</tr>
<?	if ($communication->timestamp > 0) { ?>
<tr><td class="value responseValue" nowrap><?=date('n/j/Y H:i:s',$communication->timestamp)?></td>
	<td class="value responseValue"><?=$request->client_ip?></td>
	<td class="value responseValue"><?=$request->uri?></td>
	<td class="value responseValue"><?=$request->post->method?></td>
	<td class="value responseValue"><?=$request->user_agent?></td>
	<td class="value responseValue"><?=$communication->result?></td>
</tr>
<?	} else { ?>
<tr><td class="value" colspan="4">None recorded</td></tr>
<?	} ?>
</table>
<br/>
<div class="title">Zones</div>
<table class="body" cellpadding="0" cellspacing="0">
<tr><td class="label zoneLabel" style="width: 50px;">ID</td>
	<td class="label zoneLabel" style="width: 220px;">Name</td>
	<td class="label zoneLabel" style="width: 180px;">Last Reading (EST)</td>
	<td class="label zoneLabel" style="width: 100px;">Last Value</td>
	<td class="label zoneLabel" style="width: 120px;">Units</td>
</tr>
<?	$greenbar = '';
	foreach ($sensors as $sensor) { ?>
<tr><td class="value zoneValue <?=$greenbar?>" style="text-align: right"><?=$sensor->code?>&nbsp;&nbsp;&nbsp;&nbsp;</td>
	<td class="value zoneValue <?=$greenbar?>"><?=$sensor->name?></td>
	<td class="value zoneValue <?=$greenbar?>"><? if ($sensor->timestamp) { print date('m/d/Y h:i:s',$sensor->timestamp); } ?></td>
	<td class="value zoneValue <?=$greenbar?>" style="text-align: right"><?=$sensor->value?>&nbsp;&nbsp;&nbsp;&nbsp;</td>
	<td class="value zoneValue <?=$greenbar?>"><?=$sensor->units?></td>
</tr>
<?		if ($greenbar) $greenbar = '';
		else $greenbar = 'greenbar';
	} ?>
</table>
<br/>
<table class="body" cellpadding="0" cellspacing="0">
<tr><td class="form_footer">
		<input type="button" name="btn_calibrate" class="button" value="Calibrate Monitor" onclick="goCalibrate('<?=$asset->code?>')" />
	</td>
</tr>
</table>
