<?	if (! role('monitor admin'))
	{
		print "<span class=\"form_error\">You are not authorized for this view!</span>";
		return;
	}
?>
    <script language="Javascript">
        function goUrl(url)
        {
            location.href = url;
            return true;
        }
        function goMethodUrl(url)
        {
            location.href = '/_monitor/api/&amp;method='+url;
            return true;
        }
		function goAddTask()
		{
			window.open("/_action/new_task?asset_code=<?=$asset->code?>","newTask","status=0,toolbar=0,menubar=0,resizable=0,scrollbars=0,width=610px,height=270px,top=150,left=150");
		}
		function goCalibrationVerification()
		{
			document.mainform.action = '/_monitor/calibrate';
			document.mainform.code.value = '<?=$asset->code?>';
			document.mainform.submit();
		}
		function submitForm(assetID)
		{
			document.mainform.action = '/_monitor/details';
			document.mainform.id.value=assetID;
			document.mainform.submit();
			return true;
		}
    </script>
			<form name="mainform" method="post">
			<input type="hidden" name="method" value="submit" />
			<div class="title" >Asset Details</div>
<?	if ($GLOBALS['_page']->error) { ?>
			<div class="form_error" style="width: 700px;"><?=$GLOBALS['_page']->error?></div>
<?	} ?>
<?	if ($GLOBALS['_page']->success) { ?>
			<div class="form_success" style="width: 700px;"><?=$GLOBALS['_page']->success?></div>
<?	} ?>
        <div class="tableBody">
					<div class="tableRowHeader">
						<div class="tableCell" style="width: 35%;">Serial Number</div>
						<div class="tableCell" style="width: 30%;">Model</div>
						<div class="tableCell" style="width: 35%;">Organization</div>
					</div>
					<div class="tableRow">
					<div class="tableCell"><input type="text" name="asset_code" class="value input" value="<?=$asset_code?>" /></div>
					<div class="tableCell">
						<select name="product_id" class="value input">
							<option value="">Select</option>
<?	foreach ($products as $product) { ?>
							<option value="<?=$product->id?>"<? if ($product_id == $product->id) print " selected";?>><?=$product->code?></option>
<?	} ?>
						</select>
					</div>
					<div class="tableCell">
						<select name="organization_id" class="value input">
							<option value="">Select</option>
<?	foreach ($organizations as $organization) { ?>
							<option value="<?=$organization->id?>"<? if ($asset->organization->id == $organization->id) print " selected";?>><?=$organization->name?></option>
<?	} ?>
						</select>
					</div>
				</div>
			</div>
