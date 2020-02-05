<h2>Organization Details</h2>
<script language="JavaScript">
	function addLocation() {
		var organization_id = document.forms[0].organization_id.value;
		window.location.href = "/_register/admin_location?organization_id="+organization_id;
		return true;
	}
</script>
<form name="orgDetails" method="POST">
<input type="hidden" name="organization_id" value="<?=$organization->id?>"/>
<?  if ($page->errorCount() > 0) { ?>
<div class="form_error"><?=$page->errorString() > 0?></div>
<?  }
	elseif ($page->success) {
?>
<div class="form_success"><?=$page->success?></div>
<?  } ?>
<div class="form_instruction">Make changes and click 'Apply' to complete.</div>

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
<div class="tableFooter min-tablet">
	<input type="submit" name="method" value="Apply" class="button"/>
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
			<?=$member->last_active()?>
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
<div class="tableFooter min-tablet">
	<input type="submit" name="method" value="Add User" class="button"/>
</div>

<h3>Locations</h3>
<!--	Start First Row-->
<div class="tableBody min-tablet">
	<div class="tableRowHeader">
		<div class="tableCell value" style="width: 20%;">Name</div>
		<div class="tableCell value" style="width: 20%;">Address</div>
		<div class="tableCell value" style="width: 20%;">City</div>
		<div class="tableCell value" style="width: 20%;">Province</div>
		<div class="tableCell value" style="width: 20%;">Region</div>
	</div>
<?	foreach ($locations as $location) { ;?>
	<div class="tableRow">
		<div class="tableCell">
			<a href="/_register/admin_location?organization_id=<?=$organization->id?>&id=<?=$location->id?>"><?=$location->name?></a>
		</div>
		<div class="tableCell">
			<?=$location->address_1?>
		</div>
		<div class="tableCell">
			<?=$location->city?>
		</div>
		<div class="tableCell">
			<?=$location->province()->name?>
		</div>
		<div class="tableCell">
			<?=$location->province()->country()->name?>
		</div>
	</div>
<?	} ?>
</div>
<div class="tableFooter min-tablet">
	<input type="button" name="method" value="Add Location" class="button" onclick="addLocation()"/>
</div>
<!--End first row-->
<?	} ?>
</form>
