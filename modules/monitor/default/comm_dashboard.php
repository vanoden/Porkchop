<style>
	.body {
		width: 1000px;
		margin-bottom: 5px;
		border: 1px solid #222222;
	}
	td.label {
		width: 200px;
	}
	.resultlabel {
		vertical-align: top;
		width: 100px;
	}
	.labelLastHit {
		width: 180px;
	}
	.labelRequest,
	.labelResponse {
		width: 100px;
	}
	.form_instruction,
	.form_error {
		width: 300px;
	}
	pre {
		line-height: 14px;
	}
	td {
		background-color: white;
	}
	.responseValue {
		border-bottom: 1px solid gray;
	}
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
<form action="comm_dashboard" method="POST">
<table class="body">
<tr><td class="title" colspan="4">Filter</td></tr>
<?	if ($GLOBALS['_page']->error) { ?>
<tr><td class="form_error" colspan="4"><?=$GLOBALS['_page']->error?></td></tr>
<?	} ?>
<tr><td class="form_instruction" colspan="4"><?=$GLOBALS['_page']->instruction?></td></tr>
<tr><td class="label">Account</td>
	<td class="value">
		<select name="account_code" class="value input">
			<option value="">All</option>
<?	foreach ($accounts as $account) {
		if (in_array($account->status,array('HIDDEN','DELETED'))) continue;
?>
			<option value="<?=$account->code?>"<? if (isset($parameters['account']) && $account->code == $parameters['account']) print " selected"; ?>><?=$account->code?></option>
<?	} ?>
		</select>
	</td>
	<td class="label">Status</td>
	<td class="value">
		<select name="_active" class="value input">
			<option value="1"<? if ($parameters['_active']) print " selected"; ?>>Active</option>
			<option value="0"<? if (! $parameters['_active']) print " selected"; ?>>All</option>
		</select>
	</td>
</tr>
<tr><td class="label">Start Date</td>
	<td class="value">
		<input type="text" name="date_start" class="value input" value="<?=$parameters['date_start']?>" />
	</td>
	<td class="label">Max Results</td>
	<td class="value">
		<input type="text" name="max_records" class="value input" value="<?=$parameters['_limit']?>" />
	</td>
</tr>
<tr><td class="table_footer" align="center" colspan="4"><input type="submit" name="btn_submit" class="button" value="Search"/></td></tr>
</table>
</form>
<table class="body">
<tr><td class="title" colspan="7">Sessions</td></tr>
<tr><td class="label resultlabel labelLastHit">Last Hit</td>
	<td class="label resultlabel labelAccount">Account</td>
	<td class="label resultlabel labelIPAddress">IP Address</td>
	<td class="label resultlabel labelPath">Path</td>
	<td class="label resultlabel labelResult">Result</td>
	<td class="label resultlabel labelRequest">Request</td>
	<td class="label resultlabel labelResponse">Response</td>
</tr>
<?	$session_id = 0;
	foreach($communications as $communication) {
		$session = new Session($communication->session_id);
		$customer = new RegisterCustomer($session->customer_id);
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
	<td class="value responseValue"><?=$customer->login?></td>
	<td class="value responseValue"><?=$request->client_ip?></td>
	<td class="value responseValue"><?=$request->uri?></td>
	<td class="value responseValue"><?=$communication->result?></td>
	<td class="value responseValue"><input type="button" name="btnRequest[<?=$session_id?>]" value="Show" class="button" onClick="showRequest(<?=$session_id?>)" /></td>
	<td class="value responseValue"><input type="button" name="btnResponse[<?=$session_id?>]" value="Show" class="button" onClick="showResponse(<?=$session_id?>)" /></td>
</tr>
<?
}
?>
</table>