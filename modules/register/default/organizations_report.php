<script type="text/javascript">
	function submitForm() {
		return true;
	}
	function submitSearch() {
		document.getElementById('reportForm').submit();
		return true;
	}
</script>

<!-- Page Header -->
<?=$page->showAdminPageInfo()?>
<!-- End Page Header -->

<form id="reportForm" method="get" class="float: left">
<div id="search_container">
	<div><label>Match Length:</label><input type="text" name="match_length" value="<?php print $match_length; ?>" /></div>
	<div><label>Minimum Matches:</label><input type="text" name="min_matches" value="<?php print $min_matches; ?>" /></div>
	<div><label></label><input type="hidden" name="match_string" value=""></div>
	<button id="searchButton" name="btn_search" onclick="submitSearch()">Search</button>
</div>

<?php if ($match_string): ?>
	<!-- Drill down view for specific match string -->
	<div class="tableBody">
		<div class="tableRowHeader">
			<div class="tableCell">ID</div>
			<div class="tableCell">Name</div>
			<div class="tableCell">Code</div>
			<div class="tableCell">Status</div>
			<div class="tableCell">Users</div>
			<div class="tableCell">Date Created</div>
			<div class="tableCell">Actions</div>
		</div>
		<?php
			foreach ($organizations as $org) {
		?>
		<div class="tableRow">
			<div class="tableCell"><?=$org['id']?></div>
			<div class="tableCell"><?=$org['name']?></div>
			<div class="tableCell"><?=$org['code']?></div>
			<div class="tableCell"><?=$org['status']?></div>
			<div class="tableCell"><?=$org['user_count']?></div>
			<div class="tableCell"><?=date('Y-m-d', strtotime($org['date_created']))?></div>
			<div class="tableCell"><a href="<?=PATH."/_register/organization?organization_id=".$org['id']?>">View</a></div>
		</div>
		<?php
			}
			if (empty($organizations)) {
		?>
		<div class="tableRow">
			<div class="tableCell"><p>No Organizations Found</p></div>
		</div>
		<?php
			}
		?>
	</div><!-- end table -->
	
	<div class="button-bar">
		<span class="register-organizations-button-center">
			<a href="<?=PATH."/_register/organizations_report"?>" class="input button">Back to Report</a>
		</span>
	</div>

<?php else: ?>
	<!-- Main report view showing duplicate groups -->
	<div class="tableBody">
		<div class="tableRowHeader">
			<div class="tableCell">Match Count</div>
			<div class="tableCell">Repeating Characters</div>
			<div class="tableCell">Actions</div>
		</div>
		<?php
			foreach ($duplicate_groups as $group) {
		?>
		<div class="tableRow">
			<div class="tableCell"><?=$group['match_count']?></div>
			<div class="tableCell"><?=$group['nickname']?></div>
			<div class="tableCell">
				<a href="<?=PATH."/_register/organizations_report?match_string=".urlencode($group['nickname'])."&match_length=".$match_length."&min_matches=".$min_matches?>" class="input button">View Matches</a>
			</div>
		</div>
		<?php
			}
			if (empty($duplicate_groups)) {
		?>
		<div class="tableRow">
			<div class="tableCell"><p>No Duplicate Groups Found</p></div>
		</div>
		<?php
			}
		?>
	</div><!-- end table -->
<?php endif; ?>

</form>
