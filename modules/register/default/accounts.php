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

<!-- Page Header -->
<?= $page->showTitle() ?>
<?=$page->showBreadcrumbs()?>
<?=$page->showMessages()?>
<!-- End Page Header -->

<form id="custSearch" method="get">
    <div id="search_container">
	    <input type="text" id="searchAccountInput" name="search" placeholder="account name" value="<?=$_REQUEST['search']?>"/>
	    <div><input type="checkbox" name="hidden" value="1" <?php if (isset($_REQUEST['hidden'])) print "checked"; ?> /><label>Hidden</label></div>
	    <div><input type="checkbox" name="expired" value="1" <?php if (isset($_REQUEST['expired'])) print "checked"; ?> /><label>Expired</label></div>
	    <div><input type="checkbox" name="blocked" value="1" <?php if (isset($_REQUEST['blocked'])) print "checked"; ?> /><label>Blocked</label></div>
	    <div><input type="checkbox" name="deleted" value="1" <?php if (isset($_REQUEST['deleted'])) print "checked"; ?> /><label>Deleted</label></div>
	    <div><input type="hidden" id="start" name="start" value="0"></div>
      <button id="searchOrganizationButton" name="btn_search" onclick="submitSearch(0)"/>Search</button>
    </div>
</form>

<div class="tableBody">
  <div class="tableRowHeader">
    <div class="tableCell" style="width: 18%;" >Login</div>
    <div class="tableCell" style="width: 15%;">First Name</div>
    <div class="tableCell" style="width: 15%;">Last Name</div>
    <div class="tableCell" style="width: 24%; overflow-x: hidden;">Organization</div>
    <div class="tableCell" style="width: 10%;">Status</div>
    <div class="tableCell" style="width: 18%;">Last Active</div>
  </div>
  <?php
    foreach ($customers as $customer) { 
        if (isset($greenbar)) $greenbar = ''; else $greenbar = " greenbar";
  ?>
  <div class="tableRow">
    <div class="tableCell"><a class="value<?=$greenbar?>" href="<?=PATH."/_register/admin_account?customer_id=".$customer->id?>"><?=$customer->code?></a></div>
    <div class="tableCell"><?=htmlspecialchars($customer->first_name)?></div>
    <div class="tableCell"><?=htmlspecialchars($customer->last_name)?></div>
    <div class="tableCell"><a href="/_register/organization?organization_id=<?=$customer->organization()->id?>"><?=$customer->organization()->name?></a></div>
    <div class="tableCell"><?=htmlspecialchars($customer->status)?></div>
    <div class="tableCell"><?=$customer->last_active()?></div>
  </div>
  <?php		
    }
  ?>
</div>

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

<form action="<?=PATH?>/_register/register" method="get">
	<input type="hidden" name="csrfToken" value="<?=$GLOBALS['_SESSION_']->getCSRFToken()?>">
    <div class="button-bar"><input type="submit" name="button_submit" value="Add Account" class="input button"/></div>
</form>

<?php	} ?>
<!--    [end] Standard Page Navigation Bar ADMIN ONLY-->
