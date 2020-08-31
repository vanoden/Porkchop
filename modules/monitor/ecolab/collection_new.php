<script src="/js/monitor.js" type="text/javascript"></script>
<script src="/js/product.js" type="text/javascript"></script>
<script language="Javascript">
	function assetSelected() {
		var asset = Object.create(Asset);
		asset.get(parseInt(document.getElementById('asset_id').value));
		var default_dashboard_id = asset.product.metadata.default_dashboard_id;
		var options = document.getElementById('dashboard_id').options;
		var matched = false;
		for(var id = 0; id < options.length; id ++) {
			if (options[id].value == default_dashboard_id) {
				options[id].selected = true;
				matched = true;
			}
			else {
				options[id].selected = false;
			}
			
		}
		if (matched) return dashboardSelected();
	}
	function dashboardSelected() {
		var dashboard = Object.create(Dashboard);
		var id = parseInt(document.getElementById('dashboard_id').value);
		var options;
		if (dashboard.get(id)) {
			var meta = dashboard.getMetadata('options');
			console.log(meta);
			options = JSON.parse(meta.value);
		}
		else {
			alert("Dashboard not found!");
		}
		var opt_container = document.getElementById('options_container');
		opt_container.innerHTML = '';
		if (typeof(options) == 'object') {
			if (typeof(options.date_type) == 'string') {
				var date_container = document.getElementById('date_container');
				document.getElementById('date_type').value = options.date_type;
				if (options.date_type == 'scrolling') {
					if (! date_container) date_container = document.createElement('div');
					opt_container.appendChild(date_container);
					var label = document.createElement('span');
					label.classList.add('label');
					label.innerHTML = 'Time Span (hours)';
					date_container.appendChild(label);
					var input = document.createElement('input');
					input.name="hours";
					input.value = "12";
					date_container.appendChild(input);
					opt_container.style.display = 'block';
					return true;
				}
				else if (options.date_type == 'range') {
					if (! date_container) date_container = document.createElement('div');
					opt_container.appendChild(date_container);
					var label_start = document.createElement('span');
					label_start.classList.add('label');
					label_start.innerHTML = 'Start Date';
					date_container.appendChild(label_start);
					var input_start = document.createElement('input');
					input_start.id = "date_start";
					input_start.name = "date_start";
					input_start.type = "text";
					input_start.classList.add('value');
					input_start.classList.add('input');
					date_container.appendChild(input_start);
					var label_end = document.createElement('span');
					label_end.classList.add('label');
					label_end.innerHTML = 'End Date';
					date_container.appendChild(label_end);
					opt_container.style.display = 'block';
					var input_end = document.createElement('input');
					input_end.id = "date_end";
					input_end.name = "date_end";
					input_end.type = "text";
					input_end.classList.add('value');
					input_end.classList.add('input');
					date_container.appendChild(input_end);
					return currentDate();
				}
			}
		}
	}
	function currentDate() {
		var today = new Date();
		var dateString = today.getFullYear() + '-' + String(today.getMonth()+1).padStart(2,'0') + '-' + String(today.getDate()).padStart(2,'0') + " " + String(today.getHours()).padStart(2,'0') + ":" + String(today.getMinutes()).padStart(2,'0');
		document.forms[0].date_start.value = dateString;
		var later = new Date(Date.now() + 1000 * 60 * 60 * 24 * 2);
		dateString = later.getFullYear() + '-' + String(later.getMonth()+1).padStart(2,'0') + '-' + String(later.getDate()).padStart(2,'0') + " " + String(later.getHours()).padStart(2,'0') + ":" + String(later.getMinutes()).padStart(2,'0');
		document.forms[0].date_end.value = dateString;
		return true;
	}
</script>
<h2>Create a New Job</h2>
<?php	if ($page->errorCount() > 0) { ?>
<div class="form_error"><?=$page->errorString()?></div>
<?php	} ?>
<form name="new_collection_form" action="/_monitor/collection_new" method="post">
<div class="container">
	<span class="label">Name</span>
	<input type="text" name="name" class="value input" value="<?=$_REQUEST['name']?>" />
</div>
<div class="container">
	<span class="label">Customer</span>
	<input type="text" name="customer" class="value input" value="<?=$_REQUEST['customer']?>" />
</div>
<div class="container">
	<span class="label">Commodity</span>
	<input type="text" name="commodity" class="value input" value="<?=$_REQUEST['commodity']?>" />
</div>
<div class="container">
	<span class="label">Location</span>
	<input type="text" name="location" class="value input" value="<?=$_REQUEST['location']?>" />
</div>
<div class="container">
	<span class="label">Monitor</span>
	<select name="asset_id" id="asset_id" class="value input" onchange="assetSelected();">
		<option value="">Add Later</option>
<?php	foreach ($assets as $asset) { ?>
		<option value="<?=$asset->id?>"<?php	if ($_REQUEST['asset_id'] == $asset->id) print " selected"; ?>><?=$asset->code?></option>
<?php	} ?>
	</select>
</div>
<div class="container">
	<span class="label">Dashboard</span>
	<select name="dashboard_id" id="dashboard_id" class="value input" onchange="dashboardSelected();">
		<option value="">Select</option>
<?php	foreach ($dashboards as $dashboard) { ?>
		<option value="<?=$dashboard->id?>"<?php	if ($_REQUEST['dashboard_id'] == $dashboard->id) print " selected"; ?>><?=$dashboard->name?></option>
<?php	} ?>
	</select>
</div>
<input type="hidden" name="type" id="date_type" />
<div class="container" style="display: none" id="options_container">
	<span class="label">Period (Hours)</span>
	<input type="text" name="hours" class="value input" value="<?=$_REQUEST['hours']?>" />
</div>
<div class="form_footer">
	<input type="submit" name="btn_submit" value="Create Job" class="button" />
</div>
</form>
