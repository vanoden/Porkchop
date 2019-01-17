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
<? if ($page->error) { ?>
<div class="form_error"><?=$page->error?></div>
<?	} else if ($page->success) { ?>
<div class="form_success"><?=$page->success?></div>
<?	} ?>
<div class="title">Monitor</div>
<div id="container">
	<div class="label">Serial Number</div>
	<div class="value"><?=$asset->code?></div>
	<div class="label">Name</div>
	<div class="value"><input type="text" name="name" class="value input" style="width: 250px" value="<?=$asset->name?>" /></div>
	<div class="label">Model</div>
	<div class="value"><?=$asset->product->name?></div>
</div>
<div class="form_footer" colspan="4"><input type="submit" name="btn_submit" class="button" /></div>
</form>
<div class="title">Last Communication</div>
<table class="body" cellpadding="0" cellspacing="0" width="900px">
<tr><th class="label">Date Hit</th>
	<th class="label">IP Address</th>
	<th class="label">URI</th>
	<th class="label">Method</th>
	<th class="label">Agent</th>
	<th class="label">Status</th>
</tr>
<?	if ($communication->timestamp > 0) { 
		$timearray = $GLOBALS['_SESSION_']->localtime($communication->timestamp);
		$request_time = sprintf("%d/%d/%04d %02d:%02d:%02d",$timearray['month'],$timearray['day'],$timearray['year'],$timearray['hour'],$timearray['minute'],$timearray['second']); ?>
<tr><td class="value responseValue" nowrap><?=$request_time?></td>
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
<div class="title">Zones</div>
<table class="body" cellpadding="0" cellspacing="0">
<tr><th class="label zoneLabel" style="width: 50px;">ID</th>
	<th class="label zoneLabel" style="width: 180px;">Name</th>
	<th class="label zoneLabel" style="width: 120px;">Model</th>
	<th class="label zoneLabel" style="width: 120px;">Units</th>
	<th class="label zoneLabel" style="width: 140px;">Last Reading</th>
	<th class="label zoneLabel" style="width: 100px;">Last Value</th>
</tr>
<?	$greenbar = '';
	foreach ($sensors as $sensor) {
		$reading = $sensor->lastReading();
		$datetime = $GLOBALS['_SESSION_']->localtime($reading->timestamp);
		$reading_time = sprintf("%d/%d/%4d %02d:%02d:%02d",$datetime["month"],$datetime["day"],$datetime["year"],$datetime['hour'],$datetime['minute'],$datetime['second']);
?>
<tr><td class="value zoneValue <?=$greenbar?>" style="text-align: right"><?=$sensor->code?>&nbsp;&nbsp;&nbsp;&nbsp;</td>
	<td class="value zoneValue <?=$greenbar?>"><?=$sensor->name?></td>
	<td class="value zoneValue <?=$greenbar?>"><?=$sensor->model->code?></td>
	<td class="value zoneValue <?=$greenbar?>"><?=$sensor->model->units?></td>
	<td class="value zoneValue <?=$greenbar?>"><? if ($reading->timestamp) { print $reading_time; } ?></td>
	<td class="value zoneValue <?=$greenbar?>" style="text-align: right"><?=$reading->value?>&nbsp;&nbsp;&nbsp;&nbsp;</td>
</tr>
<?		if ($greenbar) $greenbar = '';
		else $greenbar = 'greenbar';
	}
?>
</table>
<div class="title">Messages</div>
<table class="body" cellpadding="0" cellspacing="0">
<tr><th class="label">Date</th>
	<th class="label">Level</th>
	<th class="label">Message</th>
</tr>
<?  foreach ($messages as $message) { ?>
<tr><td class="value"><?=$message->localtime?></td>
	<td	class="value"><?=$message->level?></td>
	<td class="value"><?=$message->message?></td>
</tr>
<?	}
?>
</table>
