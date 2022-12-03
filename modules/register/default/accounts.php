<!--Comment for testing-->
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
	.label { text-align: left; }
	th.accountsLoginLabel { width: 18%; }
	th.accountsFirstLabel { width: 15%; }
	th.accountsLastLabel { width: 15%; }
	th.accountsOrgLabel { width: 24%; overflow-x: hidden; }
	th.accountsStatus {	width: 10%;	}
	th.accountsLastActive {	width: 18%; }
	td.value { overflow: hidden; }
</style>
<section>
	<article class="segment">
		<h2>Customers</h2>
        <?php	if ($page->errorCount() > 0) { ?>
            <div class="form_error"><?=$page->errorString()?></div>
        <?php	}
                if ($page->success) { ?>
	        <div class="form_success"><?=$page->success?></div>
        <?php	} ?>
<form id="custSearch" method="get" class="float: left">
    <div id="search_container">
	    <input type="text" id="searchAccountInput" name="search" value="<?=$_REQUEST['search']?>" class="value input searchInput wide_md"/>
        <a href="#" id="searchOrganizationButton" name="btn_search" class="search_button" onclick="submitSearch(0)"/>&nbsp;</a>
	    <input type="checkbox" name="hidden" value="1" <?php if (isset($_REQUEST['hidden'])) print "checked"; ?> /><span>Hidden</span>
	    <input type="checkbox" name="expired" value="1" <?php if (isset($_REQUEST['expired'])) print "checked"; ?> /><span>Expired</span>
	    <input type="checkbox" name="blocked" value="1" <?php if (isset($_REQUEST['blocked'])) print "checked"; ?> /><span>Blocked</span>
	    <input type="checkbox" name="deleted" value="1" <?php if (isset($_REQUEST['deleted'])) print "checked"; ?> /><span>Deleted</span>
	    <input type="hidden" id="start" name="start" value="0">
    </div>
</form>
<hr style="visibility: hidden">
<table cellpadding="0" cellspacing="0" class="body">
    <tr><th class="label accountsLoginLabel">Login</th>
	    <th class="label accountsFirstLabel">First Name</th>
	    <th class="label accountsLastLabel">Last Name</th>
	    <th class="label accountsOrgLabel">Organization</th>
	    <th class="label accountsStatus">Status</th>
	    <th class="label accountsLastActive">Last Active</th>
    </tr>
    <?php
	    foreach ($customers as $customer) { 
	        if (isset($greenbar)) $greenbar = ''; else $greenbar = " greenbar";
    ?>
    <tr><td class="value<?=$greenbar?>"><a class="value<?=$greenbar?>" href="<?=PATH."/_register/admin_account?customer_id=".$customer->id?>"><?=$customer->login?></a></td>
	    <td class="value<?=$greenbar?>"><?=htmlspecialchars($customer->first_name)?></td>
	    <td class="value<?=$greenbar?>"><?=htmlspecialchars($customer->last_name)?></td>
	    <td class="value<?=$greenbar?>"><a href="/_register/organization?organization_id=<?=$customer->organization->id?>"><?=$customer->organization->name?></a></td>
	    <td class="value<?=$greenbar?>"><?=htmlspecialchars($customer->status)?></td>
	    <td class="value<?=$greenbar?>"><?=$customer->last_active()?></td>
    </tr>
    <?php		
	    }
    ?>
    <!--    Standard Page Navigation Bar ADMIN ONLY -->
    <div class="pager_bar">
	    <div class="pager_controls">
		    <a href="/_register/accounts?start=0&hidden=<?=$_REQUEST['hidden']?>&deleted=<?=$_REQUEST['deleted']?>&expired=<?=$_REQUEST['expired']?>" class="pager pagerFirst"><< First </a>
		    <a href="/_register/accounts?start=<?=$prev_offset?>&hidden=<?=$_REQUEST['hidden']?>&deleted=<?=$_REQUEST['deleted']?>&expired=<?=$_REQUEST['expired']?>" class="pager pagerPrevious"><</a>
		    &nbsp;<?=$_REQUEST['start']+1?> - <?=$_REQUEST['start']+$customers_per_page+1?> of <?=$total_customers?>&nbsp;
		    <a href="/_register/accounts?start=<?=$next_offset?>&hidden=<?=$_REQUEST['hidden']?>&deleted=<?=$_REQUEST['deleted']?>&expired=<?=$_REQUEST['expired']?>" class="pager pagerNext">></a>
		    <a href="/_register/accounts?start=<?=$last_offset?>&hidden=<?=$_REQUEST['hidden']?>&deleted=<?=$_REQUEST['deleted']?>&expired=<?=$_REQUEST['expired']?>" class="pager pagerLast"> Last >></a>
	    </div>
    </div>
    <?php
	    if ($GLOBALS['_SESSION_']->customer->can('manage customers')) {
    ?>
</table>
<form action="<?=PATH?>/_register/register" method="get">
	<input type="hidden" name="csrfToken" value="<?=$GLOBALS['_SESSION_']->getCSRFToken()?>">
    <div class="button-bar"><input type="submit" name="button_submit" value="Add Account" class="input button"/></div>
</form>
<?php	} ?>
<!--    [end] Standard Page Navigation Bar ADMIN ONLY-->
