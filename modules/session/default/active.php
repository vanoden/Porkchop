<style>
	.activeLoginLabel {
		width: 125px;
	}
	.activeIPLabel {
		width: 125px;
	}
	.activeFirstHitLabel,
	.activeLastHitLabel {
		width: 150px;
	}
	.activeScriptLabel {
		width: 275px;
	}
</style>
<?php  if ($GLOBALS['_page']->error) { ?>
<div class="form_error"><?=$GLOBALS['_page']->error?></div>
<?php	} ?>
<table class="body">
<tr><td class="title" colspan="10">Active Sessions</tr>
<tr><td class="label activeLoginLabel">Login</td>
	<td class="label activeIPLabel">Remote IP</td>
	<td class="label activeFirstHitLabel">First Hit</td>
	<td class="label activeLastHitLabel">Last Hit</td>
	<td class="label activeScriptLabel">Script</td>
</tr>
<?php
	$_user = new RegisterPerson();
	foreach ($sessions as $session) {
    	if (! $greenbar) $greenbar = 'greenbar'; else $greenbar = '';
		list($hit) = $_session->last_hit($session->id);
		$user = $_user->details($session->customer_id);
?>
<tr><td class="value <?=$greenbar?>"><?=$user->login?></td>
	<td class="value <?=$greenbar?>"><?=$hit->remote_ip?></td>
	<td class="value <?=$greenbar?>"><?=$session->first_hit_date?></td>
	<td class="value <?=$greenbar?>"><?=$session->last_hit_date?></td>
	<td class="value <?=$greenbar?>"><?=$hit->script?></td>
</tr>
<?php	
	}
?>
</table>
