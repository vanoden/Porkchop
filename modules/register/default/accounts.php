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
<?= $page->showAdminPageInfo() ?>

<form id="custSearch" method="get">
    <div id="search_container">
	    <input type="text" id="searchAccountInput" name="search" placeholder="account name" value="<?=$_REQUEST['search']?>"/>
	    <div><input type="checkbox" name="hidden" value="1" <?php if (isset($_REQUEST['hidden'])) print "checked"; ?> /><label>Hidden</label></div>
	    <div><input type="checkbox" name="expired" value="1" <?php if (isset($_REQUEST['expired'])) print "checked"; ?> /><label>Expired</label></div>
	    <div><input type="checkbox" name="blocked" value="1" <?php if (isset($_REQUEST['blocked'])) print "checked"; ?> /><label>Blocked</label></div>
	    <div><input type="checkbox" name="deleted" value="1" <?php if (isset($_REQUEST['deleted'])) print "checked"; ?> /><label>Deleted</label></div>
	    <div><input type="hidden" id="start" name="start" value="0"></div>
        <div><label>Records per page:</label><input type="text" name="recordsPerPage" class="value input" style="width: 45px" value="<?=$recordsPerPage?>" /></div>
      <button id="searchOrganizationButton" name="btn_search" onclick="submitSearch(0)">Search</button>
    </div>
</form>

<div class="tableBody">
  <div class="tableRowHeader">
    <div class="tableCell" style="width: 18%;">Login</div>
    <div class="tableCell" style="width: 15%;">First Name</div>
    <div class="tableCell" style="width: 15%;">Last Name</div>
    <div class="tableCell" style="width: 24%; overflow-x: hidden;">Organization</div>
    <div class="tableCell" style="width: 10%;">Status</div>
    <div class="tableCell" style="width: 18%;">Last Active</div>
  </div>
  <?php
    if (! $page->errorCount()) {
    foreach ($customers as $customer) { 
        if (isset($greenbar)) $greenbar = ''; else $greenbar = " greenbar";
  ?>
  <div class="tableRow">
    <div class="tableCell"><label for="customer" class="hiddenDesktop">Login</label><a class="value<?=$greenbar?>" href="<?=PATH."/_register/admin_account?customer_id=".$customer->id?>"><?=$customer->login?></a></div>
    <div class="tableCell"><label for="first" class="hiddenDesktop">First Name</label><?=htmlspecialchars($customer->first_name)?></div>
    <div class="tableCell"><label for="last" class="hiddenDesktop">Last Name</label><?=htmlspecialchars($customer->last_name)?></div>
    <div class="tableCell"><label for="organization" class="hiddenDesktop">Organization</label><a href="/_register/organization?organization_id=<?=$customer->organization()->id?>"><?=$customer->organization()->name?></a></div>
    <div class="tableCell"><label for="status" class="hiddenDesktop">Status</label><?=htmlspecialchars($customer->status)?></div>
    <div class="tableCell"><label for="activity" class="hiddenDesktop">Last Active</label><?=$customer->last_active()?></div>
  </div>
  <?php		
    }}
  ?>
</div>

<!--    Standard Page Navigation Bar -->
<div class="pagination" id="pagination">
    <?=$pagination->renderPages(); ?>
</div>

<?php
  if ($GLOBALS['_SESSION_']->customer->can('manage customers')) {
?>

<form action="<?=PATH?>/_register/register" method="get">
    <div class="button-bar"><input type="submit" name="button_submit" value="Add Account" class="input button"/></div>
</form>

<?php	} ?>
<!--    [end] Standard Page Navigation Bar ADMIN ONLY-->
