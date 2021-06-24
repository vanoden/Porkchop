<style>
/*	h2 {border-bottom: 3px solid rgba(0,42,58,0.4);}*/
</style>
<script language="Javascript">
	var Requests = new Array();
	var Responses = new Array();
	
	function showRequest(id) {
		alert(Requests[id]);
	}
	function showResponse(id) {
		alert(Responses[id]);
	}
</script>
<h2>Monitor API Sessions</h2>
<?php	if ($GLOBALS['_page']->error) { ?>
<div class="form_error" colspan="4"><?=$GLOBALS['_page']->error?></div>
<?php	} ?>
<div class="form_instruction" colspan="4"><?=$GLOBALS['_page']->instruction?></div>

<form action="comm_dashboard" method="POST">
<h3>Filter</h3>
<!--	START First Table -->
	<div class="tableBody min-tablet marginTop_20">
	<div class="tableRowHeader">
		<div class="tableCell" style="width: 25%;">Account</div>
		<div class="tableCell" style="width: 25%;">Status</div>
		<div class="tableCell" style="width: 25%;">Start Date</div>
		<div class="tableCell" style="width: 25%;">Max Results</div>
	</div>	<div class="tableRow">
		<div class="tableCell">
			<input type="text" name="account_code" class="value input wide_md" value="<?=$parameters["account"]?>" />
		</div>
		<div class="tableCell">
			<select name="_active" class="value input wide_xs">
				<option value="1" <?php if ($parameters['_active']) print " selected"; ?>>Active</option>
				<option value="0" <?php if (! $parameters['_active']) print " selected"; ?>>All</option>
			</select>
		</div>
		<div class="tableCell">
			<input type="text" name="date_start" class="value input" value="<?=$parameters['date_start']?>" />
		</div>
		<div class="tableCell">
			<input type="text" name="max_records" class="value input wide_xs" value="<?=$parameters['_limit']?>" />
		</div>
	</div>
</div>
<!--	END First Table -->	
	
<div class="button-bar min-tablet"><input type="submit" name="btn_submit" class="button" value="Search"/></div>
</form>

<h3>Sessions</h3>
<table class="max_1000">
<tr>
	<th class="label resultlabel labelLastHit">Last Hit</th>
	<th class="label resultlabel labelAccount">Account</th>
	<th class="label resultlabel labelIPAddress">IP Address</th>
	<th class="label resultlabel labelPath">Method</th>
	<th class="label resultlabel labelResult">Result</th>
	<th class="label resultlabel labelRequest">Request</th>
	<th class="label resultlabel labelResponse">Response</th>
</tr>
<?php	$session_id = 0;
	foreach($communications as $communication) {
		$session = $communication->session;
		$customer = $session->customer;
		$request = $communication->request;
		$response = $communication->response;
		$session_id ++;
		$response_string = $communication->response_json;
		$request_string = $communication->request_json;
?>
<tr>
	<script>
		Requests[<?=$session_id?>] = '<?=$request_string?>';
		Responses[<?=$session_id?>] = '<?=$response_string?>';
	</script>
	<td class="value responseValue" nowrap><?=date('n/j/Y H:i:s',$communication->timestamp)?></td>
	<td class="value responseValue"><?=$customer->code?></td>
	<td class="value responseValue"><?=$request->client_ip?></td>
	<td class="value responseValue"><?=$request->method?></td>
	<td class="value responseValue"><?=$communication->result?></td>
	<td class="value responseValue"><input type="button" name="btnRequest[<?=$session_id?>]" value="Show" class="button" onClick="showRequest(<?=$session_id?>)" /></td>
	<td class="value responseValue"><input type="button" name="btnResponse[<?=$session_id?>]" value="Show" class="button" onClick="showResponse(<?=$session_id?>)" /></td>
</tr>
<?php
}
?>
</table>
