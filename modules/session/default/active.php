
<?php  if ($GLOBALS['_page']->error) { ?>
<div class="form_error"><?=$GLOBALS['_page']->error?></div>
<?php	} ?>
<table class="body">
<tr><td class="title" colspan="10">Active Sessions</tr>
<tr><td class="label session-active-login-label">Login</td>
	<td class="label session-active-ip-label">Remote IP</td>
	<td class="label session-active-first-hit-label">First Hit</td>
	<td class="label session-active-last-hit-label">Last Hit</td>
	<td class="label session-active-script-label">Script</td>
</tr>
<?php
	$_user = new \Register\Person();
	foreach ($sessions as $session) {
    	if (! $greenbar) $greenbar = 'greenbar'; else $greenbar = '';
		list($hit) = $_session->last_hit($session->id);
		$user = $_user->details($session->customer_id);
?>
<tr><td class="value <?=$greenbar?>"><?=$_user->code?></td>
	<td class="value <?=$greenbar?>"><?=$hit->remote_ip?></td>
	<td class="value <?=$greenbar?>"><?=$session->first_hit_date?></td>
	<td class="value <?=$greenbar?>"><?=$session->last_hit_date?></td>
	<td class="value <?=$greenbar?>"><?=$hit->script?></td>
</tr>
<?php	
	}
?>
</table>
