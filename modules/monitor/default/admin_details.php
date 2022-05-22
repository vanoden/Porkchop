<script language="Javascript">
	function goUrl(url) {
			location.href = url;
			return true;
	}
	function goMethodUrl(url) {
			location.href = '/_monitor/api/&amp;method='+url;
			return true;
	}
	function goAddTask() {
		location.href = '/_support/request_new_monitor?type=monitor&product_id=<?=$asset->product->id?>&code=<?=$asset->code?>';
	}
	function goAllTasks() {
		location.href = '/_support/request_items?product_id=<?=$asset->product->id?>&serial_number=<?=$asset->code?>';
	}
	function goCalibrationVerification() {
		document.mainform.action = '/_monitor/calibrate';
		document.mainform.code.value = '<?=$asset->code?>';
		document.mainform.submit();
	}
	function submitForm(assetID) {
		document.mainform.action = '/_monitor/details';
		document.mainform.id.value=assetID;
		document.mainform.submit();
		return true;
	}
</script>

<h2>Monitor Details</h2>
<form name="mainform" method="post">
	<input type="hidden" name="method" value="submit" />
	<?php	if ($page->errorCount()) { ?>
		<div class="form_error" style="width: 700px;">
		<?=$page->errorString?></div><?php	} ?><?php	if ($page->success) { ?>
		<div class="form_success" style="width: 700px;"><?=$page->success?></div>
	<?php	} ?>
	<div class="tableBody" style="outline: 1px solid red">
		<div class="tableColumn"></div>
		<div class="tableColumn"></div>
		<div class="tableColumn"></div>
		<div class="tableRowHeader">
			<div>Serial Number</div>
			<div>Model</div>
			<div>Organization</div>
		</div>
		<div class="tableRow">
			<div><input type="text" name="asset_code" class="value input <?=$disabled_new?>" value="<?=$asset_code?>" /></div>
			<div>
				<select name="product_id" class="value input <?=$disabled?>">
					<option value="">Select</option>
					<?php	foreach ($products as $product) { ?>
					<option value="<?=$product->id?>"<?php	if ($product_id == $product->id) print " selected";?>><?=$product->code?></option>
					<?php	} ?>
				</select>
			</div>
			<div>
				<select name="organization_id" class="value input <?=$disabled?>">
					<option value="">Select</option>
					<?php	foreach ($organizations as $organization) { ?>
					<option value="<?=$organization->id?>"<?php	if ($asset->organization->id == $organization->id) print " selected";?>><?=$organization->name?></option>
					<?php	} ?>
				</select>
			</div>
		</div>
		<div class="tableColumn"></div>
		<div class="tableColumn"></div>
		<div class="tableColumn"></div>
		<div class="tableRowHeader">
			<div>Software Version</div>
			<div>Display</div>
			<div>Date Shipped</div>
		</div>
		<div class="tableRow">
			<div><input type="text" name="software_version" class="value input" value="<?=$asset->metadata('software_version')?>" /></div>
			<div><input type="text" name="display_type" class="value input" value="<?=$asset->metadata('display_type')?>" /></div>
			<div><input type="text" name="date_shipped" class="value input" value="<?=$asset->metadata('date_shipped')?>" /></div>
		</div>
	</div>
	<?php	if ($asset->id) { ?>
				<div class="tableTitle">
					<h3 class="eyebrow">Last Communication</h3>
					<div>
						<div class="tableTitleRight">
							<input type="submit" name="btn_update" class="button" value="Apply" />
						</div>
					</div>
					</div>
					<div class="tableBody">
						<div class="tableColumn"></div>
						<div class="tableColumn"></div>
						<div class="tableColumn"></div>
						<div class="tableColumn"></div>
						<div class="tableColumn"></div>
						<div class="tableColumn"></div>
					<div class="tableRowHeader">
						<div>Date Hit</div>
						<div>IP Address</div>
						<div>URI</div>
						<div>Method</div>
						<div>Agent</div>
						<div>Status</div>
					</div>
	<?php  if ($communication->timestamp > 0) {
		$timearray = $GLOBALS['_SESSION_']->localtime($communication->timestamp);
		$request_time = sprintf("%d/%d/%04d %02d:%02d:%02d",$timearray['month'],$timearray['day'],$timearray['year'],$timearray['hour'],$timearray['minute'],$timearray['second']); ?>
		<div class="tableRow">
			<div nowrap><?=$request_time?></div>
			<div><?=$request->client_ip?></div>
			<div><?=$request->uri?></div>
			<div><?=$request->post->method?></div>
			<div><?=$request->user_agent?></div>
			<div><?=$communication->result?></div>
		</div>
	<?php  } else { ?>
		<div class="tablerow">
			<div style="width: 100%">None recorded</div>
		</div>
	<?php	 } ?>
</div>

<div class="tableTitle">
	<div>
		<h3 class="eyebrow">Asset Sensors</h3>
	</div>
	<div>
		<div class="tableTitleRight">
			<input type="submit" name="btn_update" class="button" value="Apply" />
		</div>
	</div>
</div>

<div class="tableBody">
	<div class="tableColumn"></div>
	<div class="tableColumn"></div>
	<div class="tableColumn"></div>
	<div class="tableColumn"></div>
	<div class="tableRowHeader">
		<div>Code</div>
		<div>Model</div>
		<div>Units</div>
		<div>Last Value</div>
		<div>Last Read</div>
	</div>
	<?php		foreach ($sensors as $sensor) {
					$reading = $sensor->lastReading();
					
	?>
				<div class="tableRow">
					<div><input type="text" name="sensor_code[<?=$sensor->id?>]" class="value input" value="<?=$sensor->code?>" <?=$disabled?> /></div>
					<div>
						<select name="model_id[<?=$sensor->id?>]" class="value input" />
						<option value="">Select</option>
						<?php			foreach ($models as $model) { ?>
						<option value="<?=$model->id?>"<?php	if ($model->id == $sensor->model_id) print " selected"; ?>><?=$model->code?></option>
						<?php				} ?>
						</select>
					</div>
					<div><?=$sensor->model->units?></div>
					<div><?=$reading->value?></div>
					<div><?php	if (isset($reading->timestamp)) print date('m/d/Y H:i:s',$reading->timestamp);?></div>
				</div>
				<?php		} ?>
					<div><input type="text" name="sensor_code[0]" class="value input" value="" <?=$disabled?> /></div>
					<div>
						<select name="model_id[0]" class="value input" />
							<option value="">Select</option>
							<?php		foreach ($models as $model) { ?>
													<option value="<?=$model->id?>"><?=$model->code?></option>
							<?php			} ?>
						</select>
					</div>
			</div>
<!-- Support Plugin -->
<?php			$module = new \Site\Module();
			if ($module->get('support')) { ?>
			<div class="tableTitle">
				<h3 class="eyebrow">Asset Tickets</h3>
				<div>
					<div class="tableTitleRight">
						<input type="button" name="btn_add_task" class="button secondary" value="Add Ticket" onclick="goAddTask()" />
						<input type="button" name="btn_all_tasks" class="button secondary" value="All Tickets" onclick="goAllTasks()" />
					</div>
				</div>
			</div>
			<div class="tableBody">
				<div class="tableColumn"></div>
				<div class="tableColumn"></div>
				<div class="tableColumn"></div>
				<div class="tableColumn"></div>
				<div class="tableColumn"></div>
				<div class="tableRowHeader">
					<div>#</div>
					<div>Date Requested</div>
					<div>By</div>
					<div>Status</div>
					<div>Assignee</div>
				</div>
<?php			foreach ($tickets as $ticket) { ?>
				<div class="tableRow">
					<div><a href="/_support/request_item/<?=$ticket->id?>"><?=$ticket->ticketNumber()?></a></div>
					<div><?=$ticket->request->date_request?></div>
					<div><?=$ticket->request->customer->full_name()?></div>
					<div><?=$ticket->status?></div>
					<div><?=$ticket->assigned->full_name()?></div>
				</div>
<?php			} ?>
			</div>
<?php			} ?>
<!-- Calibration Plugin -->
<?php		$module = new \Site\Module();
		if ($module->get('spectros')) {
?>
			<div class="tableTitle">
				<div>
					<div class="title tableTitleLeft">Calibration History</div>
				</div>
				<div>
					<div class="tableTitleRight">
						<input type="button" name="btn_add_task" class="button secondary" value="Record Calibration" onclick="goCalibrationVerification()" />
						<input type="submit" name="btn_update" class="button" value="Apply" />
					</div>
				</div>
			</div>
			<div class="tableBody">
				<div class="tableRowHeader">
					<div>Date Request</div>
					<div>By</div>
				</div>
			</div>
			<div class="tableBodyWrapper">
				<div class="tableBodyScrolled">
<?php		foreach ($calibrations as $calibration) {
			$calibrator = new \Register\Customer($calibration->customer->id);
?>
					<div class="tableRow">
						<div><?=$calibration->date_request?></div>
						<div><?=$calibrator->first_name." ".$calibrator->last_name." of ".$calibrator->organization->name?></div>
					</div>
<?php		} ?>
				</div>
			</div>
<?php		} ?>
<!-- Message -->
			<div class="tableTitle">
				<div>
					<div class="title tableTitleLeft">Messages</div>
				</div>
				<div>
					<div class="tableTitleRight">
						<input type="submit" name="btn_update" class="button" value="Apply" />
					</div>
				</div>
			</div>
			<div class="tableBody">
				<div class="tableRowHeader">
					<div>Date</div>
					<div>Level</div>
					<div>Message</div>
				</div>
<?php		foreach ($messages as $message) { ?>
				<div class="tableRow">
					<div><?=$message->date_recorded?></div>
					<div><?=$message->level?></div>
					<div><?=$message->message?></div>
				</div>
<?php			} ?>
			</div>
<?php	}
	else {
?>
			<div class="tableBody">
				<div class="tableRowFooter">
					<div><input type="submit" name="btn_submit" class="button" value="Add Asset"/></div>
				</div>
			</div>
<?php	} ?>
		</form>
