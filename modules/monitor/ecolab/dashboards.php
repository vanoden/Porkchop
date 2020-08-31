<h2>Dashboards</h2>
<div class="tableBody">
	<div class="tableRowHeader">
		<div class="tableCell">Name</div>
		<div class="tableCell">Status</div>
		<div class="tableCell">Template</div>
	</div>
<?php	foreach ($dashboards as $dashboard) { ?>
	<div class="tableRow">
		<div class="tableCell"><a href="/_monitor/admin_dashboard?id=<?=$dashboard->id?>"><?=$dashboard->name?></a></div>
		<div class="tableCell"><?=$dashboard->status?></div>
		<div class="tableCell"><?=$dashboard->template?></div>
	</div>
<?php	} ?>
</div>
<div class="form_footer">
	<form method="post" action="/_monitor/dashboard_new">
	<input type="submit" name="btn_new" class="button" value="Add Dashboard"/>
	</form>
</div>
