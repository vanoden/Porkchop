<script type="text/javascript">
	function submitForm() {
		if (document.register.password.value.length > 0 || document.register.password_2.value.length > 0) {
			if (document.register.password.value.length < 6) {
				alert("Your password is too short.");
				return false;
			}
			
			if (document.register.password.value != document.register.password_2.value) {
				alert("Your passwords don't match.");
				return false;
			}
		}
		return true;
	}
	function submitSearch(start) {
		document.getElementById('start').value=start;
		document.getElementById('custSearch').submit();
		return true;
	}
</script>
<style>
	.label {
		text-align: left;
	}
	th.accountsLoginLabel {
		width: 150px;
	}
	th.accountsFirstLabel {
		width: 140px;
	}
	th.accountsLastLabel {
		width: 140px;
	}
	th.accountsOrgLabel {
		width: 300px;
	}
	td.value {
		overflow: hidden;
	}
	.greenbar {
		background-color: #bbbbbb;
	}
</style>
</script>
	<div class="title">Accounts</div>
<?	if ($page->error) { ?>
	<div class="form_error"><?=$page->error?></div>
<?	} ?>
<?	if ($page->success) { ?>
	<div class="form_success"><?=$page->success?></div>
<?	} ?>
	<div id="search_container">
		<form id="custSearch" method="get" class="float: left">
		<input type="text" id="searchAccountInput" name="search" value="<?=$_REQUEST['search']?>" class="value input searchInput"/>
		<input type="hidden" id="start" name="start" value="0">
		<img id="searchOrganizationButton" name="btn_search" class="search_button" onclick="submitSearch(0)"/>
		</form>
	</div>
	<table cellpadding="0" cellspacing="0" class="body">
	<tr><th class="label accountsLoginLabel">Login</th>
		<th class="label accountsFirstLabel">First Name</th>
		<th class="label accountsLastLabel">Last Name</th>
		<th class="label accountsOrgLabel">Organization</th>
	</tr>
	<?	foreach ($customers as $customer) { ?>
	<tr><td class="value<?=$greenbar?>"><a class="value<?=$greenbar?>" href="<?=PATH."/_register/account?customer_id=".$customer->id?>"><?=$customer->login?></a></td>
		<td class="value<?=$greenbar?>"><?=$customer->first_name?></td>
		<td class="value<?=$greenbar?>"><?=$customer->last_name?></td>
		<td class="value<?=$greenbar?>"><?=$customer->organization->name?></td>
	</tr>
<?		if ($greenbar) $greenbar = '';
		else $greenbar = " greenbar";
	}
?>
	<tr><td colspan="6" style="text-align: center">
		<a href="/_register/accounts?start=0" style="margin: 5px"><<</a>
		<a href="/_register/accounts?start=<?=$prev_offset?>" style="margin: 5px"><</a>
		&nbsp;<?=$_REQUEST['start']+1?> - <?=$_REQUEST['start']+$customers_per_page+1?> of <?=$total_customers?>&nbsp;
		<a href="/_register/accounts?start=<?=$next_offset?>" style="margin: 5px">></a>
		<a href="/_register/accounts?start=<?=$last_offset?>" style="margin: 5px">>></a>
		</td>
	</tr>
<?
	if (role('register manager'))
	{
?>
	<form action="<?=PATH?>/_register/register" method="get">
	<tr><td colspan="4" style="text-align: center"><input type="submit" name="button_submit" value="Add Account" class="input button"/>
	</form>
<?	} ?>
	</table>