<?	if ($asset->id) { ?>
			<div class="tableTitle">
				<div class="tableCell">
					<div class="title tableTitleLeft">Asset Sensors</div>
				</div>
				<div class="tableCell">
					<div class="tableTitleRight">
						<input type="submit" name="btn_update" value="Apply" />
					</div>
				</div>
			</div>
			<div class="tableBody">
				<div class="tableRowHeader">
					<div class="tableCell">Code</div>
					<div class="tableCell">Model</div>
					<div class="tableCell">Last Value</div>
					<div class="tableCell">Last Read</div>
				</div>
<?		foreach ($sensors as $sensor) { 
				$reading = $sensor->lastReading();
?>
				<div class="tableRow">
					<div class="tableCell"><input type="text" name="sensor_code[<?=$sensor->id?>]" class="value input" value="<?=$sensor->code?>" /></div>
					<div class="tableCell"><select name="model_id[<?=$sensor->id?>]" class="value input">
		<?	foreach ($models as $model) { ?>
						<option value="<?=$model->id?>"<? if ($model->id == $sensor->model->id) print " selected"; ?>><?=$model->code?></option>
		<?	} ?>
						</select>
					</div>
					<div class="value tableCell"><?=$reading->value?></div>
					<div class="value tableCell"><? if (isset($reading->timestamp)) print date('m/d/Y H:i:s',$reading->timestamp);?></div>
				</div>
<?		} ?>
				<div class="tableRow">
					<div class="tableCell"><input type="text" name="sensor_code[0]" class="value input" value="" /></div>
					<div class="tableCell"><select name="model_id[0]" class="value input">
		<?	foreach ($models as $model) { ?>
						<option value="<?=$model->id?>"><?=$model->code?></option>
		<?	} ?>
						</select>
					</div>
				</div>
			</div>
<?		if (isset($GLOBALS['_config']->action)) { ?>
			<div class="tableTitle">
				<div class="tableCell">
					<div class="title tableTitleLeft">Action Tasks</div>
				</div>
				<div class="tableCell">
					<div class="tableTitleRight">
						<input type="button" name="btn_add_task" value="Add Task" onclick="goAddTask()" />
						<input type="submit" name="btn_update" value="Apply" />
					</div>
				</div>
			</div>
			<div class="tableBody">
				<div class="tableRowHeader">
					<div class="tableCell">Date Requested</div>
					<div class="tableCell">By</div>
					<div class="tableCell">Status</div>
					<div class="tableCell">Type</div>
					<div class="tableCell">Assignee</div>
				</div>
<?	foreach ($tasks as $task) { ?>
				<div class="tableRow">
					<div class="tableCell"><a href="/_spectros/admin_task/<?=$task->id?>"><?=$task->date_request?></a></div>
					<div class="tableCell"><?=$task->user_requested_name()?></div>
					<div class="tableCell"><?=$task->status?></div>
					<div class="tableCell"><?=$task->type()?></div>
					<div class="tableCell"><?=$task->user_assigned_name()?></div>
				</div>
<?	} ?>
			</div>
			<div class="tableTitle">
				<div class="tableCell">
					<div class="title tableTitleLeft">Asset History</div>
				</div>
				<div class="tableCell">
					<div class="tableTitleRight">
						<input type="submit" name="btn_update" value="Apply" />
					</div>
				</div>
			</div>
			<div class="tableBody">
				<div class="tableRowHeader">
					<div class="tableCell">Date Event</div>
					<div class="tableCell">Person</div>
					<div class="tableCell">Description</div>
				</div>
<?	foreach ($events["hits"]["hits"] as $hit) { 
                $event = $hit["_source"];
?>
				<div class="tableRow">
					<div class="tableCell"><?=$event["timestamp"]?></div>
					<div class="tableCell"><?=$event["user"]?></div>
					<div class="tableCell"><?=$event["description"]?></div>
				</div>
<?	} ?>
			</div>
<?	} ?>
<?	if (isset($GLOBALS['_config']->spectros)) { ?>
			<div class="tableTitle">
				<div class="tableCell">
					<div class="title tableTitleLeft">Calibration History</div>
				</div>
				<div class="tableCell">
					<div class="tableTitleRight">
						<input type="button" name="btn_add_task" value="Record Calibration" onclick="goCalibrationVerification()" />
						<input type="submit" name="btn_update" value="Apply" />
					</div>
				</div>
			</div>
			<div class="tableBody">
				<div class="tableRowHeader">
					<div class="tableCell">Date Request</div>
					<div class="tableCell">By</div>
				</div>
			</div>
<?	} ?>
			<div class="tableBodyWrapper">
				<div class="tableBodyScrolled">
<?		foreach ($verifications as $verification) {
			$calibrator = new \Register\Customer($verification->customer_id);
?>
					<div class="tableRow">
						<div class="tableCell"><?=$verification->date_request?></div>
						<div class="tableCell"><?=$calibrator->first_name." ".$calibrator->last_name." of ".$calibrator->organization->name?></div>
					</div>
<?		} ?>
				</div>
			</div>
			<div class="tableBody">
				<div class="tableRowFooter">
					<div class="tableCell"><input type="submit" name="btn_submit" class="button" value="Update Asset"/></div>
<?	}
	else
	{
?>
			<div class="tableBody">
				<div class="tableRowFooter">
					<div class="tableCell"><input type="submit" name="btn_submit" class="button" value="Add Asset"/></div>
<?	} ?>
				</div>
			</div>
			</form>
