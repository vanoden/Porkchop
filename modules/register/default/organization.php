<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
<link rel="stylesheet" href="//code.jquery.com/ui/1.12.1/themes/base/jquery-ui.css">
<script src="https://code.jquery.com/jquery-1.12.4.js"></script>
<script src="https://code.jquery.com/ui/1.12.1/jquery-ui.js"></script>

<style>
	.loading-spinner {
		-webkit-animation-name: spin;
		-webkit-animation-duration: 1000ms;
		-webkit-animation-iteration-count: infinite;
		-webkit-animation-timing-function: linear;
		-moz-animation-name: spin;
		-moz-animation-duration: 1000ms;
		-moz-animation-iteration-count: infinite;
		-moz-animation-timing-function: linear;
		-ms-animation-name: spin;
		-ms-animation-duration: 1000ms;
		-ms-animation-iteration-count: infinite;
		-ms-animation-timing-function: linear;
		animation-name: spin;
		animation-duration: 1000ms;
		animation-iteration-count: infinite;
		animation-timing-function: linear;
	}
	@-ms-keyframes spin {
		from { -ms-transform: rotate(0deg); }
		to { -ms-transform: rotate(360deg); }
	}
	@-moz-keyframes spin {
		from { -moz-transform: rotate(0deg); }
		to { -moz-transform: rotate(360deg); }
	}
	@-webkit-keyframes spin {
		from { -webkit-transform: rotate(0deg); }
		to { -webkit-transform: rotate(360deg); }
	}
	@keyframes spin {
		from {
		    transform:rotate(0deg);
		}
		to {
		    transform:rotate(360deg);
		}
	}
</style>


<h2>Organization Details</h2>
<form name="orgDetails" method="POST">
<input type="hidden" name="organization_id" value="<?=$organization->id?>"/>
<?  if ($GLOBALS['_page']->error) { ?>
<div class="form_error"><?=$GLOBALS['_page']->error?></div>
<?  }
	elseif ($GLOBALS['_page']->success) {
?>
<div class="form_success"><?=$GLOBALS['_page']->success?></div>
<?  } ?>
<div class="form_instruction">Make changes and click 'Apply' to complete.</div>

<script>
    // @TODO AJAX to get the slow responses on last active fixed for now, 
    //      prob going to need to convert the fields over to timestamps to index the column in the best way
    $(document).ready(function(){
        $('.active-date-lookup').each(function(){
            customerId = this.id.split('customer-id-active-').pop();
            currentElement = this;
            $.get( "/_register/api", { __format: "json", memberId: customerId, method: 'getMemberLastActive' }).done(function( data ) {
                data = $.parseJSON(data);
                $('#customer-id-loading-' + data.memberId).hide();    
                $('#customer-id-active-' + data.memberId).html(data.lastActive);
            });
        });
    });
</script>
<!--	Start First Row-->
<div class="tableBody min-tablet marginTop_20">
	<div class="tableRowHeader">
		<div class="tableCell" style="width: 20%;">Code</div>
		<div class="tableCell" style="width: 20%;">Name</div>
		<div class="tableCell" style="width: 15%;">Status</div>
		<div class="tableCell" style="width: 10%;">Can Resell</div>
		<div class="tableCell" style="width: 25%;">Reseller</div>
	</div> <!-- end row header -->
	<div class="tableRow">
		<div class="tableCell">
			<input name="code" type="text" id="organizationCodeValue" class="wide_100per" value="<?=$organization->code?>" />
		</div>
		<div class="tableCell">
			<input name="name" type="text" id="organizationNameValue" class="wide_100per" value="<?=$organization->name?>" />
		</div>
		<div class="tableCell">
			<select name="status" id="organizationStatusValue" class="wide_100per">
				<?		foreach (array("NEW","ACTIVE","EXPIRED","HIDDEN","DELETED") as $status) { ?>
				<option value="<?=$status?>"<? if ($status == $organization->status) print " selected"; ?>><?=$status?></option>
				<?		} ?>
			</select>
		</div>
		<div class="tableCell">
			<input name="is_reseller" type="checkbox" value="1" <? if($organization->is_reseller) print " checked"?> />
		</div>
		<div class="tableCell">
			<select name="assigned_reseller_id" class="wide_100per">
				<option value="">Select</option>
				<?	foreach ($resellers as $reseller) {
				if ($organization->id == $reseller->id) continue;
				?>
				<option value="<?=$reseller->id?>"<? if($organization->reseller->id == $reseller->id) print " selected";?>><?=$reseller->name?></option>
				<?	} ?>
			</select>
		</div>
	</div>
</div>
<div class="tableBody min-tablet">
	<div class="tableRowHeader">
		<div class="tableCell" style="width: 100%;">Notes</div>
	</div> <!-- end row header -->
	<div class="tableRow">
		<div class="tableCell">
			<textarea name="notes" class="wide_lg"><?=$organization->notes?></textarea>
		</div>
	</div>
</div>	
<!--End first row-->
	
<h3>Current Users</h3>
<!--	Start First Row-->
<?	if ($organization->id) { ?>
<div class="tableBody min-tablet">
	<div class="tableRowHeader">
		<div class="tableCell value" style="width: 20%;">Username</div>
		<div class="tableCell value" style="width: 20%;">First Name</div>
		<div class="tableCell value" style="width: 20%;">Last Name</div>
		<div class="tableCell value" style="width: 10%;">Status</div>
		<div class="tableCell value" style="width: 30%;">Last Active</div>
	</div>
<?	foreach ($members as $member) { ;?>
	<div class="tableRow">
		<div class="tableCell">
			<a href="/_register/admin_account?customer_id=<?=$member->id?>"><?=$member->login?></a>
		</div>
		<div class="tableCell">
			<?=$member->first_name?>
		</div>
		<div class="tableCell">
			<?=$member->last_name?>
		</div>
		<div class="tableCell">
			<?=$member->status?>
		</div>
		<div class="tableCell">
		    <i id="customer-id-loading-<?=$member->id?>" class="fa fa-refresh loading-spinner" aria-hidden="true"></i>
		    <span id="customer-id-active-<?=$member->id?>" class="active-date-lookup"></span>
		</div>
	</div>
<?	} ?>
</div>
<!--End first row-->	
		
<h3>Add New User</h3>
<!--	Start First Row-->
<div class="tableBody min-tablet">
	<div class="tableRowHeader">
		<div class="tableCell" style="width: 35%;">Username</div>
		<div class="tableCell" style="width: 30%;">First Name</div>
		<div class="tableCell" style="width: 35%;">Last Name</div>
	</div>
	<div class="tableRow">
		<div class="tableCell">
			<input type="text" class="wide_100per" name="new_login" value="" />
		</div>
		<div class="tableCell">
			<input type="text" class="wide_100per" name="new_first_name" value="" />
		</div>
		<div class="tableCell">
			<input type="text" class="wide_100per" name="new_last_name" value="" />
		</div>
	</div>
</div>
<!--End first row-->	
<?	} ?>
<div id="accountFormSubmit" class="button-bar marginTop_20">
	<input type="submit" name="method" value="Apply" class="button"/>
</div>
</form>



